<?php

namespace App\Repositories;

use App\Models\Warehouse;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class WarehouseRepository extends BaseRepository implements WarehouseRepositoryInterface
{
    public function __construct(Warehouse $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all warehouses with optional search and pagination
     * تحسين للتعامل مع البحث الفارغ
     */
    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query();

        // التحقق من وجود قيمة بحث صحيحة
        if (!empty($search) && trim($search) !== '') {
            $searchTerm = '%' . trim($search) . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('notes', 'like', $searchTerm);
            });
        }
        // إذا كان البحث فارغ، سيتم إرجاع كل المخازن

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get all active warehouses
     */
    public function getAllActive(): Collection
    {
        return $this->model->where('status', 'active')->orderBy('name')->get();
    }

    /**
     * Find warehouse by ID
     */
    public function findById(int $id): ?Warehouse
    {
        return $this->find($id);
    }

    /**
     * Find warehouse by ID or fail
     */
    public function findByIdOrFail(int $id): Warehouse
    {
        return $this->findOrFail($id);
    }

    /**
     * Create new warehouse (override to add user tracking)
     */
    public function create(array $data): Warehouse
    {
        $data['created_by'] = Auth::id();
        return parent::create($data);
    }

    /**
     * Update warehouse (متطابق مع Interface - nullable return)
     */
    public function update(int $id, array $data): ?Warehouse
    {
        $warehouse = $this->find($id);
        if (!$warehouse) {
            return null;
        }

        $data['updated_by'] = Auth::id();
        $warehouse->update($data);
        return $warehouse->fresh();
    }

    /**
     * Delete warehouse
     */
    public function delete(int $id): bool
    {
        return parent::delete($id);
    }

    /**
     * Check if warehouse name exists
     */
    public function nameExists(string $name, int $excludeId = null): bool
    {
        $query = $this->model->where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Get warehouses count
     */
    public function getCount(): int
    {
        return $this->model->count();
    }

    /**
     * Get active warehouses count
     */
    public function getActiveCount(): int
    {
        return $this->model->where('status', 'active')->count();
    }

    /**
     * Toggle warehouse status
     */
    public function toggleStatus(int $id): ?Warehouse
    {
        $warehouse = $this->find($id);
        if (!$warehouse) {
            return null;
        }

        $newStatus = $warehouse->status === 'active' ? 'inactive' : 'active';
        $this->update($id, ['status' => $newStatus]);
        return $warehouse->fresh();
    }

    /**
     * Get warehouse statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->getCount(),
            'active' => $this->getActiveCount(),
            'inactive' => $this->getCount() - $this->getActiveCount(),
        ];
    }
}
