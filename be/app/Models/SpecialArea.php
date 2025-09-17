<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialArea extends Model
{
    use HasFactory;
    protected $table = 'special_areas';
    protected $fillable = [
        'user_id', 'area_name', 'description'
    ];

    /**
     * Get the user that owns a special_areas.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'special_areas')->withTimeStamps();;
    }
}
