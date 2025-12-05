<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model de Log de Auditoria (Diferencial).
 *
 * @property int $id
 * @property string $order_id
 * @property string $action
 * @property string|null $old_value
 * @property string|null $new_value
 * @property array|null $changes
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 */
class AuditLog extends Model
{
    /**
     * Indica que o model não usa timestamps padrão.
     */
    public $timestamps = false;

    /**
     * A tabela associada ao model.
     */
    protected $table = 'audit_logs';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'order_id',
        'action',
        'old_value',
        'new_value',
        'changes',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'changes' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Relacionamento: log pertence a um pedido.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Extrai o valor do status (string ou Enum).
     */
    private static function getStatusValue(mixed $status): string
    {
        if (is_string($status)) {
            return $status;
        }
        
        if (is_object($status) && property_exists($status, 'value')) {
            return $status->value;
        }
        
        return (string) $status;
    }

    /**
     * Cria um registro de log de criação.
     */
    public static function logCreation(Order $order, ?string $ip = null, ?string $userAgent = null): self
    {
        return self::create([
            'order_id' => $order->id,
            'action' => 'created',
            'new_value' => self::getStatusValue($order->status),
            'changes' => $order->toArray(),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    /**
     * Cria um registro de log de alteração de status.
     */
    public static function logStatusChange(
        Order $order,
        string $oldStatus,
        string $newStatus,
        ?string $ip = null,
        ?string $userAgent = null
    ): self {
        return self::create([
            'order_id' => $order->id,
            'action' => 'status_changed',
            'old_value' => $oldStatus,
            'new_value' => $newStatus,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }

    /**
     * Cria um registro de log de exclusão.
     */
    public static function logDeletion(Order $order, ?string $ip = null, ?string $userAgent = null): self
    {
        return self::create([
            'order_id' => $order->id,
            'action' => 'deleted',
            'old_value' => self::getStatusValue($order->status),
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'created_at' => now(),
        ]);
    }
}

