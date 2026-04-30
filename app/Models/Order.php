<?php

namespace App\Models;

use App\Domain\Orders\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'public_token',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'shipping_city',
        'shipping_region',
        'shipping_notes',
        'currency',
        'subtotal',
        'total',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'total'    => 'integer',
        'status'   => OrderStatus::class,
        'paid_at'  => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Una orden puede tener muchos pagos (reintentos, webhooks repetidos, etc.)
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function shipment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}