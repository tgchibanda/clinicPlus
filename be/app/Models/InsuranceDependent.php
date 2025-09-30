<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InsuranceDependent extends Model
{
    protected $fillable = [
        'subscription_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'plan_id',
        'relationship',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InsuranceSubscription::class, 'subscription_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'plan_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): int
    {
        return Carbon::parse($this->date_of_birth)->age;
    }

    public function isAdult(): bool
    {
        return $this->age >= 18;
    }
}
