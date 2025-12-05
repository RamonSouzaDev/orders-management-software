<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Enums\OrderStatus;

/**
 * Exceção para transição de status inválida.
 */
class InvalidStatusTransitionException extends \Exception
{
    public function __construct(
        public readonly OrderStatus $from,
        public readonly OrderStatus $to,
    ) {
        $allowedTransitions = array_map(
            fn(OrderStatus $s) => $s->value,
            $from->allowedTransitions()
        );

        $message = sprintf(
            "Não é possível alterar o status de '%s' para '%s'. Transições permitidas: %s",
            $from->value,
            $to->value,
            empty($allowedTransitions) ? 'nenhuma (estado final)' : implode(', ', $allowedTransitions)
        );

        parent::__construct($message, 422);
    }
}

