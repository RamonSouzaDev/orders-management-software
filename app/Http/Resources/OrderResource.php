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
        return [
            'id' => $this->id,
            'customer_name' => $this->customer_name,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'status_color' => $this->status->color(),
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) ($this->discount ?? 0),
            'tax' => (float) ($this->tax ?? 0),
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->when(
                !$this->relationLoaded('items'),
                $this->items()->count()
            ),
            'allowed_transitions' => array_map(
                fn(OrderStatus $s) => $s->value,
                $this->status->allowedTransitions()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

