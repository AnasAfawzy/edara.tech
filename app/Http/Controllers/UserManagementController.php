<?php

namespace App\Http\Controllers;

use App\Repositories\RoleRepository;
use App\Services\UserService;
use App\Models\Role;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    protected $userService;
    protected $roleRepository;

    public function __construct(UserService $userService, RoleRepository $roleRepository)
    {
        $this->userService = $userService;
        $this->roleRepository = $roleRepository;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $users = $this->userService->paginateWithRoles($perPage, $search);

        // if ($request->ajax()) {
        //     $html = view('users.partials.table', compact('users'))->render();
        //     $pagination = $users->appends($request->all())->links()->render();
        //     return response()->json([
        //         'html' => $html,
        //         'pagination' => $pagination
        //     ]);
        // }
        $roles = Role::all();
        return view('users.index', compact('users', 'perPage', 'search', 'roles'));
    }

    public function create()
    {
        $roles = $this->roleRepository->all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'roles' => 'required|string'
        ]);
        $user = $this->userService->createUser([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
        $user->syncRoles([$data['roles']]);
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('تم إضافة المستخدم بنجاح'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => implode(', ', $user->getRoleNames()->toArray()),
                ]
            ]);
        }
        return redirect()->route('users.index')->with('success', __('User added successfully'));
    }


    public function edit($id)
    {
        $user = $this->userService->findUser($id);
        $role = $user->roles->pluck('name')->first(); // اسم واحد فقط
        if (request()->ajax()) {
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $role, // اسم واحد فقط
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable',
            'roles' => 'required|string'
        ]);
        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        if (!empty($data['password'])) {
            $updateData['password'] = bcrypt($data['password']);
        }
        $user = $this->userService->updateUser($id, $updateData);
        $user->syncRoles([$data['roles']]);
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('تم تحديث المستخدم بنجاح'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => implode(', ', $user->getRoleNames()->toArray()),
                ]
            ]);
        }
        return redirect()->route('users.index')->with('success', __('User updated successfully'));
    }

    public function destroy(Request $request, $id)
    {
        $this->userService->deleteUser($id);
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => __('تم حذف المستخدم بنجاح'),
                'user_id' => $id
            ]);
        }
        return redirect()->route('users.index')->with('success', __('User deleted successfully'));
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('perPage', 10);
        $users = $this->userService->searchUsers($search, $perPage);

        $view = view('users.partials.table', compact('users'))->render();
        $pagination = $users->appends($request->all())->links()->render();

        return response()->json(['html' => $view, 'pagination' => $pagination]);
    }
}
