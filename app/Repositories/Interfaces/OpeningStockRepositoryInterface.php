<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OpeningStockRepositoryInterface
{
    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function getProductsForBulkEntry(): Collection;
    public function bulkCreate(array $data): bool;
    public function bulkUpdate(array $data): bool;
    public function findByProductId(int $productId);
    public function findByProductAndDate(int $productId, string $date);
    public function getByDateRange(string $fromDate, string $toDate): Collection;
    public function productHasOpeningStock(int $productId): bool;
}
