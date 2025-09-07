<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\WarehouseRepositoryInterface;
use App\Services\AccountService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WarehouseService extends BaseService
{
    protected WarehouseRepositoryInterface $warehouseRepository;
    protected AccountService $accountService;
    protected $parentAccountId;

    public function __construct(WarehouseRepositoryInterface $warehouseRepository, AccountService $accountService)
    {
        parent::__construct($warehouseRepository);
        $this->warehouseRepository = $warehouseRepository;
        $this->accountService = $accountService;
        $this->parentAccountId = acc_setting('default_warehouse_account');
    }

    /**
     * Get all warehouses with their accounts
     */
    public function getAllWarehouses()
    {
        if (!$this->parentAccountId) {
            return collect();
        }

        return $this->accountService->getChildrenOf($this->parentAccountId);
    }

    /**
     * Search warehouses with pagination
     */
    public function searchWarehouses(?string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $search = trim($search);

        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->warehouseRepository->getAllWithSearch($search, $perPage);
    }

    /**
     * Create new warehouse with validation and account creation
     */
    public function createWarehouse(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                // التحقق من وجود الحساب الرئيسي
                if (!$this->parentAccountId) {
                    throw new Exception(__('Default warehouse account not configured. Please configure it in accounting settings.'));
                }

                // Validate unique name
                if ($this->warehouseRepository->nameExists($data['name'])) {
                    throw new Exception(__('Warehouse name already exists'));
                }

                // تحويل status إلى boolean إذا لم يكن كذلك
                if (isset($data['status'])) {
                    $data['status'] = (bool) $data['status'];
                } else {
                    $data['status'] = true; // افتراضي نشط
                }

                $parentAccountInfo = $this->accountService->find($this->parentAccountId);
                // التأكد من أن has_sub == 1 إذا كان الحساب الرئيسي يسمح بذلك
                $is_sub = 0;
                if (isset($parentAccountInfo->has_sub)) {
                    $is_sub = ($parentAccountInfo->has_sub == 1) ? 1 : 0;
                }
                // 1️⃣ إنشاء الحساب أولاً
                $accountData = [
                    'name'    => $data['name'],
                    'ownerEl' => $this->parentAccountId,
                    'slave'   => 1,
                    'has_sub' => 0,
                    'is_sub'  => $is_sub,
                    'level'   => 0,
                ];

                $accountData = $this->accountService->generateAccountData($accountData);
                $account = $this->accountService->create($accountData);

                if (!$account || !$account->id) {
                    throw new Exception(__('Warehouse account creation failed'));
                }

                // 2️⃣ إنشاء المخزن وربطه بالحساب
                $warehouseData = $data;
                $warehouseData['account_id'] = $account->id;
                $warehouse = $this->warehouseRepository->create($warehouseData);

                if (!$warehouse || !$warehouse->id) {
                    throw new Exception(__('Failed to create warehouse'));
                }

                Log::info('Warehouse created with account', [
                    'warehouse_id' => $warehouse->id,
                    'account_id' => $account->id,
                    'name' => $warehouse->name
                ]);

                return $warehouse;
            });
        } catch (Exception $e) {
            Log::error('Error creating warehouse: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Update warehouse with validation
     */
    public function updateWarehouse(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                // Validate unique name (excluding current warehouse)
                if ($this->warehouseRepository->nameExists($data['name'], $id)) {
                    throw new Exception(__('Warehouse name already exists'));
                }

                // تحويل status إلى boolean
                if (isset($data['status'])) {
                    $data['status'] = (bool) $data['status'];
                }

                $warehouse = $this->warehouseRepository->update($id, $data);
                if (!$warehouse) {
                    throw new Exception(__('Warehouse not found'));
                }

                // تحديث اسم الحساب المرتبط إذا تغير اسم المخزن
                if (isset($data['name']) && $warehouse->account_id) {
                    $this->accountService->update($warehouse->account_id, [
                        'name' => $data['name']
                    ]);
                }

                Log::info('Warehouse updated', [
                    'warehouse_id' => $id,
                    'account_id' => $warehouse->account_id,
                    'name' => $warehouse->name
                ]);

                return $warehouse;
            });
        } catch (Exception $e) {
            Log::error('Error updating warehouse: ' . $e->getMessage(), [
                'warehouse_id' => $id,
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Delete warehouse with account cleanup
     */
    public function deleteWarehouse(int $id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $warehouse = $this->warehouseRepository->findById($id);
                if (!$warehouse) {
                    throw new Exception(__('Warehouse not found'));
                }

                // التحقق من وجود منتجات أو معاملات مرتبطة
                // يمكنك إضافة المزيد من الفحوصات هنا حسب الحاجة

                // التحقق من الرصيد إذا كان للمخزن حساب
                if ($warehouse->account_id && $warehouse->account) {
                    $account = $warehouse->account;
                    if ($account->balance != 0) {
                        throw new Exception(__('Cannot delete warehouse with non-zero account balance'));
                    }

                    // التحقق من عدم وجود قيود يومية
                    if ($account->journalEntries()->exists()) {
                        throw new Exception(__('Cannot delete warehouse because its account has journal entries'));
                    }
                }

                // حفظ account_id قبل الحذف
                $accountId = $warehouse->account_id;

                // حذف المخزن أولاً
                $deleted = $this->warehouseRepository->delete($id);

                // ثم حذف الحساب المرتبط إذا وجد
                if ($deleted && $accountId) {
                    $this->accountService->delete($accountId);
                }

                Log::info('Warehouse deleted with account', [
                    'warehouse_id' => $id,
                    'account_id' => $accountId
                ]);

                return $deleted;
            });
        } catch (Exception $e) {
            Log::error('Error deleting warehouse: ' . $e->getMessage(), [
                'warehouse_id' => $id
            ]);
            throw $e;
        }
    }

    /**
     * Toggle warehouse status
     */
    public function toggleStatus(int $id)
    {
        $warehouse = $this->warehouseRepository->findById($id);
        if (!$warehouse) {
            throw new Exception(__('Warehouse not found'));
        }

        // تبديل الحالة: 1 إلى 0 أو 0 إلى 1
        $newStatus = !$warehouse->status;

        $updatedWarehouse = $this->warehouseRepository->update($id, ['status' => $newStatus]);
        if (!$updatedWarehouse) {
            throw new Exception(__('Failed to update warehouse status'));
        }

        return $updatedWarehouse;
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

    // Override parent methods to ensure proper return types
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
