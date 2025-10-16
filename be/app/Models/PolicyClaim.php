<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyClaim extends Model
{
    protected $fillable = [
        'subscription_id', 'consultation_id',
        'claim_holder_first_name', 'claim_holder_last_name', 'claim_holder_dob', 'claim_holder_relationship',
        'amount', 'claim_category', 'status'
    ];

    public function subscription() {
        return $this->belongsTo(InsuranceSubscription::class, 'subscription_id');
    }

    public function consultation() {
        return $this->belongsTo(Consultation::class, 'consultation_id');
    }
}
