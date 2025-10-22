<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Listar todos os produtos.
     *
     * @OA\Get(
     *     path="/products",
     *     tags={"Produtos"},
     *     summary="Listar todos os produtos",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Produtos recuperados com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $products = $this->productService->getAllProducts()->map(function ($product) {
            return [
                'id'             => $product->id,
                'sku'            => $product->sku,
                'name'           => $product->name,
                'description'    => $product->description,
                'cost_price'     => $product->cost_price, // Preço de custo
                'sale_price'     => $product->sale_price, // Preço de venda
                'profit_margin'  => round($product->profit_margin, 2), // Margem de lucro
                'unit_profit'    => $product->unit_profit, // Lucro unitário
                'stock_quantity' => $product->inventory?->quantity ?? 0, // Quantidade em estoque
                'created_at'     => $product->created_at,
                'updated_at'     => $product->updated_at,
            ];
        });

        return response()->json([
            'data' => $products,
        ]);
    }

    /**
     * Criar um novo produto.
     *
     * @OA\Post(
     *     path="/products",
     *     tags={"Produtos"},
     *     summary="Criar um novo produto",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"sku", "name", "cost_price", "sale_price"},
     *
     *             @OA\Property(property="sku", type="string", example="PROD-001"),
     *             @OA\Property(property="name", type="string", example="Nome do Produto"),
     *             @OA\Property(property="description", type="string", example="Descrição do produto"),
     *             @OA\Property(property="cost_price", type="number", format="float", example=100.00),
     *             @OA\Property(property="sale_price", type="number", format="float", example=150.00)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Produto criado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Produto criado com sucesso"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(CreateProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->createProduct($request->validated());

            return response()->json([
                'message' => 'Produto criado com sucesso',
                'data' => [
                    'id'            => $product->id,
                    'sku'           => $product->sku,
                    'name'          => $product->name,
                    'description'   => $product->description,
                    'cost_price'    => $product->cost_price, // Preço de custo
                    'sale_price'    => $product->sale_price, // Preço de venda
                    'profit_margin' => round($product->profit_margin, 2), // Margem de lucro
                    'unit_profit'   => $product->unit_profit, // Lucro unitário
                    'created_at'    => $product->created_at,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Falha ao criar produto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obter detalhes do produto.
     *
     * @OA\Get(
     *     path="/products/{id}",
     *     tags={"Produtos"},
     *     summary="Obter detalhes do produto",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do produto recuperados com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Produto não encontrado")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->productService->getProductById($id);

        if (! $product) {
            return response()->json([
                'message' => 'Produto não encontrado',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id'            => $product->id,
                'sku'           => $product->sku,
                'name'          => $product->name,
                'description'   => $product->description,
                'cost_price'    => $product->cost_price, // Preço de custo
                'sale_price'    => $product->sale_price, // Preço de venda
                'profit_margin' => round($product->profit_margin, 2), // Margem de lucro ex.: 20%
                'unit_profit'   => $product->unit_profit, // Lucro unitário
                'inventory'     => $product->inventory ? [
                    'quantity'         => $product->inventory->quantity,
                    'total_cost'       => $product->inventory->total_cost, // Custo total
                    'total_value'      => $product->inventory->total_value, // Valor total
                    'projected_profit' => $product->inventory->projected_profit, // Lucro projetado
                    'last_updated'     => $product->inventory->last_updated,
                ] : null,
                'created_at' => $product->created_at,
                'updated_at' => $product->updated_at,
            ],
        ]);
    }

    /**
     * Atualizar um produto.
     *
     * @OA\Put(
     *     path="/products/{id}",
     *     tags={"Produtos"},
     *     summary="Atualizar um produto",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="sku", type="string", example="PROD-001"),
     *             @OA\Property(property="name", type="string", example="Nome do Produto"),
     *             @OA\Property(property="description", type="string", example="Descrição do produto"),
     *             @OA\Property(property="cost_price", type="number", format="float", example=100.00),
     *             @OA\Property(property="sale_price", type="number", format="float", example=150.00)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Produto atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Produto atualizado com sucesso"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Produto não encontrado"),
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->updateProduct($id, $request->validated());

            if (! $product) {
                return response()->json([
                    'message' => 'Produto não encontrado',
                ], 404);
            }

            return response()->json([
                'message' => 'Produto atualizado com sucesso',
                'data' => [
                    'id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'description' => $product->description,
                    'cost_price' => $product->cost_price,
                    'sale_price' => $product->sale_price,
                    'profit_margin' => round($product->profit_margin, 2),
                    'unit_profit' => $product->unit_profit,
                    'updated_at' => $product->updated_at,
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Falha ao atualizar produto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Deletar um produto.
     *
     * @OA\Delete(
     *     path="/products/{id}",
     *     tags={"Produtos"},
     *     summary="Deletar um produto",
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Produto deletado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Produto deletado com sucesso")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Produto não encontrado")
     * )
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->productService->deleteProduct($id);

            if (! $deleted) {
                return response()->json([
                    'message' => 'Produto não encontrado',
                ], 404);
            }

            return response()->json([
                'message' => 'Produto deletado com sucesso',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Falha ao deletar produto',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
