<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateSaleRequest;
use App\Services\SaleService;
use Exception;
use Illuminate\Http\JsonResponse;

class SaleController extends Controller
{
    public function __construct(
        private SaleService $saleService
    ) {}

    /**
     * Registrar uma nova venda.
     *
     * @OA\Post(
     *     path="/sales",
     *     tags={"Vendas"},
     *     summary="Registrar uma nova venda",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"items"},
     *
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *
     *                 @OA\Items(
     *
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Venda criada com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Venda criada com sucesso"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erro de validação"),
     *     @OA\Response(response=400, description="Estoque insuficiente")
     * )
     */
    public function store(CreateSaleRequest $request): JsonResponse
    {
        try {
            $sale = $this->saleService->createSale($request->items);

            // Garantir que os relacionamentos estão carregados
            $sale->load('items.product');

            return response()->json([
                'message' => 'Venda criada com sucesso',
                'data' => [
                    'id' => $sale->id,
                    'total_amount' => $sale->total_amount,
                    'total_cost' => $sale->total_cost,
                    'total_profit' => $sale->total_profit,
                    'profit_margin' => $sale->profit_margin,
                    'status' => $sale->status,
                    'items' => $sale->items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'subtotal' => $item->subtotal,
                            'profit' => $item->profit,
                        ];
                    }),
                    'created_at' => $sale->created_at,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Falha ao criar venda',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Obter detalhes da venda.
     *
     * @OA\Get(
     *     path="/sales/{id}",
     *     tags={"Vendas"},
     *     summary="Obter detalhes da venda",
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
     *         description="Detalhes da venda recuperados com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=404, description="Venda não encontrada")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $sale = $this->saleService->getSaleDetails($id);

        if (! $sale) {
            return response()->json([
                'message' => 'Venda não encontrada',
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $sale->id,
                'total_amount' => $sale->total_amount,
                'total_cost' => $sale->total_cost,
                'total_profit' => $sale->total_profit,
                'profit_margin' => $sale->profit_margin,
                'status' => $sale->status,
                'items' => $sale->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name,
                        'product_sku' => $item->product->sku,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'unit_cost' => $item->unit_cost,
                        'subtotal' => $item->subtotal,
                        'total_cost' => $item->total_cost,
                        'profit' => $item->profit,
                    ];
                }),
                'created_at' => $sale->created_at,
                'updated_at' => $sale->updated_at,
            ],
        ]);
    }
}
