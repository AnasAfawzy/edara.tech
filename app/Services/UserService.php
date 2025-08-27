<?php

namespace App\Services;

use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\User;

class UserService
{
    protected $userRepo;

    public function __construct(UserRepositoryInterface $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function paginateWithRoles(int $perPage, string $search = ''): LengthAwarePaginator
    {
        return $this->userRepo->paginateWithRoles($perPage, $search);
    }

    public function findUser(int $id): ?User
    {
        return $this->userRepo->find($id);
    }

    public function createUser(array $data): User
    {
        return $this->userRepo->createUser($data);
    }

    public function updateUser(int $id, array $data): User
    {
        return $this->userRepo->updateUser($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepo->deleteUser($id);
    }

    public function searchUsers($search = '', $perPage = 10)
    {
        return $this->userRepo->getModel()
            ->with('roles') // <--- دي هترجع الأدوار لكل مستخدم
            ->when($search, function ($q) use ($search) {
                $q->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
