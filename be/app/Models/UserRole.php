<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'role', 'status'
    ];

    /**
     * Get the user that owns a user_roles.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'user_roles')->withTimeStamps();;
    }
}
