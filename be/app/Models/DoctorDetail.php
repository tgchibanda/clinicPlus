<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'reg_number'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'doctor_details')->withTimeStamps();;
    }
}
