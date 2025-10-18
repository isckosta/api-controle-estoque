<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => strtoupper(fake()->unique()->bothify('???-###')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'cost_price' => fake()->randomFloat(2, 50, 500),
            'sale_price' => fake()->randomFloat(2, 100, 1000),
        ];
    }
}
