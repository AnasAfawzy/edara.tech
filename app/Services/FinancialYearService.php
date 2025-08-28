<?php

namespace App\Services;

use App\Repositories\FinancialYearRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialYearService extends BaseService
{
    protected $repository;

    public function __construct(FinancialYearRepository $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }

    public function getAllFinancialYears()
    {
        return $this->repository->all();
    }

    public function getFinancialYear(int $id)
    {
        return $this->repository->find($id);
    }

    public function createFinancialYear(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                // التحقق من التداخل
                if ($this->repository->checkOverlapping($data['start_date'], $data['end_date'])) {
                    throw new \Exception('تواريخ السنة المالية تتداخل مع سنة مالية موجودة');
                }

                $financialYear = $this->repository->create($data);

                if (!$financialYear || !$financialYear->id) {
                    throw new \Exception('فشل في إنشاء السنة المالية');
                }

                return $financialYear;
            });
        } catch (\Exception $e) {
            Log::error("Error creating financial year: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateFinancialYear(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $financialYear = $this->repository->find($id);

                if (!$financialYear) {
                    throw new \Exception('السنة المالية غير موجودة');
                }

                if ($financialYear->is_closed) {
                    throw new \Exception('لا يمكن تعديل سنة مالية مغلقة');
                }

                // التحقق من التداخل (باستثناء السنة المالية الحالية)
                if ($this->repository->checkOverlapping($data['start_date'], $data['end_date'], $id)) {
                    throw new \Exception('تواريخ السنة المالية تتداخل مع سنة مالية موجودة');
                }

                $updatedYear = $this->repository->update($id, $data);

                if (!$updatedYear) {
                    throw new \Exception('فشل في تحديث السنة المالية');
                }

                return $updatedYear;
            });
        } catch (\Exception $e) {
            Log::error("Error updating financial year: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteFinancialYear($id): bool
    {
        $financialYear = $this->repository->find($id);

        if (!$financialYear) {
            throw new \Exception('السنة المالية غير موجودة');
        }

        if ($financialYear->is_active) {
            throw new \Exception('لا يمكن حذف السنة المالية النشطة');
        }

        if ($financialYear->is_closed) {
            throw new \Exception('لا يمكن حذف السنة المالية المغلقة');
        }

        // التحقق من وجود بيانات مرتبطة
        if ($financialYear->journalEntries()->exists()) {
            throw new \Exception('لا يمكن حذف السنة المالية لوجود قيود محاسبية مرتبطة بها');
        }

        return $this->repository->delete($id);
    }

    public function activateFinancialYear(int $id)
    {
        $financialYear = $this->repository->find($id);

        if (!$financialYear) {
            throw new \Exception('السنة المالية غير موجودة');
        }

        if ($financialYear->is_closed) {
            throw new \Exception('لا يمكن تفعيل سنة مالية مغلقة');
        }

        return $this->repository->activate($id);
    }

    public function closeFinancialYear(int $id)
    {
        $financialYear = $this->repository->find($id);

        if (!$financialYear) {
            throw new \Exception('السنة المالية غير موجودة');
        }

        if ($financialYear->is_closed) {
            throw new \Exception('السنة المالية مغلقة بالفعل');
        }

        return $this->repository->close($id);
    }

    public function searchFinancialYears($search = '', $perPage = 10)
    {
        return $this->repository->searchFinancialYears($search, $perPage);
    }
}
