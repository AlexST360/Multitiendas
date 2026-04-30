<?php

namespace App\Models;

use App\Domain\Shipments\Enums\ShipmentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'store_id',
        'order_id',
        'status',
        'carrier',
        'tracking_number',
        'tracking_url',
        'notes',
        'shipped_at',
        'delivered_at',
    ];

    protected $casts = [
        'status'       => ShipmentStatus::class,
        'shipped_at'   => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
