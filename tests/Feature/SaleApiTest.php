<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de API para gerenciamento de vendas.
 *
 * Esta classe contém testes para validar as operações de vendas,
 * incluindo criação, validações e atualização de inventário.
 */
class SaleApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se é possível criar uma venda.
     *
     * Verifica se a API permite criar uma venda com produtos válidos
     * e se os totais são calculados corretamente.
     */
    public function test_can_create_sale(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ]);
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response = $this->postJson('/api/v1/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Sale created successfully',
                'data' => [
                    'total_amount' => 300,
                    'total_cost' => 200,
                    'total_profit' => 100,
                    'status' => 'completed',
                ],
            ]);

        $this->assertDatabaseHas('sales', [
            'total_amount' => 300,
            'status' => 'completed',
        ]);
    }

    /**
     * Testa se a venda atualiza o inventário.
     *
     * Verifica se ao criar uma venda, a quantidade em estoque
     * é reduzida corretamente.
     */
    public function test_sale_updates_inventory(): void
    {
        $product = Product::factory()->create();
        $inventory = Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->postJson('/api/v1/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
        ]);

        $this->assertEquals(7, $inventory->fresh()->quantity);
    }

    /**
     * Testa se não é possível criar venda com estoque insuficiente.
     *
     * Verifica se a API impede a criação de uma venda quando
     * não há estoque suficiente.
     */
    public function test_cannot_create_sale_with_insufficient_stock(): void
    {
        $product = Product::factory()->create();
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $response = $this->postJson('/api/v1/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'message' => 'Failed to create sale',
            ]);
    }

    /**
     * Testa se o campo items é obrigatório ao criar venda.
     *
     * Verifica se a API retorna erro de validação quando
     * nenhum item é fornecido.
     */
    public function test_create_sale_requires_items(): void
    {
        $response = $this->postJson('/api/v1/sales', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    /**
     * Testa se os product_ids devem ser válidos.
     *
     * Verifica se a API valida que os produtos nos itens
     * da venda existem no banco de dados.
     */
    public function test_create_sale_requires_valid_product_ids(): void
    {
        $response = $this->postJson('/api/v1/sales', [
            'items' => [
                ['product_id' => 999, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }

    /**
     * Testa se é possível obter detalhes de uma venda.
     *
     * Verifica se a API retorna corretamente as informações
     * detalhadas de uma venda específica.
     */
    public function test_can_get_sale_details(): void
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

        $createResponse = $this->postJson('/api/v1/sales', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $saleId = $createResponse->json('data.id');

        $response = $this->getJson("/api/v1/sales/{$saleId}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'total_amount',
                    'total_cost',
                    'total_profit',
                    'profit_margin',
                    'status',
                    'items' => [
                        '*' => [
                            'product_id',
                            'product_name',
                            'product_sku',
                            'quantity',
                            'unit_price',
                            'unit_cost',
                            'subtotal',
                            'profit',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    /**
     * Testa se retorna 404 para venda inexistente.
     *
     * Verifica se a API retorna erro 404 quando tenta buscar
     * uma venda que não existe.
     */
    public function test_get_sale_returns_404_for_nonexistent_sale(): void
    {
        $response = $this->getJson('/api/v1/sales/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Sale not found',
            ]);
    }

    /**
     * Testa se é possível criar venda com múltiplos itens.
     *
     * Verifica se a API permite criar uma venda com vários produtos
     * e se os totais são calculados corretamente.
     */
    public function test_can_create_sale_with_multiple_items(): void
    {
        $product1 = Product::factory()->create(['cost_price' => 100, 'sale_price' => 150]);
        $product2 = Product::factory()->create(['cost_price' => 200, 'sale_price' => 300]);

        Inventory::create(['product_id' => $product1->id, 'quantity' => 10]);
        Inventory::create(['product_id' => $product2->id, 'quantity' => 5]);

        $response = $this->postJson('/api/v1/sales', [
            'items' => [
                ['product_id' => $product1->id, 'quantity' => 2],
                ['product_id' => $product2->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201);

        $data = $response->json('data');
        $this->assertEquals(600, $data['total_amount']); // (150*2) + (300*1)
        $this->assertEquals(400, $data['total_cost']);   // (100*2) + (200*1)
        $this->assertEquals(200, $data['total_profit']);
        $this->assertCount(2, $data['items']);
    }
}
