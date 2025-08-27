<?php

namespace App\Repositories\Interfaces;

use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Collection;

interface FinancialYearRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): FinancialYear;
    public function create(array $data): FinancialYear;
    public function update(int $id, array $data): ?FinancialYear;
    public function delete(int $id): bool;
    public function getActive(): ?FinancialYear;
    public function getByDate(string $date): ?FinancialYear;
    public function activate(int $id): bool;
    public function close(int $id): bool;
    public function checkOverlapping(string $startDate, string $endDate, ?int $excludeId = null): bool;
}
