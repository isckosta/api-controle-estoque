<?php

namespace App\Services;

use App\Events\SaleCompleted;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleService
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    /**
     * Criar uma nova venda.
     *
     * @param  array  $items  [['product_id' => int, 'quantity' => int], ...]
     *
     * @throws Exception
     */
    public function createSale(array $items): Sale
    {
        // Validar disponibilidade de estoque
        foreach ($items as $item) {
            if (! $this->inventoryService->hasStock($item['product_id'], $item['quantity'])) {
                $product = Product::find($item['product_id']);
                throw new Exception("Estoque insuficiente para o produto: {$product->name}");
            }
        }

        try {
            DB::beginTransaction();

            // Criar venda
            $sale = Sale::create([
                'total_amount' => 0,
                'total_cost'   => 0,
                'total_profit' => 0,
                'status'       => 'pending',
            ]);

            // Criar itens da venda
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $product->id,
                    'quantity'   => $item['quantity'],
                    'unit_price' => $product->sale_price,
                    'unit_cost'  => $product->cost_price,
                ]);
            }

            // Calcular totais
            $sale->load('items');
            $sale->calculateTotals();
            $sale->save();

            // Marcar como concluída e disparar evento
            $sale->markAsCompleted();
            event(new SaleCompleted($sale));

            DB::commit();

            return $sale->fresh(['items.product']);
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Obter detalhes da venda.
     */
    public function getSaleDetails(int $saleId): ?Sale
    {
        return Sale::with(['items.product'])->find($saleId);
    }

    /**
     * Obter todas as vendas.
     */
    public function getAllSales()
    {
        return Sale::with(['items.product'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Calcular estatísticas de vendas.
     */
    public function getSaleStatistics(): array
    {
        $sales = Sale::where('status', 'completed')->get();

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => round($sales->sum('total_amount'), 2),
            'total_cost' => round($sales->sum('total_cost'), 2),
            'total_profit' => round($sales->sum('total_profit'), 2),
            'average_sale_value' => $sales->count() > 0 ? round($sales->avg('total_amount'), 2) : 0,
            'average_profit_margin' => $sales->count() > 0 ? round($sales->avg('profit_margin'), 2) : 0,
        ];
    }
}
