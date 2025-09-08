<?php

namespace App\Repositories;

use App\Models\OpeningStock;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\OpeningStockRepositoryInterface;
use Illuminate\Support\Facades\DB;

class OpeningStockRepository extends BaseRepository implements OpeningStockRepositoryInterface
{
    public function __construct(OpeningStock $model)
    {
        parent::__construct($model);
    }

    public function getAllWithSearch(string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['product.category', 'product.brand', 'product.unit', 'creator']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($productQuery) use ($search) {
                    $productQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                })
                    ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('opening_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getAllActive(): Collection
    {
        return $this->model->with(['product'])
            ->where('is_active', true)
            ->orderBy('opening_date', 'desc')
            ->get();
    }

    public function getProductsForBulkEntry(): Collection
    {
        return Product::with(['category', 'brand', 'unit'])
            ->select('id', 'name', 'name_en', 'code', 'category_id', 'brand_id', 'unit_id')
            ->whereDoesntHave('openingStocks', function ($query) {
                $query->where('is_active', true);
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function bulkCreate(array $data): bool
    {
        try {
            DB::beginTransaction();

            foreach ($data as $item) {
                $this->model->create($item);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkUpdate(array $data): bool
    {
        try {
            DB::beginTransaction();

            foreach ($data as $item) {
                $this->model->where('id', $item['id'])->update($item);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findByProductId(int $productId)
    {
        return $this->model->where('product_id', $productId)
            ->where('is_active', true)
            ->latest('opening_date')
            ->first();
    }

    public function findByProductAndDate(int $productId, string $date)
    {
        return $this->model->where('product_id', $productId)
            ->where('opening_date', $date)
            ->first();
    }

    public function getByDateRange(string $fromDate, string $toDate): Collection
    {
        return $this->model->with(['product'])
            ->whereBetween('opening_date', [$fromDate, $toDate])
            ->where('is_active', true)
            ->orderBy('opening_date')
            ->get();
    }

    public function productHasOpeningStock(int $productId): bool
    {
        return $this->model->where('product_id', $productId)
            ->where('is_active', true)
            ->exists();
    }
}
