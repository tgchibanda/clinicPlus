<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'unit_number', 'street_name', 'suburb', 'city', 'gps', 'mobile_no'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'contacts')->withTimeStamps();;
    }
}
