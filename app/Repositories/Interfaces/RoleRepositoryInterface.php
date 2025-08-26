<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface RoleRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Model;
    public function getModel(): Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): ?Model;
    public function delete(int $id): bool;
    public function paginateWithPermissions(int $perPage, string $search = '');
}
