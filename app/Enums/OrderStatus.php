<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Enum para status do pedido com regras de transição.
 */
enum OrderStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';

    /**
     * Retorna os status permitidos para transição.
     *
     * @return array<OrderStatus>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::DRAFT => [self::PENDING],
            self::PENDING => [self::PAID, self::CANCELLED],
            self::PAID => [], // Estado final
            self::CANCELLED => [], // Estado final
        };
    }

    /**
     * Verifica se pode transicionar para outro status.
     */
    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions(), true);
    }

    /**
     * Retorna label amigável.
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::PENDING => 'Pendente',
            self::PAID => 'Pago',
            self::CANCELLED => 'Cancelado',
        };
    }

    /**
     * Retorna cor para exibição no frontend.
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => '#6b7280',
            self::PENDING => '#f59e0b',
            self::PAID => '#10b981',
            self::CANCELLED => '#ef4444',
        };
    }

    /**
     * Verifica se é um estado final.
     */
    public function isFinal(): bool
    {
        return in_array($this, [self::PAID, self::CANCELLED], true);
    }

    /**
     * Retorna todos os valores como array.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

