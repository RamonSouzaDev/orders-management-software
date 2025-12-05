<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model do Pedido.
 *
 * @property string $id
 * @property string $customer_name
 * @property OrderStatus $status
 * @property float $subtotal
 * @property float|null $discount
 * @property float|null $tax
 * @property float $total
 * @property array|null $notes
 * @property \Carbon\Carbon|null $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Order extends Model
{
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    /**
     * A tabela associada ao model.
     */
    protected $table = 'orders';

    /**
     * A chave primária da tabela.
     */
    protected $primaryKey = 'id';

    /**
     * Tipo da chave primária.
     */
    protected $keyType = 'string';

    /**
     * Indica se a chave é auto-incremento.
     */
    public $incrementing = false;

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'customer_name',
        'status',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
            'notes' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Valores padrão para atributos.
     */
    protected $attributes = [
        'status' => 'draft',
        'subtotal' => 0,
        'discount' => 0,
        'tax' => 0,
        'total' => 0,
    ];

    /**
     * Relacionamento: um pedido tem muitos itens.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    /**
     * Relacionamento: logs de auditoria.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'order_id', 'id');
    }

    /**
     * Calcula o subtotal baseado nos itens.
     */
    public function calculateSubtotal(): float
    {
        return (float) $this->items->sum('total_price');
    }

    /**
     * Calcula o total do pedido.
     */
    public function calculateTotal(): float
    {
        $subtotal = $this->calculateSubtotal();
        $discount = (float) ($this->discount ?? 0);
        $tax = (float) ($this->tax ?? 0);

        return max(0, $subtotal - $discount + $tax);
    }

    /**
     * Recalcula e atualiza os totais do pedido.
     */
    public function recalculateTotals(): self
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->total = $this->calculateTotal();

        return $this;
    }

    /**
     * Verifica se pode transicionar para um novo status.
     */
    public function canTransitionTo(OrderStatus $newStatus): bool
    {
        return $this->status->canTransitionTo($newStatus);
    }

    /**
     * Scope para filtrar por status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por nome do cliente.
     */
    public function scopeByCustomerName($query, string $name)
    {
        return $query->where('customer_name', 'like', "%{$name}%");
    }

    /**
     * Scope para ordenar por data de criação (keyset pagination).
     */
    public function scopeOrderByCreatedDesc($query)
    {
        return $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }
}

