<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface do repositório de pedidos.
 */
interface OrderRepositoryInterface
{
    /**
     * Busca todos os pedidos com filtros opcionais.
     */
    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Busca pedido por ID.
     */
    public function findById(string $id): ?Order;

    /**
     * Busca pedido por ID ou lança exceção.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(string $id): Order;

    /**
     * Cria um novo pedido.
     */
    public function create(array $data): Order;

    /**
     * Atualiza um pedido.
     */
    public function update(Order $order, array $data): Order;

    /**
     * Exclui um pedido (soft delete).
     */
    public function delete(Order $order): bool;

    /**
     * Adiciona itens ao pedido.
     */
    public function addItems(Order $order, array $items): Order;

    /**
     * Busca pedidos com keyset pagination (diferencial).
     */
    public function findWithKeysetPagination(?string $cursor, int $limit = 15, array $filters = []): Collection;
}

