<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BrandRepositoryInterface
{
    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function nameExists(string $name, int $excludeId = null): bool;
    public function findById(int $id);
    public function findByIdOrFail(int $id);
}
