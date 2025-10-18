<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        $initialStock = [
            'LAPTOP-001' => 15,
            'MOUSE-001' => 50,
            'KEYBOARD-001' => 30,
            'MONITOR-001' => 20,
            'HEADSET-001' => 40,
        ];

        foreach ($products as $product) {
            Inventory::create([
                'product_id' => $product->id,
                'quantity' => $initialStock[$product->sku] ?? 0,
                'last_updated' => now(),
            ]);
        }
    }
}
