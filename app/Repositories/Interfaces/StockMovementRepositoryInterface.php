<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getProductMovements(int $productId, string $fromDate = null, string $toDate = null): Collection;
    public function getLastMovementBalance(int $productId): float;
    public function findByReference(string $referenceType, int $referenceId);
    public function bulkCreate(array $movements): bool;
    public function recalculateBalances(int $productId): bool;
}
