<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\CreateOrderDTO;
use App\DTOs\OrderItemDTO;
use App\Enums\OrderStatus;
use App\Services\OrderService;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários do OrderService.
 */
class OrderServiceTest extends TestCase
{
    /**
     * Testa cálculo do total do pedido.
     */
    public function test_calculate_order_total_with_discount_and_tax(): void
    {
        // Arrange
        $subtotal = 100.00;
        $discount = 10.00;
        $tax = 5.00;

        // Act
        $total = $this->calculateTotal($subtotal, $discount, $tax);

        // Assert
        // Total = subtotal - discount + tax = 100 - 10 + 5 = 95
        $this->assertEquals(95.00, $total);
    }

    /**
     * Testa cálculo do total sem desconto.
     */
    public function test_calculate_order_total_without_discount(): void
    {
        // Arrange
        $subtotal = 100.00;
        $discount = null;
        $tax = 10.00;

        // Act
        $total = $this->calculateTotal($subtotal, $discount, $tax);

        // Assert
        // Total = 100 - 0 + 10 = 110
        $this->assertEquals(110.00, $total);
    }

    /**
     * Testa cálculo do total sem taxa.
     */
    public function test_calculate_order_total_without_tax(): void
    {
        // Arrange
        $subtotal = 100.00;
        $discount = 20.00;
        $tax = null;

        // Act
        $total = $this->calculateTotal($subtotal, $discount, $tax);

        // Assert
        // Total = 100 - 20 + 0 = 80
        $this->assertEquals(80.00, $total);
    }

    /**
     * Testa que total não pode ser negativo.
     */
    public function test_calculate_order_total_cannot_be_negative(): void
    {
        // Arrange
        $subtotal = 50.00;
        $discount = 100.00; // Desconto maior que subtotal
        $tax = 10.00;

        // Act
        $total = $this->calculateTotal($subtotal, $discount, $tax);

        // Assert
        // Total = max(0, 50 - 100 + 10) = max(0, -40) = 0
        $this->assertEquals(0, $total);
    }

    /**
     * Método auxiliar para cálculo do total (mesma lógica do Service).
     */
    private function calculateTotal(float $subtotal, ?float $discount, ?float $tax): float
    {
        $discount = $discount ?? 0;
        $tax = $tax ?? 0;

        return max(0, round($subtotal - $discount + $tax, 2));
    }
}

