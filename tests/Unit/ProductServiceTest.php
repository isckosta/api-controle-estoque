<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes unitários para o serviço de produtos.
 *
 * Esta classe contém testes para validar a lógica de negócio
 * do serviço de produtos, incluindo operações CRUD.
 */
class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    /**
     * Configura o ambiente de teste.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService;
    }

    /**
     * Testa se é possível obter todos os produtos.
     *
     * Verifica se o serviço retorna corretamente a lista
     * completa de produtos.
     */
    public function test_can_get_all_products(): void
    {
        Product::factory()->count(3)->create();

        $products = $this->productService->getAllProducts();

        $this->assertCount(3, $products);
    }

    /**
     * Testa se é possível criar um produto.
     *
     * Verifica se o serviço cria um produto com os dados
     * fornecidos e persiste no banco de dados.
     */
    public function test_can_create_product(): void
    {
        $data = [
            'sku' => 'TEST-001',
            'name' => 'Test Product',
            'description' => 'Test Description',
            'cost_price' => 100.00,
            'sale_price' => 150.00,
        ];

        $product = $this->productService->createProduct($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('TEST-001', $product->sku);
        $this->assertEquals('Test Product', $product->name);
        $this->assertDatabaseHas('products', ['sku' => 'TEST-001']);
    }

    /**
     * Testa se é possível obter produto por ID.
     *
     * Verifica se o serviço retorna o produto correto
     * quando buscado pelo ID.
     */
    public function test_can_get_product_by_id(): void
    {
        $product = Product::factory()->create(['sku' => 'TEST-001']);

        $found = $this->productService->getProductById($product->id);

        $this->assertNotNull($found);
        $this->assertEquals($product->id, $found->id);
        $this->assertEquals('TEST-001', $found->sku);
    }

    /**
     * Testa se retorna null quando produto não é encontrado.
     *
     * Verifica se o serviço retorna null ao buscar
     * um produto inexistente.
     */
    public function test_returns_null_when_product_not_found(): void
    {
        $found = $this->productService->getProductById(999);

        $this->assertNull($found);
    }

    /**
     * Testa se é possível atualizar um produto.
     *
     * Verifica se o serviço atualiza corretamente os dados
     * de um produto existente.
     */
    public function test_can_update_product(): void
    {
        $product = Product::factory()->create([
            'name' => 'Old Name',
            'sale_price' => 100,
        ]);

        $updated = $this->productService->updateProduct($product->id, [
            'name' => 'New Name',
            'sale_price' => 200,
        ]);

        $this->assertNotNull($updated);
        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals(200, $updated->sale_price);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'New Name',
        ]);
    }

    /**
     * Testa se update retorna null quando produto não existe.
     *
     * Verifica se o serviço retorna null ao tentar atualizar
     * um produto inexistente.
     */
    public function test_update_returns_null_when_product_not_found(): void
    {
        $updated = $this->productService->updateProduct(999, ['name' => 'Test']);

        $this->assertNull($updated);
    }

    /**
     * Testa se é possível deletar um produto.
     *
     * Verifica se o serviço remove corretamente um produto
     * do banco de dados.
     */
    public function test_can_delete_product(): void
    {
        $product = Product::factory()->create();

        $deleted = $this->productService->deleteProduct($product->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /**
     * Testa se delete retorna false quando produto não existe.
     *
     * Verifica se o serviço retorna false ao tentar deletar
     * um produto inexistente.
     */
    public function test_delete_returns_false_when_product_not_found(): void
    {
        $deleted = $this->productService->deleteProduct(999);

        $this->assertFalse($deleted);
    }

    /**
     * Testa se getAllProducts inclui dados de inventário.
     *
     * Verifica se o serviço carrega o relacionamento
     * de inventário ao listar produtos.
     */
    public function test_get_all_products_includes_inventory(): void
    {
        $product = Product::factory()->create();
        $product->inventory()->create(['quantity' => 10]);

        $products = $this->productService->getAllProducts();

        $this->assertTrue($products->first()->relationLoaded('inventory'));
        $this->assertEquals(10, $products->first()->inventory->quantity);
    }

    /**
     * Testa se getProductById inclui dados de inventário.
     *
     * Verifica se o serviço carrega o relacionamento
     * de inventário ao buscar um produto específico.
     */
    public function test_get_product_by_id_includes_inventory(): void
    {
        $product = Product::factory()->create();
        $product->inventory()->create(['quantity' => 20]);

        $found = $this->productService->getProductById($product->id);

        $this->assertTrue($found->relationLoaded('inventory'));
        $this->assertEquals(20, $found->inventory->quantity);
    }
}
