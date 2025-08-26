<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Models\Module;

class RoleController extends Controller
{
    protected $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '') ?? '';
        $roles = $this->roleService->paginateWithPermissions($perPage, (string)$search);
        return view('roles.index', compact('roles', 'perPage', 'search'));
    }

    public function create()
    {
        $modules = Module::all();
        $sidebarModules = [];
        $rolePermissions = [];
        return view('roles.create', compact('modules', 'sidebarModules', 'rolePermissions'));
    }

    public function store(Request $request)
    {
        $data = $this->validateRole($request);

        $role = $this->roleService->createRole($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'تم إضافة الدور بنجاح',
                'role' => $role->load('permissions')
            ]);
        }
        return redirect()->route('roles.index')
            ->with('success', 'تم إضافة الدور بنجاح')
            ->with('swal_title', 'إضافة دور جديد');
    }

    public function edit($id)
    {
        $role = $this->roleService->findRole($id);
        $modules = Module::all();
        $sidebarModules = $role->modules->pluck('id')->toArray();
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        return view('roles.edit', compact('role', 'modules', 'sidebarModules', 'rolePermissions'));
    }

    public function update(Request $request, $id)
    {
        $data = $this->validateRole($request, $id);

        $role = $this->roleService->updateRole($id, $data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'تم تحديث الدور بنجاح',
                'role' => $role->load('permissions')
            ]);
        }
        return redirect()->route('roles.index')->with('success', 'تم تحديث الدور بنجاح')->with('swal_title', 'تم تحديث الدور بنجاح');
    }

    public function destroy($id)
    {
        $this->roleService->deleteRole($id);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'status' => true,
                'message' => 'تم حذف الدور بنجاح'
            ]);
        }
        return redirect()->route('roles.index')
            ->with('success', 'تم حذف الدور بنجاح')
            ->with('swal_title', 'تم حذف الدور بنجاح');
    }

    public function search(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '') ?? '';
        $roles = $this->roleService->paginateWithPermissions($perPage, (string)$search);
        $view = view('roles.partials.table', compact('roles'))->render();
        return response()->json(['html' => $view]);
    }

    protected function validateRole(Request $request, $id = null)
    {
        $rules = [
            'name' => 'required|unique:roles,name' . ($id ? ',' . $id : ''),
            'permissions' => 'array',
            'sidebar_modules' => 'array'
        ];
        return $request->validate($rules);
    }
}
