<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddInventoryRequest extends FormRequest
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
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Obter mensagens personalizadas para erros de validação.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'ID do produto é obrigatório',
            'product_id.exists' => 'Produto não encontrado',
            'quantity.required' => 'Quantidade é obrigatória',
            'quantity.min' => 'Quantidade deve ser no mínimo 1',
        ];
    }
}
