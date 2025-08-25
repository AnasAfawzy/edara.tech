<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\PseudoTypes\True_;

class CostCenter extends Model
{
    protected $fillable = [
        'name',
        'position',
        'ownerEl',
        'slave',
        'code',
        'level',
        'creditor',
        'debtor',
        'has_sub',
        'is_sub'
    ];

    protected $casts = [
        'has_sub' => 'boolean',
        'is_sub' => 'boolean',
        'slave' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(CostCenter::class, 'ownerEl');
    }

    public function children()
    {
        return $this->hasMany(CostCenter::class, 'ownerEl');
    }

    public function scopeMain_Accounts($query)
    {
        return $query->where('has_sub', true)->orWhere('slave', false);
    }
}
