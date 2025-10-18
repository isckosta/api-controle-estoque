<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductService
{
    /**
     * Listar todos os produtos com informações de estoque.
     */
    public function getAllProducts(): Collection
    {
        return Product::with('inventory')->get();
    }

    /**
     * Criar um novo produto.
     * 
     * @throws Exception
     */
    public function createProduct(array $data): Product
    {
        try {
            return DB::transaction(function () use ($data) {
                return Product::create($data);
            });
        } catch (Exception $e) {
            Log::error('Erro ao criar produto', [
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao criar produto: ' . $e->getMessage());
        }
    }

    /**
     * Obter detalhes de um produto específico.
     */
    public function getProductById(int $id): ?Product
    {
        return Product::with('inventory')->find($id);
    }

    /**
     * Atualizar um produto existente.
     * 
     * @throws Exception
     */
    public function updateProduct(int $id, array $data): ?Product
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $product = Product::find($id);

                if (!$product) {
                    return null;
                }

                $product->update($data);

                return $product->fresh();
            });
        } catch (Exception $e) {
            Log::error('Erro ao atualizar produto', [
                'product_id' => $id,
                'data' => $data,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao atualizar produto: ' . $e->getMessage());
        }
    }

    /**
     * Deletar um produto.
     * 
     * @throws Exception
     */
    public function deleteProduct(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $product = Product::find($id);

                if (!$product) {
                    return false;
                }

                return $product->delete();
            });
        } catch (Exception $e) {
            Log::error('Erro ao deletar produto', [
                'product_id' => $id,
                'error' => $e->getMessage(),
            ]);
            throw new Exception('Falha ao deletar produto: ' . $e->getMessage());
        }
    }
}
