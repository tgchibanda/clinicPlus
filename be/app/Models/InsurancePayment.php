<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InsurancePayment extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'payment_method',
        'transaction_ref',
        'paid_at',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'raw_payload' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InsuranceSubscription::class, 'subscription_id');
    }
}