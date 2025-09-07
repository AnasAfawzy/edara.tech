<?php

namespace App\Repositories\Interfaces;

use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface WarehouseRepositoryInterface
{
    /**
     * Get all warehouses with optional search and pagination
     */
    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator;

    /**
     * Get all active warehouses
     */
    public function getAllActive(): Collection;

    /**
     * Find warehouse by ID
     */
    public function findById(int $id): ?Warehouse;

    /**
     * Find warehouse by ID or fail
     */
    public function findByIdOrFail(int $id): Warehouse;

    /**
     * Create new warehouse
     */
    public function create(array $data): Warehouse;

    /**
     * Update warehouse - يجب أن يرجع nullable لتطابق BaseRepository
     */
    public function update(int $id, array $data): ?Warehouse;

    /**
     * Delete warehouse
     */
    public function delete(int $id): bool;

    /**
     * Check if warehouse name exists
     */
    public function nameExists(string $name, int $excludeId = null): bool;

    /**
     * Get warehouses count
     */
    public function getCount(): int;

    /**
     * Get active warehouses count
     */
    public function getActiveCount(): int;

    /**
     * Toggle warehouse status
     */
    public function toggleStatus(int $id): ?Warehouse;

    /**
     * Get warehouse statistics
     */
    public function getStatistics(): array;
}
