<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductCategoryRepositoryInterface
{
    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function nameExists(string $name, int $excludeId = null): bool;
    public function findById(int $id);
    public function findByIdOrFail(int $id);
    public function getStatistics(): array;
    public function getCount(): int;
    public function getActiveCount(): int;
}
