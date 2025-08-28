<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface JournalEntryRepositoryInterface
{
    public function all();
    public function find(int $id);
    public function create(array $data): Model;
    public function update(int $id, array $data);
    public function delete(int $id): bool;
    public function getModel();

    // Journal Entry specific methods
    public function allWithDetails(): Collection;
    public function findWithDetails(int $id): ?Model;
    public function createWithDetails(array $data, array $details);
    public function updateWithDetails(int $id, array $data, array $details);
    public function searchEntries(array $filters): LengthAwarePaginator;
    public function getByFinancialYear(int $financialYearId): Collection;
    public function getByDateRange(string $startDate, string $endDate): Collection;
    public function getLastEntryNumber(): ?string;
    public function getNextEntryNumber(): string;
}
