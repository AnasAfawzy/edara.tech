<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'company_name',
        'company_email',
        'company_phone',
        'company_address',
        'company_logo',
        'tax_number',
        'default_tax_rate',
        'currency',
        'timezone',
        'language',
        'payment_methods',
    ];

    protected $casts = [
        'payment_methods' => 'array',
    ];
}
