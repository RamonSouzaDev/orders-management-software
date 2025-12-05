<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\DTOs\CreateOrderDTO;
use App\DTOs\UpdateStatusDTO;
use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Http\Requests\UpdateStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Controller da API de Pedidos.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    /**
     * Lista todos os pedidos.
     *
     * GET /api/orders
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = [
            'status' => $request->query('status'),
            'customer_name' => $request->query('customer_name'),
        ];

        $perPage = (int) $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 100); // Limita entre 1 e 100

        $orders = $this->orderService->listOrders(
            array_filter($filters),
            $perPage
        );

        return OrderResource::collection($orders);
    }

    /**
     * Lista pedidos com keyset pagination (diferencial).
     *
     * GET /api/orders/cursor
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function indexWithCursor(Request $request): JsonResponse
    {
        $filters = [
            'status' => $request->query('status'),
            'customer_name' => $request->query('customer_name'),
        ];

        $cursor = $request->query('cursor');
        $limit = (int) $request->query('limit', 15);
        $limit = min(max($limit, 1), 100);

        $result = $this->orderService->listOrdersWithCursor(
            $cursor,
            $limit,
            array_filter($filters)
        );

        return response()->json([
            'data' => OrderResource::collection($result['data']),
            'next_cursor' => $result['next_cursor'],
            'has_more' => $result['has_more'],
        ]);
    }

    /**
     * Exibe um pedido específico.
     *
     * GET /api/orders/{id}
     *
     * @param string $id
     * @return OrderResource|JsonResponse
     */
    public function show(string $id): OrderResource|JsonResponse
    {
        try {
            $order = $this->orderService->getOrder($id);
            return new OrderResource($order);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Pedido não encontrado.',
                'error' => 'not_found',
            ], 404);
        }
    }

    /**
     * Cria um novo pedido.
     *
     * POST /api/orders
     *
     * @param CreateOrderRequest $request
     * @return JsonResponse
     */
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $dto = CreateOrderDTO::fromArray($request->validated());

            $order = $this->orderService->createOrder(
                $dto,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'message' => 'Pedido criado com sucesso.',
                'data' => new OrderResource($order),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'validation_error',
            ], 422);
        }
    }

    /**
     * Atualiza o status do pedido.
     *
     * PUT /api/orders/{id}/status
     *
     * @param UpdateStatusRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function updateStatus(UpdateStatusRequest $request, string $id): JsonResponse
    {
        try {
            $dto = UpdateStatusDTO::fromArray($request->validated());

            $order = $this->orderService->updateStatus(
                $id,
                $dto,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'message' => 'Status atualizado com sucesso.',
                'data' => new OrderResource($order),
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Pedido não encontrado.',
                'error' => 'not_found',
            ], 404);
        } catch (InvalidStatusTransitionException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'invalid_transition',
                'from_status' => $e->from->value,
                'to_status' => $e->to->value,
                'allowed_transitions' => array_map(
                    fn($s) => $s->value,
                    $e->from->allowedTransitions()
                ),
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'error' => 'validation_error',
            ], 422);
        }
    }

    /**
     * Exclui um pedido (soft delete).
     *
     * DELETE /api/orders/{id}
     *
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        try {
            $this->orderService->deleteOrder(
                $id,
                $request->ip(),
                $request->userAgent()
            );

            return response()->json([
                'message' => 'Pedido excluído com sucesso.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Pedido não encontrado.',
                'error' => 'not_found',
            ], 404);
        }
    }
}

