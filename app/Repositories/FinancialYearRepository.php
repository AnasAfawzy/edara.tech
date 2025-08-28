<?php

namespace App\Repositories;

use App\Models\FinancialYear;
use App\Repositories\Interfaces\FinancialYearRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FinancialYearRepository extends BaseRepository implements FinancialYearRepositoryInterface
{
    public function __construct(FinancialYear $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return $this->model->orderBy('start_date', 'desc')->get();
    }

    public function find(int $id): Model
    {
        return $this->model->find($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): ?Model
    {
        $financialYear = $this->find($id);
        if (!$financialYear) return null;
        $financialYear->update($data);
        return $financialYear;
    }

    public function delete(int $id): bool
    {
        $financialYear = $this->find($id);
        if (!$financialYear) return false;
        return (bool) $financialYear->delete();
    }

    public function getActive(): ?FinancialYear
    {
        return $this->model->where('is_active', true)->first();
    }

    public function getByDate(string $date): ?FinancialYear
    {
        $date = Carbon::parse($date);
        return $this->model->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    public function activate(int $id): bool
    {
        // إلغاء تفعيل جميع السنوات المالية
        $this->model->where('is_active', true)->update(['is_active' => false]);

        // تفعيل السنة المالية المحددة
        $result = $this->update($id, ['is_active' => true]);
        return $result !== null;
    }

    public function close(int $id): bool
    {
        $result = $this->update($id, ['is_closed' => true, 'is_active' => false]);
        return $result !== null;
    }

    public function checkOverlapping(string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $query = $this->model->where(function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate])
                ->orWhereBetween('end_date', [$startDate, $endDate])
                ->orWhere(function ($q) use ($startDate, $endDate) {
                    $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                });
        });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function searchFinancialYears($search = '', $perPage = 10)
    {
        return $this->model
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            })
            ->orderBy('start_date', 'desc')
            ->paginate($perPage);
    }
}
