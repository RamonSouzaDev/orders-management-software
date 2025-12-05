<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource para Pedido.
 */
class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Garantir que status seja um Enum
        $status = $this->status instanceof OrderStatus 
            ? $this->status 
            : OrderStatus::from($this->status);

        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'status' => $status->value,
            'status_label' => $status->label(),
            'status_color' => $status->color(),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) ($this->discount ?? 0),
            'tax' => (float) ($this->tax ?? 0),
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when(
                !$this->relationLoaded('items'),
                fn() => $this->items()->count()
            ),
            'allowed_transitions' => array_map(
                fn(OrderStatus $s) => $s->value,
                $status->allowedTransitions()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
