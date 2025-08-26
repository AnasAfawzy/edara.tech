<?php

namespace App\Models;

use App\Models\Module;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function modules()
    {
        return $this->belongsToMany(Module::class, 'module_role');
    }
}
