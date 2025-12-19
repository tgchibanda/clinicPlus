<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InsuranceSubscription extends Model
{
    protected $fillable = [
        'patient_id',
        'plan_id',
        'status',
        'started_at',
        'coverage_starts_at',
        'first_payment_at',
        'last_payment_at',
        'total_paid_amount',
        'due_count',
        'next_due_date',
        'notes',
        'policy_number',
        'last_notification',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'coverage_starts_at' => 'datetime',
        'first_payment_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'next_due_date' => 'date',
        'total_paid_amount' => 'decimal:2',
        'due_count' => 'integer',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'plan_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(InsuranceDependent::class, 'subscription_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InsurancePayment::class, 'subscription_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(InsuranceEvent::class, 'subscription_id');
    }

    public function calculateMonthlyTotal(): float
    {
        $total = 0;

        // Owner's plan
        if ($this->plan) {
            $ownerAge = Carbon::parse($this->patient->date_of_birth)->age;
            $total += $this->plan->getPriceForAge($ownerAge);
        }

        // Dependents' plans
        foreach ($this->dependents as $dependent) {
            $dependentAge = Carbon::parse($dependent->date_of_birth)->age;
            $total += $dependent->plan->getPriceForAge($dependentAge);
        }

        return $total;
    }

    public function isCoverageActive(): bool
    {
        return $this->status === 'active' 
            && $this->coverage_starts_at 
            && now()->greaterThanOrEqualTo($this->coverage_starts_at);
    }

    public function getCompletedPaymentsCount(): int
    {
        return $this->payments()->where('status', 'completed')->count();
    }

    public function logEvent(string $type, array $payload = []): void
    {
        $this->events()->create([
            'type' => $type,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }

    public function claims() {
    return $this->hasMany(PolicyClaim::class, 'subscription_id');
    }

}