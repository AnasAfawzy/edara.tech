<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class CategoryService extends BaseService
{
    protected CategoryRepositoryInterface $categoryRepository;

    public function __construct(CategoryRepositoryInterface $categoryRepository)
    {
        parent::__construct($categoryRepository);
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Search categories with pagination
     */
    public function searchCategories(?string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $search = trim($search);

        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->categoryRepository->getAllWithSearch($search, $perPage);
    }

    /**
     * Create new category with validation
     */
    public function createCategory(array $data): Model
    {
        // Validate unique name
        if ($this->categoryRepository->nameExists($data['name'])) {
            throw new Exception(__('Category name already exists'));
        }

        // تحويل status إلى boolean إذا لم يكن كذلك
        if (isset($data['status'])) {
            $data['status'] = (bool) $data['status'];
        } else {
            $data['status'] = true;
        }

        // إضافة معلومات المستخدم
        $data['created_by'] = Auth::id();

        return $this->create($data); // استخدام دالة BaseService
    }

    /**
     * Update category with validation
     */
    public function updateCategory(int $id, array $data): Model
    {
        // Validate unique name (excluding current category)
        if ($this->categoryRepository->nameExists($data['name'], $id)) {
            throw new Exception(__('Category name already exists'));
        }

        // تحويل status إلى boolean
        if (isset($data['status'])) {
            $data['status'] = (bool) $data['status'];
        }

        // إضافة معلومات المستخدم
        $data['updated_by'] = Auth::id();

        $category = $this->update($id, $data); // استخدام دالة BaseService
        if (!$category) {
            throw new Exception(__('Category not found'));
        }

        return $category;
    }

    /**
     * Delete category
     */
    public function deleteCategory(int $id): bool
    {
        $category = $this->find($id); // استخدام دالة BaseService
        if (!$category) {
            throw new Exception(__('Category not found'));
        }

        // التحقق من وجود منتجات مرتبطة
        if ($category->products()->exists()) {
            throw new Exception(__('Cannot delete category with products'));
        }

        return $this->delete($id); // استخدام دالة BaseService
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(int $id): Model
    {
        $category = $this->find($id); // استخدام دالة BaseService
        if (!$category) {
            throw new Exception(__('Category not found'));
        }

        $newStatus = !$category->status;

        $updatedCategory = $this->update($id, ['status' => $newStatus]); // استخدام دالة BaseService
        if (!$updatedCategory) {
            throw new Exception(__('Failed to update category status'));
        }

        return $updatedCategory;
    }

    /**
     * Get all active categories
     */
    public function getAllActiveCategories(): Collection
    {
        return $this->categoryRepository->getAllActive();
    }

    /**
     * Find category by ID
     */
    public function findCategory(int $id)
    {
        return $this->find($id); // استخدام دالة BaseService
    }

    /**
     * Find category by ID or fail
     */
    public function findCategoryOrFail(int $id)
    {
        return $this->findOrFail($id); // استخدام دالة BaseService
    }

    /**
     * Get category statistics
     */
    public function getStatistics(): array
    {
        return $this->categoryRepository->getStatistics();
    }

    /**
     * Check if category name is available
     */
    public function isNameAvailable(string $name, int $excludeId = null): bool
    {
        return !$this->categoryRepository->nameExists($name, $excludeId);
    }

    /**
     * Get categories count
     */
    public function getCategoriesCount(): int
    {
        return $this->categoryRepository->getCount();
    }

    /**
     * Get active categories count
     */
    public function getActiveCategoriesCount(): int
    {
        return $this->categoryRepository->getActiveCount();
    }
}
