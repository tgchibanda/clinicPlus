<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Consultation extends Model
{
    use HasFactory;
    protected $fillable = [
        'patient_id', 'consultation_fee','payment_method', 'doctor_id','user_id', 'reason', 'instruction', 'location_id', 'start_at', 'end_at', 'doctor_notes', 'examination', 'diagnosis', 'investigation', 'management', 'status', 'pathology', 'radiology'
    ];

    protected $dates = ['start_at', 'end_at'];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(Patient::class, 'patients')->withTimeStamps();;
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    public function patient()  
    { 
        return $this->belongsTo(Patient::class, 'patient_id'); 
    }

    public function creator()  
    { 
        return $this->belongsTo(User::class, 'user_id'); 
    }

    public function medicalHistory()
    {
        return $this->hasOne(\App\Models\MedicalHistory::class, 'consultation_id');
    }

    public function prescription()
    {
         return $this->hasOne(Prescription::class, 'consultation_id');
    }

}