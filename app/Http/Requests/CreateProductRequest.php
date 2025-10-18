<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
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
        return [
            'sku' => ['required', 'string', 'max:255', 'unique:products,sku'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['required', 'numeric', 'min:0', 'gte:cost_price'],
        ];
    }

    /**
     * Obter mensagens personalizadas para erros de validação.
     */
    public function messages(): array
    {
        return [
            'sku.required' => 'SKU é obrigatório',
            'sku.unique' => 'SKU já existe',
            'name.required' => 'Nome do produto é obrigatório',
            'cost_price.required' => 'Preço de custo é obrigatório',
            'cost_price.min' => 'Preço de custo deve ser maior ou igual a 0',
            'sale_price.required' => 'Preço de venda é obrigatório',
            'sale_price.min' => 'Preço de venda deve ser maior ou igual a 0',
            'sale_price.gte' => 'Preço de venda deve ser maior ou igual ao preço de custo',
        ];
    }
}
