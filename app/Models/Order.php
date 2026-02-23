<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Orders\Enums\OrderStatus;

class Order extends Model
{
    protected $fillable = [
        'store_id',
        'public_token',
        'status',
        'customer_name',
        'customer_email',
        'customer_phone',
        'currency',
        'subtotal',
        'total',
    ];

    protected $casts = [
        'subtotal' => 'integer',
        'total' => 'integer',
        'status' => OrderStatus::class, // 🔥 ahora es enum real
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}