<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class InsurancePlan extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price_adult',
        'price_child',
        'active',
    ];

    protected $casts = [
        'price_adult' => 'decimal:2',
        'price_child' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(InsuranceSubscription::class, 'plan_id');
    }

    public function dependents(): HasMany
    {
        return $this->hasMany(InsuranceDependent::class, 'plan_id');
    }

    public function getPriceForAge(int $age): float
    {
        return $age >= 18 ? $this->price_adult : $this->price_child;
    }
}