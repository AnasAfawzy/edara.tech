<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;

class WarehouseService extends BaseService
{
    protected WarehouseRepositoryInterface $warehouseRepository;

    public function __construct(WarehouseRepositoryInterface $warehouseRepository)
    {
        parent::__construct($warehouseRepository);
        $this->warehouseRepository = $warehouseRepository;
    }

    /**
     * Search warehouses with pagination - تحسين للتعامل مع null/empty values
     */
    public function searchWarehouses(?string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        // التأكد من أن search هو string صحيح
        $search = $search ?? '';
        $search = trim($search); // إزالة المسافات الزائدة

        // التحقق من صحة perPage
        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->warehouseRepository->getAllWithSearch($search, $perPage);
    }

    /**
     * Get all active warehouses
     */
    public function getAllActiveWarehouses(): Collection
    {
        return $this->warehouseRepository->getAllActive();
    }

    /**
     * Find warehouse by ID
     */
    public function findWarehouse(int $id)
    {
        return $this->warehouseRepository->findById($id);
    }

    /**
     * Find warehouse by ID or fail
     */
    public function findWarehouseOrFail(int $id)
    {
        return $this->warehouseRepository->findByIdOrFail($id);
    }

    /**
     * Create new warehouse with validation
     */
    public function createWarehouse(array $data)
    {
        // Validate unique name
        if ($this->warehouseRepository->nameExists($data['name'])) {
            throw new Exception(__('Warehouse name already exists'));
        }

        // Set default status if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        return $this->warehouseRepository->create($data);
    }

    /**
     * Update warehouse with validation
     */
    public function updateWarehouse(int $id, array $data)
    {
        // Validate unique name (excluding current warehouse)
        if ($this->warehouseRepository->nameExists($data['name'], $id)) {
            throw new Exception(__('Warehouse name already exists'));
        }

        $warehouse = $this->warehouseRepository->update($id, $data);
        if (!$warehouse) {
            throw new Exception(__('Warehouse not found'));
        }

        return $warehouse;
    }

    /**
     * Delete warehouse
     */
    public function deleteWarehouse(int $id): bool
    {
        $warehouse = $this->warehouseRepository->findById($id);
        if (!$warehouse) {
            throw new Exception(__('Warehouse not found'));
        }

        // Add business logic checks here
        // For example: check if warehouse has products
        // if ($warehouse->products()->exists()) {
        //     throw new Exception(__('Cannot delete warehouse with existing products'));
        // }

        return $this->warehouseRepository->delete($id);
    }

    /**
     * Toggle warehouse status
     */
    public function toggleStatus(int $id)
    {
        $warehouse = $this->warehouseRepository->toggleStatus($id);
        if (!$warehouse) {
            throw new Exception(__('Warehouse not found'));
        }
        return $warehouse;
    }

    /**
     * Get warehouse statistics
     */
    public function getStatistics(): array
    {
        return $this->warehouseRepository->getStatistics();
    }

    /**
     * Check if warehouse name is available
     */
    public function isNameAvailable(string $name, int $excludeId = null): bool
    {
        return !$this->warehouseRepository->nameExists($name, $excludeId);
    }

    /**
     * Get warehouses count
     */
    public function getWarehousesCount(): int
    {
        return $this->warehouseRepository->getCount();
    }

    /**
     * Get active warehouses count
     */
    public function getActiveWarehousesCount(): int
    {
        return $this->warehouseRepository->getActiveCount();
    }

    /**
     * Override parent methods to ensure proper return types
     */
    public function find($id)
    {
        return $this->findWarehouse($id);
    }

    public function findOrFail($id)
    {
        return $this->findWarehouseOrFail($id);
    }

    public function update($id, array $data): Model
    {
        return $this->updateWarehouse($id, $data);
    }

    public function delete($id): bool
    {
        return $this->deleteWarehouse($id);
    }
}
