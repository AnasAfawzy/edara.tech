<?php

namespace App\Repositories\Interfaces;

use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface FinancialYearRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): ?Model;
    public function delete(int $id): bool;
    public function getActive(): ?FinancialYear;
    public function getByDate(string $date): ?FinancialYear;
    public function activate(int $id): bool;
    public function close(int $id): bool;
    public function checkOverlapping(string $startDate, string $endDate, ?int $excludeId = null): bool;
    public function searchFinancialYears($search = '', $perPage = 10);
}
