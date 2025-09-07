<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\BrandRepositoryInterface;

class BrandRepository extends BaseRepository implements BrandRepositoryInterface
{
    public function __construct(Brand $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all brands with search and pagination
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
     * Get all active brands
     */
    public function getAllActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /**
     * Check if brand name exists
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
     * Find brand by ID
     */
    public function findById(int $id)
    {
        return $this->find($id);
    }

    /**
     * Find brand by ID or fail
     */
    public function findByIdOrFail(int $id)
    {
        return $this->findOrFail($id);
    }
}
