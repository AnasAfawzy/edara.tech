<?php

namespace App\Repositories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\UnitRepositoryInterface;

class UnitRepository extends BaseRepository implements UnitRepositoryInterface
{
    public function __construct(Unit $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all units with search and pagination
     */
    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query();

        if (!empty($search)) {
            $query->search($search);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get all active units
     */
    public function getAllActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /**
     * Check if unit name exists
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
     * Check if unit symbol exists
     */
    public function symbolExists(string $symbol, int $excludeId = null): bool
    {
        $query = $this->model->where('symbol', $symbol);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Find unit by ID
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Find unit by ID or fail
     */
    public function findByIdOrFail(int $id)
    {
        return $this->findOrFail($id);
    }

    /**
     * Get units statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_units' => $this->model->count(),
            'active_units' => $this->model->active()->count(),
            'inactive_units' => $this->model->inactive()->count(),
        ];
    }

    /**
     * Get total units count
     */
    public function getCount(): int
    {
        return $this->model->count();
    }

    /**
     * Get active units count
     */
    public function getActiveCount(): int
    {
        return $this->model->active()->count();
    }
}
