<?php

namespace App\Listeners;

use App\Events\SaleCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class UpdateInventoryOnSale implements ShouldQueue
{
    /**
     * Manipular o evento.
     */
    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale;

        // Eager load para evitar N+1 queries
        $sale->load('items.product.inventory');

        foreach ($sale->items as $item) {
            $inventory = $item->product->inventory;

            if ($inventory) {
                $inventory->removeQuantity($item->quantity);

                Log::info('Estoque atualizado para o produto', [
                    'product_id' => $item->product_id,
                    'quantity_sold' => $item->quantity,
                    'remaining_stock' => $inventory->quantity,
                ]);
            }
        }
    }
}
