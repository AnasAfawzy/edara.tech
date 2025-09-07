<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    public function getAllWithSearch(string $search = '', array $filters = [], int $perPage = 10): LengthAwarePaginator;
    public function getAllActive(): Collection;
    public function codeExists(string $code, int $excludeId = null): bool;
    public function barcodeExists(string $barcode, int $excludeId = null): bool;
    public function findById(int $id);
    public function findByIdOrFail(int $id);
    public function findByCode(string $code);
    public function findByBarcode(string $barcode);
    public function getLowStockProducts(int $perPage = 10): LengthAwarePaginator;
    public function getProductsByCategory(int $categoryId): Collection;
    public function getProductsByBrand(int $brandId): Collection;
    public function updateStock(int $productId, float $quantity, string $operation = 'add'): bool;
}
