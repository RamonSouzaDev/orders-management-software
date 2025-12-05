<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\OrderStatus;

/**
 * DTO para criação de Pedido.
 */
readonly class CreateOrderDTO
{
    /**
     * @param array<OrderItemDTO> $items
     */
    public function __construct(
        public string $customerName,
        public array $items,
        public ?float $discount = null,
        public ?float $tax = null,
        public ?array $notes = null,
    ) {
    }

    /**
     * Cria DTO a partir de array de request.
     */
    public static function fromArray(array $data): self
    {
        $items = [];
        foreach ($data['items'] ?? [] as $itemData) {
            $items[] = OrderItemDTO::fromArray($itemData);
        }

        return new self(
            customerName: $data['customer_name'],
            items: $items,
            discount: isset($data['discount']) ? (float) $data['discount'] : null,
            tax: isset($data['tax']) ? (float) $data['tax'] : null,
            notes: $data['notes'] ?? null,
        );
    }

    /**
     * Converte para array.
     */
    public function toArray(): array
    {
        return [
            'customer_name' => $this->customerName,
            'status' => OrderStatus::DRAFT->value,
            'discount' => $this->discount ?? 0,
            'tax' => $this->tax ?? 0,
            'notes' => $this->notes,
        ];
    }

    /**
     * Valida os dados do pedido.
     *
     * @throws \InvalidArgumentException
     */
    public function validate(): void
    {
        if (empty($this->customerName)) {
            throw new \InvalidArgumentException('Nome do cliente é obrigatório.');
        }

        if (empty($this->items)) {
            throw new \InvalidArgumentException('Pedido deve ter pelo menos um item.');
        }

        if ($this->discount !== null && $this->discount < 0) {
            throw new \InvalidArgumentException('Desconto não pode ser negativo.');
        }

        if ($this->tax !== null && $this->tax < 0) {
            throw new \InvalidArgumentException('Taxa não pode ser negativa.');
        }

        foreach ($this->items as $item) {
            $item->validate();
        }
    }

    /**
     * Calcula o subtotal do pedido.
     */
    public function calculateSubtotal(): float
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item->calculateTotalPrice();
        }
        return round($subtotal, 2);
    }

    /**
     * Calcula o total do pedido.
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->calculateSubtotal();
        $discount = $this->discount ?? 0;
        $tax = $this->tax ?? 0;

        return max(0, round($subtotal - $discount + $tax, 2));
    }
}

