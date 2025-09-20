<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'category', 'selling_price',
        'stock_quantity', 'minimum_stock_level', 'unit', 'expiry_date'
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'expiry_date' => 'date'
    ];

    public function prescriptionItems()
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function isLowStock()
    {
        return $this->stock_quantity <= $this->minimum_stock_level;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }
}