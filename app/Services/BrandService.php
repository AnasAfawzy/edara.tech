<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\BrandRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class BrandService extends BaseService
{
    protected BrandRepositoryInterface $brandRepository;

    public function __construct(BrandRepositoryInterface $brandRepository)
    {
        parent::__construct($brandRepository);
        $this->brandRepository = $brandRepository;
    }

    /**
     * Search brands with pagination
     */
    public function searchBrands(?string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $search = trim($search);

        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->brandRepository->getAllWithSearch($search, $perPage);
    }

    /**
     * Create new brand with validation
     */
    public function createBrand(array $data): Model
    {
        // Validate unique name
        if ($this->brandRepository->nameExists($data['name'])) {
            throw new Exception(__('Brand name already exists'));
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
     * Update brand with validation
     */
    public function updateBrand(int $id, array $data): Model
    {
        // Validate unique name (excluding current brand)
        if ($this->brandRepository->nameExists($data['name'], $id)) {
            throw new Exception(__('Brand name already exists'));
        }

        // تحويل status إلى boolean
        if (isset($data['status'])) {
            $data['status'] = (bool) $data['status'];
        }

        // إضافة معلومات المستخدم
        $data['updated_by'] = Auth::id();

        $brand = $this->update($id, $data);
        if (!$brand) {
            throw new Exception(__('Brand not found'));
        }

        return $brand;
    }

    /**
     * Delete brand
     */
    public function deleteBrand(int $id): bool
    {
        $brand = $this->find($id);
        if (!$brand) {
            throw new Exception(__('Brand not found'));
        }

        // التحقق من وجود منتجات مرتبطة
        if ($brand->products()->exists()) {
            throw new Exception(__('Cannot delete brand with products'));
        }

        return $this->delete($id);
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(int $id): Model
    {
        $brand = $this->find($id);
        if (!$brand) {
            throw new Exception(__('Brand not found'));
        }

        $newStatus = !$brand->status;

        $updatedBrand = $this->update($id, ['status' => $newStatus]);
        if (!$updatedBrand) {
            throw new Exception(__('Failed to update brand status'));
        }

        return $updatedBrand;
    }

    /**
     * Find brand by ID or fail
     */
    public function findBrandOrFail(int $id)
    {
        return $this->findOrFail($id);
    }
}
