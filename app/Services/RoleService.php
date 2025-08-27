<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Repositories\RoleRepository;

class RoleService extends BaseService
{
    protected $repository;

    public function __construct(RoleRepository $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    public function getAllRoles()
    {
        return $this->repository->all();
    }

    public function findRole(int $id)
    {
        return $this->repository->find($id);
    }

    public function createRole(array $data)
    {
        return $this->repository->create($data);
    }

    public function updateRole(int $id, array $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteRole(int $id)
    {
        return $this->repository->delete($id);
    }

    public function paginateWithPermissions(int $perPage, string $search = '')
    {
        return $this->repository->paginateWithPermissions($perPage, $search);
    }

    public function roleHasUsers(int $id): bool
    {
        $role = $this->findRole($id);
        if (! $role) {
            return false;
        }

        // Prefer relation if available (Role::users morph relation)
        if (method_exists($role, 'users')) {
            return (bool) $role->users()->exists();
        }

        // fallback to Spatie pivot table
        return DB::table('model_has_roles')->where('role_id', $role->id)->exists();
    }
}
