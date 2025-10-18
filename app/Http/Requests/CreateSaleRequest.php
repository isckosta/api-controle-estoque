<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSaleRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Obter mensagens personalizadas para erros de validação.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'Pelo menos um item é obrigatório',
            'items.*.product_id.required' => 'ID do produto é obrigatório para todos os itens',
            'items.*.product_id.exists' => 'Um ou mais produtos não foram encontrados',
            'items.*.quantity.required' => 'Quantidade é obrigatória para todos os itens',
            'items.*.quantity.min' => 'Quantidade deve ser no mínimo 1 para todos os itens',
        ];
    }
}
