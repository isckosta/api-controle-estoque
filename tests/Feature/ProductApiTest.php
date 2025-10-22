<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de API para gerenciamento de produtos.
 *
 * Esta classe contém testes para validar as operações CRUD de produtos,
 * incluindo criação, listagem, atualização, exclusão e validações.
 */
class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa se é possível listar todos os produtos.
     *
     * Verifica se a API retorna corretamente a lista de produtos
     * com todas as informações necessárias.
     */
    public function test_can_list_all_products(): void
    {
        Product::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'sku',
                        'name',
                        'description',
                        'cost_price',
                        'sale_price',
                        'profit_margin',
                        'unit_profit',
                        'stock_quantity',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    /**
     * Testa se é possível criar um novo produto.
     *
     * Verifica se a API permite criar um produto com dados válidos
     * e se os dados são persistidos corretamente no banco.
     */
    public function test_can_create_product(): void
    {
        $productData = [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'description' => 'Test Description',
            'cost_price' => 100.00,
            'sale_price' => 150.00,
        ];

        $response = $this->postJson('/api/v1/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Produto criado com sucesso',
                'data' => [
                    'sku' => 'TEST-001',
                    'name' => 'Test Product',
                    'cost_price' => '100.00',
                    'sale_price' => '150.00',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
        ]);
    }

    /**
     * Testa se a validação de campos obrigatórios funciona na criação.
     *
     * Verifica se a API retorna erros de validação quando campos
     * obrigatórios não são fornecidos.
     */
    public function test_create_product_validates_required_fields(): void
    {
        $response = $this->postJson('/api/v1/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku', 'name', 'cost_price', 'sale_price']);
    }

    /**
     * Testa se a validação de SKU único funciona.
     *
     * Verifica se a API impede a criação de produtos com SKU duplicado.
     */
    public function test_create_product_validates_unique_sku(): void
    {
        Product::factory()->create(['sku' => 'DUPLICATE-SKU']);

        $response = $this->postJson('/api/v1/products', [
            'sku' => 'DUPLICATE-SKU',
            'name' => 'Test Product',
            'cost_price' => 100,
            'sale_price' => 150,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /**
     * Testa se o preço de venda deve ser maior que o custo.
     *
     * Verifica se a API valida que o preço de venda é maior
     * que o preço de custo.
     */
    public function test_create_product_validates_sale_price_greater_than_cost(): void
    {
        $response = $this->postJson('/api/v1/products', [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'cost_price' => 150,
            'sale_price' => 100,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sale_price']);
    }

    /**
     * Testa se é possível obter detalhes de um produto.
     *
     * Verifica se a API retorna corretamente as informações
     * detalhadas de um produto específico.
     */
    public function test_can_get_product_details(): void
    {
        $product = Product::factory()->create([
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'cost_price' => 100,
            'sale_price' => 150,
        ]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $product->id,
                    'sku' => 'TEST-001',
                    'name' => 'Test Product',
                    'cost_price' => '100.00',
                    'sale_price' => '150.00',
                ],
            ]);
    }

    /**
     * Testa se retorna 404 para produto inexistente.
     *
     * Verifica se a API retorna erro 404 quando tenta buscar
     * um produto que não existe.
     */
    public function test_get_product_returns_404_for_nonexistent_product(): void
    {
        $response = $this->getJson('/api/v1/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Produto não encontrado',
            ]);
    }

    /**
     * Testa se é possível atualizar um produto.
     *
     * Verifica se a API permite atualizar dados de um produto
     * existente e se as alterações são persistidas.
     */
    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'sku' => 'OLD-SKU',
            'name' => 'Old Name',
            'cost_price' => 100,
            'sale_price' => 150,
        ]);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'name' => 'Updated Name',
            'cost_price' => 100,
            'sale_price' => 200,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Produto atualizado com sucesso',
                'data' => [
                    'name' => 'Updated Name',
                    'sale_price' => '200.00',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'sale_price' => 200,
        ]);
    }

    /**
     * Testa se a validação de SKU único funciona na atualização.
     *
     * Verifica se a API impede atualizar um produto com SKU
     * que já pertence a outro produto.
     */
    public function test_update_product_validates_unique_sku(): void
    {
        $product1 = Product::factory()->create(['sku' => 'SKU-001']);
        $product2 = Product::factory()->create(['sku' => 'SKU-002']);

        $response = $this->putJson("/api/v1/products/{$product2->id}", [
            'sku' => 'SKU-001',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }

    /**
     * Testa se pode atualizar produto mantendo o mesmo SKU.
     *
     * Verifica se a API permite atualizar um produto sem alterar
     * seu SKU (validação de unicidade deve ignorar o próprio produto).
     */
    public function test_can_update_product_with_same_sku(): void
    {
        $product = Product::factory()->create(['sku' => 'SKU-001', 'name' => 'Old Name']);

        $response = $this->putJson("/api/v1/products/{$product->id}", [
            'sku' => 'SKU-001',
            'name' => 'New Name',
        ]);

        $response->assertStatus(200);
    }

    /**
     * Testa se é possível deletar um produto.
     *
     * Verifica se a API permite excluir um produto existente
     * e se ele é removido do banco de dados.
     */
    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Produto deletado com sucesso',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Testa se retorna 404 ao deletar produto inexistente.
     *
     * Verifica se a API retorna erro 404 quando tenta deletar
     * um produto que não existe.
     */
    public function test_delete_product_returns_404_for_nonexistent_product(): void
    {
        $response = $this->deleteJson('/api/v1/products/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'Produto não encontrado',
            ]);
    }

    /**
     * Testa se a listagem de produtos inclui quantidade em estoque.
     *
     * Verifica se a API retorna a quantidade em estoque de cada
     * produto na listagem.
     */
    public function test_product_list_includes_stock_quantity(): void
    {
        $product = Product::factory()->create();
        $product->inventory()->create(['quantity' => 50]);

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200);

        $productData = collect($response->json('data'))->firstWhere('id', $product->id);
        $this->assertEquals(50, $productData['stock_quantity']);
    }

    /**
     * Testa se os detalhes do produto incluem informações de inventário.
     *
     * Verifica se a API retorna informações detalhadas do inventário
     * ao buscar um produto específico.
     */
    public function test_product_details_include_inventory_info(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ]);
        $product->inventory()->create(['quantity' => 10]);

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'inventory' => [
                        'quantity',
                        'total_cost',
                        'total_value',
                        'projected_profit',
                        'last_updated',
                    ],
                ],
            ]);

        $inventory = $response->json('data.inventory');
        $this->assertEquals(10, $inventory['quantity']);
        $this->assertEquals(1000, $inventory['total_cost']);
        $this->assertEquals(1500, $inventory['total_value']);
        $this->assertEquals(500, $inventory['projected_profit']);
    }
}
