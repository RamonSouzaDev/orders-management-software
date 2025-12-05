<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\CreateOrderDTO;
use App\DTOs\UpdateStatusDTO;
use App\Enums\OrderStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Exceptions\OrderValidationException;
use App\Models\AuditLog;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service para regras de negócio de Pedidos.
 */
class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $repository,
    ) {
    }

    /**
     * Lista todos os pedidos com filtros.
     */
    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->findAll($filters, $perPage);
    }

    /**
     * Lista pedidos com keyset pagination (diferencial).
     */
    public function listOrdersWithCursor(?string $cursor, int $limit = 15, array $filters = []): array
    {
        $orders = $this->repository->findWithKeysetPagination($cursor, $limit, $filters);

        $hasMore = $orders->count() > $limit;

        if ($hasMore) {
            $orders = $orders->take($limit);
        }

        $nextCursor = null;
        if ($hasMore && $orders->isNotEmpty()) {
            $lastOrder = $orders->last();
            $nextCursor = base64_encode(json_encode([
                'created_at' => $lastOrder->created_at->toIso8601String(),
                'id' => $lastOrder->id,
            ]));
        }

        return [
            'data' => $orders,
            'next_cursor' => $nextCursor,
            'has_more' => $hasMore,
        ];
    }

    /**
     * Busca um pedido por ID.
     */
    public function getOrder(string $id): Order
    {
        return $this->repository->findOrFail($id);
    }

    /**
     * Cria um novo pedido.
     *
     * @throws OrderValidationException
     */
    public function createOrder(CreateOrderDTO $dto, ?string $ip = null, ?string $userAgent = null): Order
    {
        // Valida DTO
        $dto->validate();

        return DB::transaction(function () use ($dto, $ip, $userAgent) {
            // Prepara dados do pedido
            $orderData = $dto->toArray();
            $orderData['subtotal'] = $dto->calculateSubtotal();
            $orderData['total'] = $dto->calculateTotal();

            // Cria o pedido
            $order = $this->repository->create($orderData);

            // Adiciona os itens
            $itemsData = [];
            foreach ($dto->items as $item) {
                $itemsData[] = $item->toArray();
            }

            $order = $this->repository->addItems($order, $itemsData);

            // Registra auditoria
            AuditLog::logCreation($order, $ip, $userAgent);

            return $order;
        });
    }

    /**
     * Atualiza o status do pedido.
     *
     * @throws InvalidStatusTransitionException
     */
    public function updateStatus(
        string $orderId,
        UpdateStatusDTO $dto,
        ?string $ip = null,
        ?string $userAgent = null
    ): Order {
        return DB::transaction(function () use ($orderId, $dto, $ip, $userAgent) {
            $order = $this->repository->findOrFail($orderId);
            $oldStatus = $order->status;
            $newStatus = $dto->status;

            // Valida transição de status
            if (!$order->canTransitionTo($newStatus)) {
                throw new InvalidStatusTransitionException(
                    $oldStatus,
                    $newStatus
                );
            }

            // Atualiza o status
            $order = $this->repository->update($order, ['status' => $newStatus->value]);

            // Registra auditoria
            AuditLog::logStatusChange($order, $oldStatus->value, $newStatus->value, $ip, $userAgent);

            return $order;
        });
    }

    /**
     * Exclui um pedido (soft delete).
     */
    public function deleteOrder(string $orderId, ?string $ip = null, ?string $userAgent = null): bool
    {
        return DB::transaction(function () use ($orderId, $ip, $userAgent) {
            $order = $this->repository->findOrFail($orderId);

            // Registra auditoria antes de deletar
            AuditLog::logDeletion($order, $ip, $userAgent);

            return $this->repository->delete($order);
        });
    }

    /**
     * Calcula o total de um pedido baseado nos parâmetros.
     * Método útil para testes e validação.
     */
    public function calculateOrderTotal(float $subtotal, ?float $discount, ?float $tax): float
    {
        $discount = $discount ?? 0;
        $tax = $tax ?? 0;

        return max(0, round($subtotal - $discount + $tax, 2));
    }

    /**
     * Valida se uma transição de status é permitida.
     */
    public function isValidStatusTransition(OrderStatus $from, OrderStatus $to): bool
    {
        return $from->canTransitionTo($to);
    }
}

