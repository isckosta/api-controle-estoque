<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AddInventoryRequest;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    public function __construct(
        private InventoryService $inventoryService
    ) {}

    /**
     * Registrar entrada de produtos no estoque.
     *
     * @OA\Post(
     *     path="/inventory",
     *     tags={"Estoque"},
     *     summary="Registrar entrada de produtos no estoque",
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             required={"product_id", "quantity"},
     *
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="quantity", type="integer", example=50)
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Estoque atualizado com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="Estoque atualizado com sucesso"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=422, description="Erro de validação")
     * )
     */
    public function store(AddInventoryRequest $request): JsonResponse
    {
        try {
            $inventory = $this->inventoryService->addStock(
                $request->product_id,
                $request->quantity
            );

            return response()->json([
                'message' => 'Estoque atualizado com sucesso',
                'data' => [
                    'product_id' => $inventory->product_id,
                    'product_name' => $inventory->product->name,
                    'quantity' => $inventory->quantity,
                    'last_updated' => $inventory->last_updated,
                ],
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Falha ao atualizar estoque',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Obter situação atual do estoque.
     *
     * @OA\Get(
     *     path="/inventory",
     *     tags={"Estoque"},
     *     summary="Obter situação atual do estoque",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Situação do estoque recuperada com sucesso",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="summary", type="object")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $inventory = $this->inventoryService->getInventoryStatus();
        $summary = $this->inventoryService->getInventorySummary();

        return response()->json([
            'data' => $inventory,
            'summary' => $summary,
        ]);
    }
}
