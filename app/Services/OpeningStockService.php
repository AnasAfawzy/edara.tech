<?php

namespace App\Services;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\OpeningStockRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OpeningStockService extends BaseService
{
    protected OpeningStockRepositoryInterface $openingStockRepository;
    protected StockMovementService $stockMovementService;

    public function __construct(
        OpeningStockRepositoryInterface $openingStockRepository,
        StockMovementService $stockMovementService
    ) {
        parent::__construct($openingStockRepository);
        $this->openingStockRepository = $openingStockRepository;
        $this->stockMovementService = $stockMovementService;
    }

    public function searchOpeningStocks(?string $search = '', int $perPage = 10): LengthAwarePaginator
    {
        $search = $search ?? '';
        $search = trim($search);

        if ($perPage < 1 || $perPage > 100) {
            $perPage = 10;
        }

        return $this->openingStockRepository->getAllWithSearch($search, $perPage);
    }

    public function getAllActiveOpeningStocks(): Collection
    {
        return $this->openingStockRepository->getAllActive();
    }

    public function getProductsForBulkEntry(): Collection
    {
        return $this->openingStockRepository->getProductsForBulkEntry();
    }

    public function bulkCreateOpeningStocks(array $data, string $openingDate): array
    {
        try {
            DB::beginTransaction();

            $createdCount = 0;
            $errors = [];
            $userId = Auth::id();

            foreach ($data as $item) {
                try {
                    // التحقق من البيانات
                    if (empty($item['product_id']) || empty($item['quantity']) || empty($item['unit_cost'])) {
                        continue;
                    }

                    if ($item['quantity'] <= 0 || $item['unit_cost'] < 0) {
                        continue;
                    }

                    // التحقق من عدم وجود رصيد سابق
                    if ($this->openingStockRepository->findByProductAndDate($item['product_id'], $openingDate)) {
                        continue;
                    }

                    $openingStockData = [
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['quantity'] * $item['unit_cost'],
                        'opening_date' => $openingDate,
                        'notes' => $item['notes'] ?? null,
                        'is_active' => true,
                        'created_by' => $userId
                    ];

                    $openingStock = $this->create($openingStockData);
                    $createdCount++;
                } catch (\Exception $e) {
                    $errors[] = "خطأ في الصنف ID {$item['product_id']}: " . $e->getMessage();
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
            throw new Exception(__('Failed to create opening stocks: ') . $e->getMessage());
        }
    }

    public function bulkUpdateOpeningStocks(array $data): array
    {
        try {
            DB::beginTransaction();

            $updatedCount = 0;
            $errors = [];
            $userId = Auth::id();

            foreach ($data as $item) {
                try {
                    if (empty($item['id']) || empty($item['quantity']) || empty($item['unit_cost'])) {
                        continue;
                    }

                    if ($item['quantity'] <= 0 || $item['unit_cost'] < 0) {
                        continue;
                    }

                    $updateData = [
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_cost'],
                        'total_cost' => $item['quantity'] * $item['unit_cost'],
                        'notes' => $item['notes'] ?? null,
                        'updated_by' => $userId
                    ];

                    $this->update($item['id'], $updateData);
                    $updatedCount++;
                } catch (\Exception $e) {
                    $errors[] = "خطأ في تحديث السجل ID {$item['id']}: " . $e->getMessage();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'updated_count' => $updatedCount,
                'errors' => $errors
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw new Exception(__('Failed to update opening stocks: ') . $e->getMessage());
        }
    }

    public function createOpeningStock(array $data): Model
    {
        if ($this->openingStockRepository->findByProductAndDate($data['product_id'], $data['opening_date'])) {
            throw new Exception(__('Opening stock already exists for this product on this date'));
        }

        if ($data['quantity'] < 0) {
            throw new Exception(__('Quantity cannot be negative'));
        }

        if ($data['unit_cost'] < 0) {
            throw new Exception(__('Unit cost cannot be negative'));
        }

        $data['created_by'] = Auth::id();
        $data['is_active'] = $data['is_active'] ?? true;

        return $this->create($data);
    }

    public function updateOpeningStock(int $id, array $data): Model
    {
        $openingStock = $this->find($id);
        if (!$openingStock) {
            throw new Exception(__('Opening stock not found'));
        }

        $existing = $this->openingStockRepository->findByProductAndDate($data['product_id'], $data['opening_date']);
        if ($existing && $existing->id !== $id) {
            throw new Exception(__('Opening stock already exists for this product on this date'));
        }

        if ($data['quantity'] < 0) {
            throw new Exception(__('Quantity cannot be negative'));
        }

        if ($data['unit_cost'] < 0) {
            throw new Exception(__('Unit cost cannot be negative'));
        }

        $data['updated_by'] = Auth::id();

        $updated = $this->update($id, $data);
        if (!$updated) {
            throw new Exception(__('Failed to update opening stock'));
        }

        return $updated;
    }

    public function deleteOpeningStock(int $id): bool
    {
        $openingStock = $this->find($id);
        if (!$openingStock) {
            throw new Exception(__('Opening stock not found'));
        }

        return $this->delete($id);
    }

    public function toggleStatus(int $id): Model
    {
        $openingStock = $this->find($id);
        if (!$openingStock) {
            throw new Exception(__('Opening stock not found'));
        }

        $newStatus = !$openingStock->is_active;

        $updated = $this->update($id, ['is_active' => $newStatus, 'updated_by' => Auth::id()]);
        if (!$updated) {
            throw new Exception(__('Failed to update opening stock status'));
        }

        return $updated;
    }

    public function getProductOpeningStock(int $productId)
    {
        return $this->openingStockRepository->findByProductId($productId);
    }

    public function getOpeningStocksByDateRange(string $fromDate, string $toDate): Collection
    {
        return $this->openingStockRepository->getByDateRange($fromDate, $toDate);
    }

    public function findOpeningStockOrFail(int $id): Model
    {
        return $this->findOrFail($id);
    }
}
