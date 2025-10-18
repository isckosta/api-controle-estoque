<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'sku' => 'LAPTOP-001',
                'name' => 'Notebook Dell Inspiron 15',
                'description' => 'Notebook Dell Inspiron 15, Intel Core i7, 16GB RAM, 512GB SSD',
                'cost_price' => 3500.00,
                'sale_price' => 4999.00,
            ],
            [
                'sku' => 'MOUSE-001',
                'name' => 'Mouse Logitech MX Master 3',
                'description' => 'Mouse sem fio Logitech MX Master 3, ergonômico, 7 botões',
                'cost_price' => 350.00,
                'sale_price' => 549.00,
            ],
            [
                'sku' => 'KEYBOARD-001',
                'name' => 'Teclado Mecânico Keychron K2',
                'description' => 'Teclado mecânico sem fio Keychron K2, RGB, switches Gateron Brown',
                'cost_price' => 450.00,
                'sale_price' => 699.00,
            ],
            [
                'sku' => 'MONITOR-001',
                'name' => 'Monitor LG UltraWide 29"',
                'description' => 'Monitor LG UltraWide 29", Full HD, IPS, 75Hz',
                'cost_price' => 1200.00,
                'sale_price' => 1799.00,
            ],
            [
                'sku' => 'HEADSET-001',
                'name' => 'Headset HyperX Cloud II',
                'description' => 'Headset Gamer HyperX Cloud II, 7.1 surround, microfone removível',
                'cost_price' => 400.00,
                'sale_price' => 599.00,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
