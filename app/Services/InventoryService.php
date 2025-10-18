<?php

namespace App\Services;

use App\Models\Inventory;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Adicionar estoque ao inventário.
     *
     * @throws Exception
     */
    public function addStock(int $productId, int $quantity): Inventory
    {
        try {
            return DB::transaction(function () use ($productId, $quantity) {
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $productId],
                    ['quantity' => 0]
                );

                $inventory->addQuantity($quantity);

                return $inventory->fresh();
            });
        } catch (Exception $e) {
            Log::error('Erro ao adicionar estoque', [
                'product_id' => $productId,
                'quantity' => $quantity,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao adicionar estoque: '.$e->getMessage());
        }
    }

    /**
     * Obter situação atual do inventário.
     */
    public function getInventoryStatus(): Collection
    {
        return Inventory::with('product')
            ->get()
            ->map(function ($inventory) {
                return [
                    'product_id' => $inventory->product_id,
                    'sku' => $inventory->product->sku,
                    'name' => $inventory->product->name,
                    'quantity' => $inventory->quantity,
                    'cost_price' => $inventory->product->cost_price,
                    'sale_price' => $inventory->product->sale_price,
                    'total_cost' => $inventory->total_cost,
                    'total_value' => $inventory->total_value,
                    'projected_profit' => $inventory->projected_profit,
                    'last_updated' => $inventory->last_updated,
                ];
            });
    }

    /**
     * Obter resumo do inventário.
     */
    public function getInventorySummary(): array
    {
        $inventories = Inventory::with('product')->get();

        $totalCost = $inventories->sum('total_cost');
        $totalValue = $inventories->sum('total_value');
        $projectedProfit = $totalValue - $totalCost;

        return [
            'total_items' => $inventories->count(),
            'total_units' => $inventories->sum('quantity'),
            'total_cost' => round($totalCost, 2),
            'total_value' => round($totalValue, 2),
            'projected_profit' => round($projectedProfit, 2),
            'profit_margin' => $totalValue > 0 ? round(($projectedProfit / $totalValue) * 100, 2) : 0,
        ];
    }

    /**
     * Verificar se o produto tem estoque suficiente.
     */
    public function hasStock(int $productId, int $quantity): bool
    {
        $inventory = Inventory::where('product_id', $productId)->first();

        return $inventory && $inventory->hasStock($quantity);
    }

    /**
     * Obter inventário do produto.
     */
    public function getProductInventory(int $productId): ?Inventory
    {
        return Inventory::where('product_id', $productId)->first();
    }
}
