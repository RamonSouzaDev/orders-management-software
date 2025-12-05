<?php

declare(strict_types=1);

namespace App\Exceptions;

/**
 * Exceção para erros de validação do pedido.
 */
class OrderValidationException extends \Exception
{
    public function __construct(string $message)
    {
        parent::__construct($message, 422);
    }
}

