<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'consultation_id', 'history'
    ];

    public function consultation()
    {
        return $this->belongsTo(\App\Models\Consultation::class, 'consultation_id');
    }
}
