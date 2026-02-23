<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Domain\Payments\Enums\PaymentStatus;

class Payment extends Model
{
    protected $fillable = [
        'store_id',
        'order_id',
        'gateway',
        'gateway_reference',
        'status',
        'currency',
        'amount',
        'idempotency_key',
        'raw_response',
    ];

    protected $casts = [
        'amount' => 'integer',
        'raw_response' => 'array',
        'status' => PaymentStatus::class, //  enum real
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}