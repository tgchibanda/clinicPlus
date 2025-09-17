<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'upload_name', 'description', 'upload'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'uploads')->withTimeStamps();;
    }
}

