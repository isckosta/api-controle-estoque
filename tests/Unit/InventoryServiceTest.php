<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\Product;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes unitários para o serviço de inventário.
 *
 * Esta classe contém testes para validar a lógica de negócio
 * do serviço de inventário, incluindo adição de estoque e consultas.
 */
class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $inventoryService;

    /**
     * Configura o ambiente de teste.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->inventoryService = new InventoryService();
    }

    /**
     * Testa se é possível adicionar estoque a um produto novo.
     *
     * Verifica se o serviço cria um novo registro de inventário
     * quando o produto ainda não possui estoque.
     *
     * @return void
     */
    public function test_can_add_stock_to_new_product(): void
    {
        $product = Product::factory()->create();

        $inventory = $this->inventoryService->addStock($product->id, 50);

        $this->assertEquals(50, $inventory->quantity);
        $this->assertEquals($product->id, $inventory->product_id);
    }

    /**
     * Testa se é possível adicionar estoque a um inventário existente.
     *
     * Verifica se o serviço incrementa corretamente a quantidade
     * quando o produto já possui estoque.
     *
     * @return void
     */
    public function test_can_add_stock_to_existing_inventory(): void
    {
        $product = Product::factory()->create();
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 30,
        ]);

        $inventory = $this->inventoryService->addStock($product->id, 20);

        $this->assertEquals(50, $inventory->quantity);
    }

    /**
     * Testa se é possível verificar disponibilidade de estoque.
     *
     * Verifica se o serviço retorna corretamente se há ou não
     * estoque suficiente para uma determinada quantidade.
     *
     * @return void
     */
    public function test_can_check_stock_availability(): void
    {
        $product = Product::factory()->create();
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $this->assertTrue($this->inventoryService->hasStock($product->id, 5));
        $this->assertTrue($this->inventoryService->hasStock($product->id, 10));
        $this->assertFalse($this->inventoryService->hasStock($product->id, 15));
    }

    /**
     * Testa se é possível obter o status do inventário.
     *
     * Verifica se o serviço retorna corretamente as informações
     * de estoque com cálculos de custos e valores.
     *
     * @return void
     */
    public function test_can_get_inventory_status(): void
    {
        $product = Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ]);
        Inventory::create([
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $status = $this->inventoryService->getInventoryStatus();

        $this->assertCount(1, $status);
        $this->assertEquals(10, $status->first()['quantity']);
        $this->assertEquals(1000, $status->first()['total_cost']);
        $this->assertEquals(1500, $status->first()['total_value']);
        $this->assertEquals(500, $status->first()['projected_profit']);
    }

    /**
     * Testa se é possível obter o resumo do inventário.
     *
     * Verifica se o serviço calcula corretamente os totais
     * de itens, unidades, custos, valores e lucros.
     *
     * @return void
     */
    public function test_can_get_inventory_summary(): void
    {
        Product::factory()->create([
            'cost_price' => 100,
            'sale_price' => 150,
        ])->inventory()->create(['quantity' => 10]);

        Product::factory()->create([
            'cost_price' => 200,
            'sale_price' => 300,
        ])->inventory()->create(['quantity' => 5]);

        $summary = $this->inventoryService->getInventorySummary();

        $this->assertEquals(2, $summary['total_items']);
        $this->assertEquals(15, $summary['total_units']);
        $this->assertEquals(2000, $summary['total_cost']);
        $this->assertEquals(3000, $summary['total_value']);
        $this->assertEquals(1000, $summary['projected_profit']);
    }
}
