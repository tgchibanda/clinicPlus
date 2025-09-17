<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZimService extends Model
{
    use HasFactory;
    protected $fillable = [
        'name', 'address', 'mobile_no', 'landline', 'additional_contacts', 'facebook', 'twitter', 'instagram', 'type'
    ];
}
