<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\ProductRepositoryInterface;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all products with search and pagination
     */
    public function getAllWithSearch(string $search = '', array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['category', 'brand', 'unit']);

        if (!empty($search)) {
            $query->search($search);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        if (!empty($filters['unit_id'])) {
            $query->where('unit_id', $filters['unit_id']);
        }
        // أهم شرط: لا تستخدم where إذا كانت القيمة فارغة
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['stock_status'])) {
            switch ($filters['stock_status']) {
                case 'low':
                    $query->lowStock();
                    break;
                case 'high':
                    $query->whereColumn('current_stock', '>=', 'max_stock')
                        ->whereNotNull('max_stock');
                    break;
            }
        }

        // دائماً استخدم paginate حتى لو لم يوجد فلتر
        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get all active products
     */
    public function getAllActive(): Collection
    {
        return $this->model->active()->with(['category', 'brand', 'unit'])->orderBy('name')->get();
    }

    /**
     * Check if product code exists
     */
    public function codeExists(string $code, int $excludeId = null): bool
    {
        $query = $this->model->where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Check if barcode exists
     */
    public function barcodeExists(string $barcode, int $excludeId = null): bool
    {
        $query = $this->model->where('barcode', $barcode);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Find product by ID
     */
    public function findById(int $id)
    {
        return $this->model->with(['category', 'brand', 'unit'])->find($id);
    }

    /**
     * Find product by ID or fail
     */
    public function findByIdOrFail(int $id)
    {
        return $this->model->with(['category', 'brand', 'unit'])->findOrFail($id);
    }

    /**
     * Find product by code
     */
    public function findByCode(string $code)
    {
        return $this->model->with(['category', 'brand', 'unit'])->where('code', $code)->first();
    }

    /**
     * Find product by barcode
     */
    public function findByBarcode(string $barcode)
    {
        return $this->model->with(['category', 'brand', 'unit'])->where('barcode', $barcode)->first();
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->lowStock()
            ->with(['category', 'brand', 'unit'])
            ->orderBy('current_stock')
            ->paginate($perPage);
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(int $categoryId): Collection
    {
        return $this->model->where('category_id', $categoryId)
            ->active()
            ->with(['brand', 'unit'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Get products by brand
     */
    public function getProductsByBrand(int $brandId): Collection
    {
        return $this->model->where('brand_id', $brandId)
            ->active()
            ->with(['category', 'unit'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Update product stock
     */
    public function updateStock(int $productId, float $quantity, string $operation = 'add'): bool
    {
        $product = $this->find($productId);
        if (!$product) {
            return false;
        }

        switch ($operation) {
            case 'add':
                $newStock = $product->current_stock + $quantity;
                break;
            case 'subtract':
                $newStock = $product->current_stock - $quantity;
                break;
            case 'set':
                $newStock = $quantity;
                break;
            default:
                return false;
        }

        // التأكد من أن المخزون لا يصبح سالباً
        if ($newStock < 0) {
            $newStock = 0;
        }

        return $this->update($productId, ['current_stock' => $newStock]) ? true : false;
    }
}
