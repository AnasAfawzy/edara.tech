<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\ProductRepositoryInterface;
use Illuminate\Support\Facades\{Auth, Storage};
use Illuminate\Http\UploadedFile;

class ProductService extends BaseService
{
    protected ProductRepositoryInterface $productRepository;

    public function __construct(ProductRepositoryInterface $productRepository)
    {
        parent::__construct($productRepository);
        $this->productRepository = $productRepository;
    }

    /**
     * Search products with pagination
     */
    public function searchProducts(?string $search = '', array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $search = trim($search);

        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->productRepository->getAllWithSearch($search, $filters, $perPage);
    }

    /**
     * Create new product with validation
     */
    public function createProduct(array $data): Model
    {
        // Validate unique code
        if ($this->productRepository->codeExists($data['code'])) {
            throw new Exception(__('Product code already exists'));
        }

        // Validate unique barcode if provided
        if (!empty($data['barcode']) && $this->productRepository->barcodeExists($data['barcode'])) {
            throw new Exception(__('Product barcode already exists'));
        }

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $this->uploadProductImage($data['image']);
        }

        // تحويل البيانات المنطقية
        $data['has_expiry'] = isset($data['has_expiry']) ? (bool) $data['has_expiry'] : false;
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        // إضافة معلومات المستخدم
        $data['created_by'] = Auth::id();

        // التأكد من صحة الأسعار
        if ($data['sale_price'] <= $data['purchase_price']) {
            throw new Exception(__('Sale price must be greater than purchase price'));
        }

        return $this->create($data);
    }

    /**
     * Update product with validation
     */
    public function updateProduct(int $id, array $data): Model
    {
        // Validate unique code (excluding current product)
        if ($this->productRepository->codeExists($data['code'], $id)) {
            throw new Exception(__('Product code already exists'));
        }

        // Validate unique barcode if provided (excluding current product)
        if (!empty($data['barcode']) && $this->productRepository->barcodeExists($data['barcode'], $id)) {
            throw new Exception(__('Product barcode already exists'));
        }

        $product = $this->find($id);
        if (!$product) {
            throw new Exception(__('Product not found'));
        }

        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $this->uploadProductImage($data['image']);
        }

        // تحويل البيانات المنطقية
        $data['has_expiry'] = isset($data['has_expiry']) ? (bool) $data['has_expiry'] : false;
        $data['is_active'] = isset($data['is_active']) ? (bool) $data['is_active'] : true;

        // إضافة معلومات المستخدم
        $data['updated_by'] = Auth::id();

        // التأكد من صحة الأسعار
        if ($data['sale_price'] <= $data['purchase_price']) {
            throw new Exception(__('Sale price must be greater than purchase price'));
        }

        $updatedProduct = $this->update($id, $data);
        if (!$updatedProduct) {
            throw new Exception(__('Product not found'));
        }

        return $updatedProduct;
    }

    /**
     * Delete product
     */
    public function deleteProduct(int $id): bool
    {
        $product = $this->find($id);
        if (!$product) {
            throw new Exception(__('Product not found'));
        }

        // التحقق من وجود حركات مخزونية أو مبيعات/مشتريات
        // يمكن إضافة التحقق هنا حسب الحاجة

        // Delete image if exists
        if ($product->image && Storage::disk('public')->exists($product->image)) {
            Storage::disk('public')->delete($product->image);
        }

        return $this->delete($id);
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(int $id): Model
    {
        $product = $this->find($id);
        if (!$product) {
            throw new Exception(__('Product not found'));
        }

        $newStatus = !$product->is_active;

        $updatedProduct = $this->update($id, ['is_active' => $newStatus]);
        if (!$updatedProduct) {
            throw new Exception(__('Failed to update product status'));
        }

        return $updatedProduct;
    }

    /**
     * Update product stock
     */
    public function updateProductStock(int $id, float $quantity, string $operation = 'add'): Model
    {
        $success = $this->productRepository->updateStock($id, $quantity, $operation);
        if (!$success) {
            throw new Exception(__('Failed to update product stock'));
        }

        return $this->findProductOrFail($id);
    }

    /**
     * Get low stock products
     */
    public function getLowStockProducts(int $perPage = 10): LengthAwarePaginator
    {
        return $this->productRepository->getLowStockProducts($perPage);
    }

    /**
     * Find product by code
     */
    public function findByCode(string $code)
    {
        return $this->productRepository->findByCode($code);
    }

    /**
     * Find product by barcode
     */
    public function findByBarcode(string $barcode)
    {
        return $this->productRepository->findByBarcode($barcode);
    }

    /**
     * Find product by ID or fail
     */
    public function findProductOrFail(int $id)
    {
        return $this->productRepository->findByIdOrFail($id);
    }

    /**
     * Upload product image
     */
    private function uploadProductImage(UploadedFile $image): string
    {
        $fileName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
        $path = $image->storeAs('products', $fileName, 'public');

        return $path;
    }

    /**
     * Generate unique product code
     */
    public function generateProductCode(string $prefix = 'PRD'): string
    {
        $lastProduct = $this->productRepository->getAllWithSearch()->first();
        $lastNumber = $lastProduct ? (int) substr($lastProduct->code, strlen($prefix) + 1) : 0;
        $newNumber = $lastNumber + 1;

        return $prefix . '-' . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }
}
