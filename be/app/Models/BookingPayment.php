<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPayment extends Model
{
    use HasFactory;
    
    protected $table = 'booking_payments';

    protected $fillable = [
        'consultation_id',
        'subscription_id',
        'amount',
        'payment_method',
        'transaction_ref',
        'description',
        'status',
        'raw_payload',
    ];

    protected $casts = [
        'amount' => 'float',
        'raw_payload' => 'array',
    ];

    public function consultation()
    {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }

    public function subscription()
    {
        return $this->belongsTo(\App\Models\InsuranceSubscription::class, 'subscription_id');
    }
}
