<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de API para gerenciamento de inventário.
 *
 * Esta classe contém testes para validar as operações de inventário,
 * incluindo adição de estoque, validações e consultas de status.
 */
class InventoryApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se é possível adicionar inventário para um produto.
     *
     * Verifica se a API permite adicionar uma quantidade de estoque
     * para um produto existente e se os dados são persistidos corretamente.
     */
    public function test_can_add_inventory(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/inventory', [
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Inventory updated successfully',
                'data' => [
                    'product_id' => $product->id,
                    'quantity' => 50,
                ],
            ]);

        $this->assertDatabaseHas('inventory', [
            'product_id' => $product->id,
            'quantity' => 50,
        ]);
    }

    /**
     * Testa se o campo product_id é obrigatório ao adicionar inventário.
     *
     * Verifica se a API retorna erro de validação quando o product_id
     * não é fornecido na requisição.
     */
    public function test_add_inventory_requires_product_id(): void
    {
        $response = $this->postJson('/api/v1/inventory', [
            'quantity' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Testa se o product_id deve corresponder a um produto existente.
     *
     * Verifica se a API retorna erro de validação quando um product_id
     * inválido (não existente) é fornecido.
     */
    public function test_add_inventory_requires_valid_product(): void
    {
        $response = $this->postJson('/api/v1/inventory', [
            'product_id' => 999,
            'quantity' => 50,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /**
     * Testa se a quantidade deve ser um valor positivo.
     *
     * Verifica se a API retorna erro de validação quando uma quantidade
     * zero ou negativa é fornecida.
     */
    public function test_add_inventory_requires_positive_quantity(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson('/api/v1/inventory', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /**
     * Testa se é possível obter o status do inventário.
     *
     * Verifica se a API retorna corretamente a lista de produtos em estoque
     * com suas informações detalhadas e um resumo geral do inventário.
     */
    public function test_can_get_inventory_status(): void
    {
        $product = Product::factory()->create([
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'cost_price' => 100,
            'sale_price' => 150,
        ]);
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response = $this->getJson('/api/v1/inventory');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'product_id',
                        'sku',
                        'name',
                        'quantity',
                        'cost_price',
                        'sale_price',
                        'total_cost',
                        'total_value',
                        'projected_profit',
                    ],
                ],
                'summary' => [
                    'total_items',
                    'total_units',
                    'total_cost',
                    'total_value',
                    'projected_profit',
                    'profit_margin',
                ],
            ]);

        $this->assertEquals(1, $response->json('summary.total_items'));
        $this->assertEquals(10, $response->json('summary.total_units'));
    }

    /**
     * Testa se o resumo do inventário é calculado corretamente.
     *
     * Verifica se os totais de itens, unidades, custos, valores e lucros
     * são calculados corretamente quando há múltiplos produtos no inventário.
     */
    public function test_inventory_summary_calculates_correctly(): void
    {
        Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ])->inventory()->create(['quantity' => 10]);

        Product::factory()->create([
            'cost_price' => 200,
            'sale_price' => 300,
        ])->inventory()->create(['quantity' => 5]);

        $response = $this->getJson('/api/v1/inventory');

        $response->assertStatus(200);

        $summary = $response->json('summary');
        $this->assertEquals(2, $summary['total_items']);
        $this->assertEquals(15, $summary['total_units']);
        $this->assertEquals(2000, $summary['total_cost']);
        $this->assertEquals(3000, $summary['total_value']);
        $this->assertEquals(1000, $summary['projected_profit']);
    }
}
