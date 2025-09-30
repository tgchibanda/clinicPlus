<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceEvent extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'subscription_id',
        'type',
        'payload',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InsuranceSubscription::class, 'subscription_id');
    }
}