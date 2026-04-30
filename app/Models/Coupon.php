<?php

namespace App\Models;

use App\Domain\Coupons\Enums\CouponType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    protected $fillable = [
        'store_id',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_uses',
        'uses_count',
        'expires_at',
        'active',
    ];

    protected $casts = [
        'type'             => CouponType::class,
        'value'            => 'integer',
        'min_order_amount' => 'integer',
        'max_uses'         => 'integer',
        'uses_count'       => 'integer',
        'expires_at'       => 'datetime',
        'active'           => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Lógica de negocio
    |--------------------------------------------------------------------------
    */

    public function isValid(int $subtotal): bool
    {
        if (! $this->active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses !== null && $this->uses_count >= $this->max_uses) return false;
        if ($this->min_order_amount !== null && $subtotal < $this->min_order_amount) return false;

        return true;
    }

    public function calculateDiscount(int $subtotal): int
    {
        if ($this->type === CouponType::Percent) {
            return (int) round($subtotal * $this->value / 100);
        }

        // Fixed: no puede descontar más que el subtotal
        return min($this->value, $subtotal);
    }
}
