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
        $perPage = $request->get('perPage', 6); // تغيير من 10 إلى 6 للكاردات
        $search = $request->get('search', '') ?? '';

        // تحميل الأدوار مع العلاقات المطلوبة للكاردات
        $roles = $this->roleService->paginateWithPermissions($perPage, (string)$search);

        // تحميل المستخدمين والصلاحيات لكل دور
        // $roles->load(['users:id,name,avatar', 'permissions:id,name']);
        $roles->load(['users:id,name', 'permissions:id,name']);

        // إضافة عدد المستخدمين لكل دور
        $roles->getCollection()->transform(function ($role) {
            $role->users_count = $role->users->count();
            return $role;
        });

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
        $modules = Module::with([
            'children.permissions:id,name,module_id',
            'permissions:id,name,module_id'
        ])
            ->whereNull('parent_id')
            ->get()
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'label' => $parent->label,
                    'permissions' => $parent->permissions->map(fn($perm) => [
                        'id' => $perm->id,
                        'name' => $perm->name,
                    ]),
                    'children' => $parent->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'label' => $child->label,
                            'permissions' => $child->permissions->map(fn($perm) => [
                                'id' => $perm->id,
                                'name' => $perm->name,
                            ]),
                        ];
                    }),
                ];
            })->values();

        return response()->json([
            'status' => true,
            'role' => [
                'id' => null,
                'name' => '',
                'sidebar_modules' => [],
                'permissions' => [],
            ],
            'modules' => $modules,
        ]);
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
        if (!$role) {
            return response()->json(['status' => false, 'message' => __('Role not found')], 404);
        }

        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $sidebarModules = $role->modules->pluck('id')->toArray();

        // جلب الموديولات الرئيسية مع الأطفال والصلاحيات
        $modules = \App\Models\Module::with([
            'children.permissions:id,name,module_id',
            'permissions:id,name,module_id'
        ])
            ->whereNull('parent_id')
            ->get()
            ->map(function ($parent) {
                return [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'label' => $parent->label,
                    'permissions' => $parent->permissions->map(fn($perm) => [
                        'id' => $perm->id,
                        'name' => $perm->name,
                    ]),
                    'children' => $parent->children->map(function ($child) {
                        return [
                            'id' => $child->id,
                            'name' => $child->name,
                            'label' => $child->label,
                            'permissions' => $child->permissions->map(fn($perm) => [
                                'id' => $perm->id,
                                'name' => $perm->name,
                            ]),
                        ];
                    }),
                ];
            })->values();

        return response()->json([
            'status' => true,
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'sidebar_modules' => $sidebarModules,
                'permissions' => $rolePermissions,
            ],
            'modules' => $modules,
        ]);
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
        $perPage = $request->get('perPage', 6); 
        $search = $request->get('search', '') ?? '';

        $roles = $this->roleService->paginateWithPermissions($perPage, (string)$search);

        // تحميل العلاقات المطلوبة للكاردات
        // $roles->load(['users:id,name,avatar', 'permissions:id,name']);
        $roles->load(['users:id,name', 'permissions:id,name']);

        // إضافة عدد المستخدمين
        $roles->getCollection()->transform(function ($role) {
            $role->users_count = $role->users->count();
            return $role;
        });

        // استخدام partial للكاردات بدلاً من الجدول
        $html = view('roles.partials.cards', compact('roles'))->render();
        $pagination = $roles->appends(request()->query())->links()->render();

        return response()->json([
            'status' => true,
            'html' => $html,
            'pagination' => $pagination
        ]);
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


    public function duplicate($id)
    {
        try {
            $originalRole = $this->roleService->findRole((int) $id);
            if (!$originalRole) {
                return $this->respondNotFound();
            }

            // إنشاء نسخة من الدور
            $data = [
                'name' => $originalRole->name . ' - ' . __('Copy'),
                'permissions' => $originalRole->permissions->pluck('name')->toArray(),
                'sidebar_modules' => $originalRole->modules->pluck('id')->toArray()
            ];

            $newRole = $this->roleService->createRole($data);

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => true,
                    'message' => __('Role duplicated successfully'),
                    'role' => $newRole->load('permissions', 'users')
                ]);
            }

            return redirect()->route('roles.index')
                ->with('success', __('Role duplicated successfully'))
                ->with('swal_title', __('Success'));
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'status' => false,
                    'message' => __('Error duplicating role: ') . $e->getMessage()
                ], 500);
            }

            return redirect()->route('roles.index')
                ->with('error', __('Error duplicating role'))
                ->with('swal_title', __('Error'));
        }
    }


    public function getUsersWithRoles(Request $request)
    {
        try {
            $perPage = $request->get('perPage', 10);
            $search = $request->get('search', '');

            // جلب المستخدمين مع أدوارهم
            $users = DB::table('users')
                ->leftJoin('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->select([
                    'users.id',
                    'users.name',
                    'users.email',
                    'users.avatar',
                    'users.status',
                    'users.created_at',
                    'roles.name as role_name',
                    'roles.id as role_id'
                ])
                ->when($search, function ($query) use ($search) {
                    return $query->where(function ($q) use ($search) {
                        $q->where('users.name', 'like', "%{$search}%")
                            ->orWhere('users.email', 'like', "%{$search}%")
                            ->orWhere('roles.name', 'like', "%{$search}%");
                    });
                })
                ->orderBy('users.created_at', 'desc')
                ->paginate($perPage);

            if ($request->ajax()) {
                $html = view('roles.partials.users-table', compact('users'))->render();
                return response()->json([
                    'status' => true,
                    'html' => $html
                ]);
            }

            return response()->json([
                'status' => true,
                'data' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Error fetching users: ') . $e->getMessage()
            ], 500);
        }
    }


    public function getPermissionMatrix()
    {
        try {
            $modules = Module::with(['children', 'permissions'])->whereNull('parent_id')->get();
            $allRoles = $this->roleService->getAllRoles();

            $matrix = [];
            foreach ($modules as $module) {
                $moduleData = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'children' => [],
                    'permissions' => []
                ];

                // إضافة الوحدات الفرعية
                foreach ($module->children as $child) {
                    $childData = [
                        'id' => $child->id,
                        'name' => $child->name,
                        'permissions' => []
                    ];

                    // إضافة صلاحيات الوحدة الفرعية
                    foreach (['view', 'create', 'edit', 'delete'] as $action) {
                        $permissionName = strtolower($action . ' ' . str_replace(['_', '-'], ' ', $child->name));
                        $childData['permissions'][$action] = [
                            'name' => $permissionName,
                            'roles' => $allRoles->filter(function ($role) use ($permissionName) {
                                return $role->permissions->contains('name', $permissionName);
                            })->pluck('name')->toArray()
                        ];
                    }

                    $moduleData['children'][] = $childData;
                }

                $matrix[] = $moduleData;
            }

            return response()->json([
                'status' => true,
                'matrix' => $matrix,
                'roles' => $allRoles
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => __('Error fetching permission matrix: ') . $e->getMessage()
            ], 500);
        }
    }
}
