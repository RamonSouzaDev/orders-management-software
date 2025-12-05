<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * DTO para Item do Pedido.
 */
readonly class OrderItemDTO
{
    public function __construct(
        public string $productName,
        public int $quantity,
        public float $unitPrice,
    ) {
    }

    /**
     * Cria DTO a partir de array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            productName: $data['product_name'],
            quantity: (int) $data['quantity'],
            unitPrice: (float) $data['unit_price'],
        );
    }

    /**
     * Converte para array.
     */
    public function toArray(): array
    {
        return [
            'product_name' => $this->productName,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total_price' => $this->calculateTotalPrice(),
        ];
    }

    /**
     * Calcula o preço total do item.
     */
    public function calculateTotalPrice(): float
    {
        return round($this->quantity * $this->unitPrice, 2);
    }

    /**
     * Valida os dados do item.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if (empty($this->productName)) {
            throw new \InvalidArgumentException('Nome do produto é obrigatório.');
        }

        if ($this->quantity < 1) {
            throw new \InvalidArgumentException('Quantidade deve ser maior ou igual a 1.');
        }

        if ($this->unitPrice <= 0) {
            throw new \InvalidArgumentException('Preço unitário deve ser maior que 0.');
        }
    }
}

