<?php

namespace App\Repositories;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\StockMovementRepositoryInterface;
use Illuminate\Support\Facades\DB;

class StockMovementRepository extends BaseRepository implements StockMovementRepositoryInterface
{
    public function __construct(StockMovement $model)
    {
        parent::__construct($model);
    }

    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['product', 'creator']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function getProductMovements(int $productId, string $fromDate = null, string $toDate = null): Collection
    {
        $query = $this->model->where('product_id', $productId);

        if ($fromDate && $toDate) {
            $query->whereBetween('movement_date', [$fromDate, $toDate]);
        }

        return $query->orderBy('movement_date')
            ->orderBy('id')
            ->get();
    }

    public function getLastMovementBalance(int $productId): float
    {
        $lastMovement = $this->model->where('product_id', $productId)
            ->orderBy('movement_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        return $lastMovement ? $lastMovement->current_balance : 0;
    }

    public function findByReference(string $referenceType, int $referenceId)
    {
        return $this->model->where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->first();
    }

    public function bulkCreate(array $movements): bool
    {
        try {
            DB::beginTransaction();

            foreach ($movements as $movement) {
                $this->model->create($movement);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function recalculateBalances(int $productId): bool
    {
        try {
            DB::beginTransaction();

            $movements = $this->model->where('product_id', $productId)
                ->orderBy('movement_date')
                ->orderBy('id')
                ->get();

            $runningBalance = 0;

            foreach ($movements as $movement) {
                $runningBalance += $movement->movement_type === 'in'
                    ? $movement->quantity
                    : -$movement->quantity;

                $movement->update(['current_balance' => $runningBalance]);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
