<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model do Item do Pedido.
 *
 * @property int $id
 * @property string $order_id
 * @property string $product_name
 * @property int $quantity
 * @property float $unit_price
 * @property float $total_price
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrderItem extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao model.
     */
    protected $table = 'order_items';

    /**
     * Atributos que podem ser preenchidos em massa.
     */
    protected $fillable = [
        'order_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
    ];

    /**
     * Casts de atributos.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Relacionamento: item pertence a um pedido.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Calcula o preço total do item.
     */
    public function calculateTotalPrice(): float
    {
        return (float) ($this->quantity * $this->unit_price);
    }

    /**
     * Boot do model para calcular total_price automaticamente.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (OrderItem $item) {
            $item->total_price = $item->calculateTotalPrice();
        });
    }
}

