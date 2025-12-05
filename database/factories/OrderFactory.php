<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para criação de pedidos em testes.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 5000);
        $discount = $this->faker->randomFloat(2, 0, $subtotal * 0.2);
        $tax = $this->faker->randomFloat(2, 0, $subtotal * 0.1);

        return [
            'customer_name' => $this->faker->name(),
            'status' => OrderStatus::DRAFT,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => max(0, $subtotal - $discount + $tax),
            'notes' => null,
        ];
    }

    /**
     * Pedido com status draft.
     */
    public function draft(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::DRAFT,
        ]);
    }

    /**
     * Pedido com status pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::PENDING,
        ]);
    }

    /**
     * Pedido com status paid.
     */
    public function paid(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::PAID,
        ]);
    }

    /**
     * Pedido com status cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => OrderStatus::CANCELLED,
        ]);
    }
}

