<?php

namespace Tests\Unit;

use App\Events\SaleCompleted;
use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Testes unitários para o serviço de vendas.
 *
 * Esta classe contém testes para validar a lógica de negócio
 * do serviço de vendas, incluindo criação e cálculos.
 */
class SaleServiceTest extends TestCase
{
    use RefreshDatabase;

    private SaleService $saleService;

    /**
     * Configura o ambiente de teste.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->saleService = new SaleService(new InventoryService);
    }

    /**
     * Testa se é possível criar venda com um único item.
     *
     * Verifica se o serviço cria uma venda corretamente,
     * calcula os totais e dispara o evento SaleCompleted.
     */
    public function test_can_create_sale_with_single_item(): void
    {
        Event::fake();

        $product = Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ]);
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $sale = $this->saleService->createSale([
            ['product_id' => $product->id, 'quantity' => 2],
        ]);

        $this->assertEquals(300, $sale->total_amount);
        $this->assertEquals(200, $sale->total_cost);
        $this->assertEquals(100, $sale->total_profit);
        $this->assertEquals('completed', $sale->status);
        $this->assertCount(1, $sale->items);

        Event::assertDispatched(SaleCompleted::class);
    }

    /**
     * Testa se é possível criar venda com múltiplos itens.
     *
     * Verifica se o serviço cria uma venda com vários produtos
     * e calcula corretamente os totais.
     */
    public function test_can_create_sale_with_multiple_items(): void
    {
        Event::fake();

        $product1 = Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ]);
        Inventory::create(['product_id' => $product1->id, 'quantity' => 10]);

        $product2 = Product::factory()->create([
            'cost_price' => 200,
            'sale_price' => 300,
        ]);
        Inventory::create(['product_id' => $product2->id, 'quantity' => 5]);

        $sale = $this->saleService->createSale([
            ['product_id' => $product1->id, 'quantity' => 2],
            ['product_id' => $product2->id, 'quantity' => 1],
        ]);

        $this->assertEquals(600, $sale->total_amount); // (150*2) + (300*1)
        $this->assertEquals(400, $sale->total_cost);   // (100*2) + (200*1)
        $this->assertEquals(200, $sale->total_profit);
        $this->assertCount(2, $sale->items);
    }

    /**
     * Testa se não é possível criar venda com estoque insuficiente.
     *
     * Verifica se o serviço lança exceção quando tenta criar
     * uma venda sem estoque suficiente.
     */
    public function test_cannot_create_sale_with_insufficient_stock(): void
    {
        $product = Product::factory()->create();
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->expectException(\Exception::class);

        $this->saleService->createSale([
            ['product_id' => $product->id, 'quantity' => 10],
        ]);
    }

    /**
     * Testa se é possível obter detalhes de uma venda.
     *
     * Verifica se o serviço retorna corretamente os detalhes
     * de uma venda específica com seus itens.
     */
    public function test_can_get_sale_details(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ]);
        Inventory::create(['product_id' => $product->id, 'quantity' => 10]);

        $sale = $this->saleService->createSale([
            ['product_id' => $product->id, 'quantity' => 2],
        ]);

        $saleDetails = $this->saleService->getSaleDetails($sale->id);

        $this->assertNotNull($saleDetails);
        $this->assertEquals($sale->id, $saleDetails->id);
        $this->assertCount(1, $saleDetails->items);
    }

    /**
     * Testa se a margem de lucro é calculada corretamente.
     *
     * Verifica se o serviço calcula a margem de lucro
     * percentual corretamente.
     */
    public function test_sale_calculates_profit_margin_correctly(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 80,
            'sale_price' => 100,
        ]);
        Inventory::create(['product_id' => $product->id, 'quantity' => 10]);

        $sale = $this->saleService->createSale([
            ['product_id' => $product->id, 'quantity' => 1],
        ]);

        $this->assertEquals(20, $sale->profit_margin);
    }
}
