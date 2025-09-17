<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'patient_id', 'consultation_id', 'amount', 'order_number', 'status'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'payments')->withTimeStamps();;
    }
}

