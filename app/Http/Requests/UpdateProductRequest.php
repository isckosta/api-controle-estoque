<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determinar se o usuário está autorizado a fazer esta requisição.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obter as regras de validação que se aplicam à requisição.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'sku' => ['sometimes', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($productId)],
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'numeric', 'min:0', 'gte:cost_price'],
        ];
    }

    /**
     * Obter mensagens personalizadas para erros de validação.
     */
    public function messages(): array
    {
        return [
            'sku.unique' => 'SKU já existe',
            'cost_price.min' => 'Preço de custo deve ser maior ou igual a 0',
            'sale_price.min' => 'Preço de venda deve ser maior ou igual a 0',
            'sale_price.gte' => 'Preço de venda deve ser maior ou igual ao preço de custo',
        ];
    }
}
