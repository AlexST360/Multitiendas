<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePaymentConfig extends Model
{
    protected $fillable = [
        'store_id',
        'gateway',
        'environment',
        'credentials',
        'active',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'active'      => 'boolean',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
