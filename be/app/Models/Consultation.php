<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id', 'reason', 'instruction', 'doctor_notes', 'examination', 'diagnosis', 'investigation', 'management', 'status', 'pathology', 'radiology'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(PatientDetail::class, 'patient_details')->withTimeStamps();;
    }
}