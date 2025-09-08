<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockMovementService extends BaseService
{
    protected StockMovementRepositoryInterface $stockMovementRepository;

    public function __construct(StockMovementRepositoryInterface $stockMovementRepository)
    {
        parent::__construct($stockMovementRepository);
        $this->stockMovementRepository = $stockMovementRepository;
    }

    public function searchStockMovements(?string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $search = trim($search);

        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->stockMovementRepository->getAllWithSearch($search, $perPage);
    }

    public function recordMovement(array $data): Model
    {
        try {
            DB::beginTransaction();

            // حساب الرصيد الجاري
            $previousBalance = $this->stockMovementRepository->getLastMovementBalance($data['product_id']);

            $newBalance = $data['movement_type'] === 'in'
                ? $previousBalance + $data['quantity']
                : $previousBalance - $data['quantity'];

            $movementData = array_merge($data, [
                'total_cost' => $data['quantity'] * $data['unit_cost'],
                'current_balance' => $newBalance,
                'created_by' => Auth::id()
            ]);

            $movement = $this->create($movementData);

            DB::commit();
            return $movement;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception(__('Failed to record stock movement: ') . $e->getMessage());
        }
    }

    public function bulkRecordMovements(array $movements): array
    {
        try {
            DB::beginTransaction();

            $createdCount = 0;
            $errors = [];

            foreach ($movements as $movementData) {
                try {
                    $this->recordMovement($movementData);
                    $createdCount++;
                } catch (\Exception $e) {
                    $errors[] = "خطأ في تسجيل حركة المنتج ID {$movementData['product_id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'created_count' => $createdCount,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception(__('Failed to record movements: ') . $e->getMessage());
        }
    }

    public function getProductMovements(int $productId, string $fromDate = null, string $toDate = null): Collection
    {
        return $this->stockMovementRepository->getProductMovements($productId, $fromDate, $toDate);
    }

    public function getProductBalance(int $productId, string $date = null): float
    {
        if ($date) {
            $movements = $this->stockMovementRepository->getProductMovements($productId, null, $date);
            $balance = 0;

            foreach ($movements as $movement) {
                $balance += $movement->movement_type === 'in'
                    ? $movement->quantity
                    : -$movement->quantity;
            }

            return $balance;
        }

        return $this->stockMovementRepository->getLastMovementBalance($productId);
    }

    public function findByReference(string $referenceType, int $referenceId)
    {
        return $this->stockMovementRepository->findByReference($referenceType, $referenceId);
    }

    public function updateMovement(int $id, array $data): Model
    {
        try {
            DB::beginTransaction();

            $movement = $this->find($id);
            if (!$movement) {
                throw new Exception(__('Stock movement not found'));
            }

            // إعادة حساب البيانات
            if (isset($data['quantity']) && isset($data['unit_cost'])) {
                $data['total_cost'] = $data['quantity'] * $data['unit_cost'];
            }

            $updated = $this->update($id, $data);

            // إعادة حساب الأرصدة لهذا المنتج
            $this->stockMovementRepository->recalculateBalances($movement->product_id);

            DB::commit();
            return $updated;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception(__('Failed to update movement: ') . $e->getMessage());
        }
    }

    public function deleteMovement(int $id): bool
    {
        try {
            DB::beginTransaction();

            $movement = $this->find($id);
            if (!$movement) {
                throw new Exception(__('Stock movement not found'));
            }

            $productId = $movement->product_id;
            $deleted = $this->delete($id);

            // إعادة حساب الأرصدة لهذا المنتج
            $this->stockMovementRepository->recalculateBalances($productId);

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception(__('Failed to delete movement: ') . $e->getMessage());
        }
    }

    public function recalculateProductBalances(int $productId): bool
    {
        return $this->stockMovementRepository->recalculateBalances($productId);
    }
}
