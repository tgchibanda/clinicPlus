<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id', 'history'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(PatientDetail::class, 'patient_details')->withTimeStamps();;
    }
}
