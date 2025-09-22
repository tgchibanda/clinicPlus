<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name', 'last_name', 'phone', 'email', 'date_of_birth',
        'gender', 'address', 'emergency_contact',
        'voucher_code', 'assigned_doctor_id', 'status', 'user_id'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'visit_date' => 'datetime'
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'assigned_doctor_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}