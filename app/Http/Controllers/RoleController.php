<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $query = Role::with('permissions');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->orderBy('id', 'desc')->paginate($perPage);

        return view('roles.index', compact('roles', 'perPage', 'search'));
    }

    public function create()
    {
        $modules = Module::all();
        // للإضافة: لا يوجد صلاحيات أو موديولات مختارة
        $sidebarModules = [];
        $rolePermissions = [];
        return view('roles.create', compact('modules', 'sidebarModules', 'rolePermissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|unique:roles',
            'permissions' => 'array',
            'sidebar_modules' => 'array'
        ]);

        $role = Role::create(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        $role->modules()->sync($data['sidebar_modules'] ?? []);

        return redirect()->route('roles.index')->with('success', 'تم إضافة الدور');
    }

    public function edit(Role $role)
    {
        $modules = Module::all();
        $sidebarModules = $role->modules->pluck('id')->toArray();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'modules', 'sidebarModules', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => 'required|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'sidebar_modules' => 'array'
        ]);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions'] ?? []);
        $role->modules()->sync($data['sidebar_modules'] ?? []);
        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور');
    }

    public function destroy(Role $role)
    {
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'تم حذف الدور');
    }

    public function editPermissions(Role $role)
    {
        $modules = Module::all(['name', 'label'])->toArray();
        return view('roles.permissions_matrix', compact('role', 'modules'));
    }

    public function updatePermissions(Request $request, Role $role)
    {
        $role->syncPermissions($request->permissions ?? []);
        return back()->with('success', 'تم تحديث الصلاحيات');
    }

    public function search(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $query = Role::with('permissions');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $roles = $query->orderBy('id', 'desc')->paginate($perPage);

        $view = view('roles.partials.table', compact('roles'))->render();

        return response()->json(['html' => $view]);
    }
}
