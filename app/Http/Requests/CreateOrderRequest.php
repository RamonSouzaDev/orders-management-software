<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Request para criação de pedido.
 */
class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'array'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'gt:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'O nome do cliente é obrigatório.',
            'customer_name.max' => 'O nome do cliente deve ter no máximo 255 caracteres.',
            'discount.numeric' => 'O desconto deve ser um valor numérico.',
            'discount.min' => 'O desconto não pode ser negativo.',
            'tax.numeric' => 'A taxa deve ser um valor numérico.',
            'tax.min' => 'A taxa não pode ser negativa.',
            'items.required' => 'O pedido deve ter pelo menos um item.',
            'items.min' => 'O pedido deve ter pelo menos um item.',
            'items.*.product_name.required' => 'O nome do produto é obrigatório.',
            'items.*.quantity.required' => 'A quantidade é obrigatória.',
            'items.*.quantity.integer' => 'A quantidade deve ser um número inteiro.',
            'items.*.quantity.min' => 'A quantidade deve ser pelo menos 1.',
            'items.*.unit_price.required' => 'O preço unitário é obrigatório.',
            'items.*.unit_price.numeric' => 'O preço unitário deve ser um valor numérico.',
            'items.*.unit_price.gt' => 'O preço unitário deve ser maior que 0.',
        ];
    }
}

