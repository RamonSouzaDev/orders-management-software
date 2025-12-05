<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\OrderStatus;

/**
 * DTO para atualização de status do Pedido.
 */
readonly class UpdateStatusDTO
{
    public function __construct(
        public OrderStatus $status,
    ) {
    }

    /**
     * Cria DTO a partir de array de request.
     */
    public static function fromArray(array $data): self
    {
        $status = OrderStatus::tryFrom($data['status'] ?? '');

        if ($status === null) {
            throw new \InvalidArgumentException(
                'Status inválido. Valores permitidos: ' . implode(', ', OrderStatus::values())
            );
        }

        return new self(status: $status);
    }
}

