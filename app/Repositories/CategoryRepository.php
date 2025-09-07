<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\CategoryRepositoryInterface;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all categories with search and pagination
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
     * Get all active categories
     */
    public function getAllActive(): Collection
    {
        return $this->model->active()->orderBy('name')->get();
    }

    /**
     * Check if category name exists
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
     * Find category by ID
     */
    public function findById(int $id)
    {
        return $this->find($id); // استخدام دالة BaseRepository
    }

    /**
     * Find category by ID or fail
     */
    public function findByIdOrFail(int $id)
    {
        return $this->findOrFail($id); // استخدام دالة BaseRepository
    }

    /**
     * Get categories statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_categories' => $this->model->count(),
            'active_categories' => $this->model->active()->count(),
            'inactive_categories' => $this->model->inactive()->count(),
        ];
    }

    /**
     * Get total categories count
     */
    public function getCount(): int
    {
        return $this->model->count();
    }

    /**
     * Get active categories count
     */
    public function getActiveCount(): int
    {
        return $this->model->active()->count();
    }
}
