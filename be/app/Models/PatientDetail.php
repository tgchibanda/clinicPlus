<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'fullname', 'email', 'address_line1', 'address_line2', 'address_line3', 'city', 'gps', 'mobile_no', 'mobile_no_contact_person', 'gender', 'dob'
    ];

    /**
     * Get the user that owns a contact.
     */
    public function user()
    {
        return $this->belongsToMany(User::class, 'patientdetails')->withTimeStamps();;
    }
}
