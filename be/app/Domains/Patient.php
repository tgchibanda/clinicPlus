<?php

namespace App\Domains;

use App\Models\PatientDetail;

class Patient
{
    public function getPatient($id)
    {
        $patientDetail = PatientDetail::select(
            'patient_details.*'
        )
            ->where('patient_details.id', '=', $id)
            ->get();
        return $patientDetail;
    }
}
