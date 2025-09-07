<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\UnitRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UnitService extends BaseService
{
    protected UnitRepositoryInterface $unitRepository;

    public function __construct(UnitRepositoryInterface $unitRepository)
    {
        parent::__construct($unitRepository);
        $this->unitRepository = $unitRepository;
    }

    /**
     * Search units with pagination
     */
    public function searchUnits(?string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $search = trim($search);

        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->unitRepository->getAllWithSearch($search, $perPage);
    }

    /**
     * Create new unit with validation
     */
    public function createUnit(array $data): Model
    {
        // Validate unique name
        if ($this->unitRepository->nameExists($data['name'])) {
            throw new Exception(__('Unit name already exists'));
        }

        // Validate unique symbol
        if ($this->unitRepository->symbolExists($data['symbol'])) {
            throw new Exception(__('Unit symbol already exists'));
        }

        // تحويل status إلى boolean إذا لم يكن كذلك
        if (isset($data['status'])) {
            $data['status'] = (bool) $data['status'];
        } else {
            $data['status'] = true;
        }

        // إضافة معلومات المستخدم
        $data['created_by'] = Auth::id();

        return $this->create($data);
    }

    /**
     * Update unit with validation
     */
    public function updateUnit(int $id, array $data): Model
    {
        // Validate unique name (excluding current unit)
        if ($this->unitRepository->nameExists($data['name'], $id)) {
            throw new Exception(__('Unit name already exists'));
        }

        // Validate unique symbol (excluding current unit)
        if (isset($data['symbol']) && $this->unitRepository->symbolExists($data['symbol'], $id)) {
            throw new Exception(__('Unit symbol already exists'));
        }

        // تحويل status إلى boolean
        if (isset($data['status'])) {
            $data['status'] = (bool) $data['status'];
        }

        // إضافة معلومات المستخدم
        $data['updated_by'] = Auth::id();

        $unit = $this->update($id, $data);
        if (!$unit) {
            throw new Exception(__('Unit not found'));
        }

        return $unit;
    }

    /**
     * Delete unit
     */
    public function deleteUnit(int $id): bool
    {
        $unit = $this->find($id);
        if (!$unit) {
            throw new Exception(__('Unit not found'));
        }

        // التحقق من وجود منتجات مرتبطة
        if ($unit->products()->exists()) {
            throw new Exception(__('Cannot delete unit with products'));
        }

        return $this->delete($id);
    }

    /**
     * Toggle unit status
     */
    public function toggleStatus(int $id): Model
    {
        $unit = $this->find($id);
        if (!$unit) {
            throw new Exception(__('Unit not found'));
        }

        $newStatus = !$unit->status;

        $updatedUnit = $this->update($id, ['status' => $newStatus]);
        if (!$updatedUnit) {
            throw new Exception(__('Failed to update unit status'));
        }

        return $updatedUnit;
    }

    /**
     * Get all active units
     */
    public function getAllActiveUnits(): Collection
    {
        return $this->unitRepository->getAllActive();
    }

    /**
     * Find unit by ID
     */
    public function findUnit(int $id)
    {
        return $this->find($id);
    }

    /**
     * Find unit by ID or fail
     */
    public function findUnitOrFail(int $id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Get unit statistics
     */
    public function getStatistics(): array
    {
        return $this->unitRepository->getStatistics();
    }

    /**
     * Check if unit name is available
     */
    public function isNameAvailable(string $name, int $excludeId = null): bool
    {
        return !$this->unitRepository->nameExists($name, $excludeId);
    }

    /**
     * Check if unit symbol is available
     */
    public function isSymbolAvailable(string $symbol, int $excludeId = null): bool
    {
        return !$this->unitRepository->symbolExists($symbol, $excludeId);
    }

    /**
     * Get units count
     */
    public function getUnitsCount(): int
    {
        return $this->unitRepository->getCount();
    }

    /**
     * Get active units count
     */
    public function getActiveUnitsCount(): int
    {
        return $this->unitRepository->getActiveCount();
    }
}
