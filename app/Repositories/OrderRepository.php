<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Implementação do repositório de pedidos.
 */
class OrderRepository implements OrderRepositoryInterface
{
    /**
     * Tempo de cache em segundos (5 minutos).
     */
    private const CACHE_TTL = 300;

    /**
     * {@inheritdoc}
     */
    public function findAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::query()->with('items');

        // Aplica filtros
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['customer_name'])) {
            $query->byCustomerName($filters['customer_name']);
        }

        return $query->orderByCreatedDesc()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function findById(string $id): ?Order
    {
        $cacheKey = "order:{$id}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Order::with('items')->find($id);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(string $id): Order
    {
        $order = $this->findById($id);

        if ($order === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException(
                "Pedido com ID {$id} não encontrado."
            );
        }

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Order
    {
        $order = Order::create($data);
        $this->clearCache($order->id);

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        $this->clearCache($order->id);

        return $order->fresh(['items']);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(Order $order): bool
    {
        $this->clearCache($order->id);
        return $order->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function addItems(Order $order, array $items): Order
    {
        foreach ($items as $item) {
            $order->items()->create($item);
        }

        $this->clearCache($order->id);

        return $order->fresh(['items']);
    }

    /**
     * {@inheritdoc}
     */
    public function findWithKeysetPagination(?string $cursor, int $limit = 15, array $filters = []): Collection
    {
        $query = Order::query()->with('items');

        // Aplica filtros
        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (!empty($filters['customer_name'])) {
            $query->byCustomerName($filters['customer_name']);
        }

        // Keyset pagination usando created_at e id
        if ($cursor) {
            $decoded = json_decode(base64_decode($cursor), true);
            if ($decoded && isset($decoded['created_at'], $decoded['id'])) {
                $query->where(function ($q) use ($decoded) {
                    $q->where('created_at', '<', $decoded['created_at'])
                        ->orWhere(function ($q2) use ($decoded) {
                            $q2->where('created_at', '=', $decoded['created_at'])
                                ->where('id', '<', $decoded['id']);
                        });
                });
            }
        }

        return $query->orderByCreatedDesc()->limit($limit + 1)->get();
    }

    /**
     * Limpa o cache do pedido.
     */
    private function clearCache(string $orderId): void
    {
        Cache::forget("order:{$orderId}");
    }
}

