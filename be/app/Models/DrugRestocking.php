<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugRestocking extends Model
{
    use HasFactory;

    protected $fillable = [
        'drug_id',
        'user_id',
        'quantity_added',
        'previous_quantity',
        'new_quantity',
        'notes',
    ];

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}