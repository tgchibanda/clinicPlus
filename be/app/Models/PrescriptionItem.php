<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_id', 'drug_id', 'quantity_prescribed',
        'quantity_dispensed', 'dosage_instructions', 'unit_price'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2'
    ];

    public function prescription()
    {
        return $this->belongsTo(Prescription::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }

    public function getRemainingQuantityAttribute()
    {
        return $this->quantity_prescribed - $this->quantity_dispensed;
    }

    public function isFullyDispensed()
    {
        return $this->quantity_dispensed >= $this->quantity_prescribed;
    }
}