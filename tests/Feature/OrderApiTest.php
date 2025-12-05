<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testes de integração da API de Pedidos.
 */
class OrderApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Testa criação de pedido via API.
     */
    public function test_can_create_order_via_api(): void
    {
        $payload = [
            'customer_name' => 'Cliente Teste',
            'discount' => 10.00,
            'tax' => 5.00,
            'items' => [
                [
                    'product_name' => 'Produto 1',
                    'quantity' => 2,
                    'unit_price' => 50.00,
                ],
                [
                    'product_name' => 'Produto 2',
                    'quantity' => 1,
                    'unit_price' => 100.00,
                ],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'id',
                    'customer_name',
                    'status',
                    'subtotal',
                    'discount',
                    'tax',
                    'total',
                    'items',
                ],
            ]);

        // Verifica cálculos
        // Subtotal: (2 * 50) + (1 * 100) = 200
        // Total: 200 - 10 + 5 = 195
        $response->assertJsonPath('data.subtotal', 200.0);
        $response->assertJsonPath('data.total', 195.0);
        $response->assertJsonPath('data.status', 'draft');
    }

    /**
     * Testa validação de campos obrigatórios.
     */
    public function test_validation_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_name', 'items']);
    }

    /**
     * Testa que pedido não pode ser criado como paid.
     */
    public function test_order_is_created_as_draft(): void
    {
        $payload = [
            'customer_name' => 'Cliente',
            'items' => [
                ['product_name' => 'Produto', 'quantity' => 1, 'unit_price' => 100],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', 'draft');
    }

    /**
     * Testa validação de quantidade mínima.
     */
    public function test_item_quantity_must_be_at_least_one(): void
    {
        $payload = [
            'customer_name' => 'Cliente',
            'items' => [
                ['product_name' => 'Produto', 'quantity' => 0, 'unit_price' => 100],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.quantity']);
    }

    /**
     * Testa validação de preço unitário.
     */
    public function test_item_unit_price_must_be_greater_than_zero(): void
    {
        $payload = [
            'customer_name' => 'Cliente',
            'items' => [
                ['product_name' => 'Produto', 'quantity' => 1, 'unit_price' => 0],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.unit_price']);
    }
}

