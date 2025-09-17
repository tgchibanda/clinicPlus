<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'institution_name', 'qualification_name', 'year_completed', 'upload'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'qualifications')->withTimeStamps();;
    }
}
