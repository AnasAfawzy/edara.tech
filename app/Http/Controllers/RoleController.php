<?php

namespace App\Http\Controllers;

use App\Models\Module;
use Illuminate\Http\Request;
use App\Services\RoleService;
use Illuminate\Support\Facades\DB;

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

    public function show($id)
    {
        $role = $this->roleService->findRole((int) $id);
        if (! $role) {
            return $this->respondNotFound();
        }

        $role->load('permissions', 'modules');

        // Provide view with modules, sidebarModules and rolePermissions used by the blade
        $modules = Module::all();
        $sidebarModules = $role->modules->pluck('id')->toArray();
        $rolePermissions = $role->permissions->pluck('name')->toArray();

        return view('roles.show', compact('role', 'modules', 'sidebarModules', 'rolePermissions'));
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
                'message' => __('The role has been added successfully'),
                'role' => $role->load('permissions')
            ]);
        }
        return redirect()->route('roles.index')
            ->with('success', __('The role has been added successfully'))
            ->with('swal_title', __('Success'));
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
                'message' => __('The role has been updated successfully'),
                'role' => $role->load('permissions')
            ]);
        }
        return redirect()->route('roles.index')->with('success', __('The role has been updated successfully'))->with('swal_title', __('Success'));
    }

    public function destroy($id)
    {
        $role = $this->roleService->findRole((int) $id);
        if (! $role) {
            return $this->respondNotFound();
        }

        if ($this->roleService->roleHasUsers((int) $id)) {
            return $this->respondCannotDelete();
        }

        $this->roleService->deleteRole((int) $id);

        return $this->respondSuccess(__('The role has been deleted successfully'));
    }

    protected function respondNotFound()
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['status' => false, 'message' => __('Role not found')], 404);
        }
        return redirect()->route('roles.index')->with('error', __('Role not found'))->with('swal_title', __('Error'));
    }

    protected function respondCannotDelete()
    {
        $msg = __('This role cannot be deleted because it is assigned to users');
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['status' => false, 'message' => $msg], 400);
        }
        return redirect()->route('roles.index')->with('error', $msg)->with('swal_title', __('Error'));
    }

    protected function respondSuccess(string $message)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['status' => true, 'message' => $message]);
        }
        return redirect()->route('roles.index')->with('success', $message)->with('swal_title', __('Success'));
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
