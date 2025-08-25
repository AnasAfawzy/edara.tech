<?php

namespace App\Repositories\Interfaces;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface CurrencyRepositoryInterface
{
    public function all(): Collection;
    public function paginate(int $perPage = 10): LengthAwarePaginator;
    public function searchAndPaginate(string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): ?Model;
    public function getModel(): Model;
    public function create(array $data): Model;
    // public function update(int $id, array $data): Model;
    // public function delete(int $id): bool;
}
