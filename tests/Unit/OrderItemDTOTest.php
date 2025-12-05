<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DTOs\OrderItemDTO;
use PHPUnit\Framework\TestCase;

/**
 * Testes unitários do OrderItemDTO.
 */
class OrderItemDTOTest extends TestCase
{
    /**
     * Testa cálculo do preço total do item.
     */
    public function test_calculates_total_price_correctly(): void
    {
        // Arrange
        $item = new OrderItemDTO(
            productName: 'Produto Teste',
            quantity: 3,
            unitPrice: 25.50
        );

        // Act & Assert
        // Total = 3 * 25.50 = 76.50
        $this->assertEquals(76.50, $item->calculateTotalPrice());
    }

    /**
     * Testa criação de DTO a partir de array.
     */
    public function test_creates_from_array(): void
    {
        // Arrange
        $data = [
            'product_name' => 'Produto',
            'quantity' => 2,
            'unit_price' => 10.00,
        ];

        // Act
        $item = OrderItemDTO::fromArray($data);

        // Assert
        $this->assertEquals('Produto', $item->productName);
        $this->assertEquals(2, $item->quantity);
        $this->assertEquals(10.00, $item->unitPrice);
    }

    /**
     * Testa conversão para array.
     */
    public function test_converts_to_array(): void
    {
        // Arrange
        $item = new OrderItemDTO(
            productName: 'Produto',
            quantity: 2,
            unitPrice: 10.00
        );

        // Act
        $array = $item->toArray();

        // Assert
        $this->assertEquals([
            'product_name' => 'Produto',
            'quantity' => 2,
            'unit_price' => 10.00,
            'total_price' => 20.00,
        ], $array);
    }

    /**
     * Testa validação de quantidade mínima.
     */
    public function test_validates_minimum_quantity(): void
    {
        // Arrange
        $item = new OrderItemDTO(
            productName: 'Produto',
            quantity: 0,
            unitPrice: 10.00
        );

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Quantidade deve ser maior ou igual a 1');

        // Act
        $item->validate();
    }

    /**
     * Testa validação de preço unitário.
     */
    public function test_validates_unit_price_greater_than_zero(): void
    {
        // Arrange
        $item = new OrderItemDTO(
            productName: 'Produto',
            quantity: 1,
            unitPrice: 0
        );

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Preço unitário deve ser maior que 0');

        // Act
        $item->validate();
    }

    /**
     * Testa validação de nome do produto.
     */
    public function test_validates_product_name_required(): void
    {
        // Arrange
        $item = new OrderItemDTO(
            productName: '',
            quantity: 1,
            unitPrice: 10.00
        );

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nome do produto é obrigatório');

        // Act
        $item->validate();
    }

    /**
     * Testa que validação passa com dados corretos.
     */
    public function test_validation_passes_with_valid_data(): void
    {
        // Arrange
        $item = new OrderItemDTO(
            productName: 'Produto Válido',
            quantity: 1,
            unitPrice: 10.00
        );

        // Act & Assert - não deve lançar exceção
        $item->validate();
        $this->assertTrue(true);
    }

    /**
     * Testa precisão decimal no cálculo.
     */
    public function test_decimal_precision_in_calculation(): void
    {
        // Arrange
        $item = new OrderItemDTO(
            productName: 'Produto',
            quantity: 3,
            unitPrice: 9.99
        );

        // Act & Assert
        // 3 * 9.99 = 29.97
        $this->assertEquals(29.97, $item->calculateTotalPrice());
    }
}

