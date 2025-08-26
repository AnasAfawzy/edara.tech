<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name',
        'label',
        'route',
        'icon',
        'parent_id',
        'show_in_sidebar'
    ];

    public function children()
    {
        return $this->hasMany(Module::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(Module::class, 'parent_id');
    }

    public function roles()
    {
        return $this->belongsToMany(\App\Models\Role::class, 'module_role');
    }
}
