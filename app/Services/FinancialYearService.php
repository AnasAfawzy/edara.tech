<?php

namespace App\Services;

use App\Repositories\Interfaces\FinancialYearRepositoryInterface;
use App\Models\FinancialYear;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class FinancialYearService
{
    protected $financialYearRepository;

    public function __construct(FinancialYearRepositoryInterface $financialYearRepository)
    {
        $this->financialYearRepository = $financialYearRepository;
    }

    public function getAllFinancialYears(): Collection
    {
        return $this->financialYearRepository->all();
    }

    public function getFinancialYear(int $id): ?FinancialYear
    {
        return $this->financialYearRepository->find($id);
    }

    public function createFinancialYear(array $data): array
    {
        // التحقق من صحة البيانات
        $validation = $this->validateFinancialYearData($data);
        if (!$validation['success']) {
            return $validation;
        }

        // التحقق من التداخل
        if ($this->financialYearRepository->checkOverlapping($data['start_date'], $data['end_date'])) {
            return [
                'success' => false,
                'message' => 'تواريخ السنة المالية تتداخل مع سنة مالية موجودة'
            ];
        }

        $financialYear = $this->financialYearRepository->create($data);

        return [
            'success' => true,
            'data' => $financialYear,
            'message' => 'تم إنشاء السنة المالية بنجاح'
        ];
    }

    public function updateFinancialYear(int $id, array $data): array
    {
        $financialYear = $this->financialYearRepository->find($id);
        if (!$financialYear) {
            return [
                'success' => false,
                'message' => 'السنة المالية غير موجودة'
            ];
        }

        // التحقق من صحة البيانات
        $validation = $this->validateFinancialYearData($data);
        if (!$validation['success']) {
            return $validation;
        }

        // التحقق من التداخل (باستثناء السنة المالية الحالية)
        if ($this->financialYearRepository->checkOverlapping($data['start_date'], $data['end_date'], $id)) {
            return [
                'success' => false,
                'message' => 'تواريخ السنة المالية تتداخل مع سنة مالية موجودة'
            ];
        }

        $updated = $this->financialYearRepository->update($id, $data);

        return [
            'success' => $updated,
            'message' => $updated ? 'تم تحديث السنة المالية بنجاح' : 'فشل في تحديث السنة المالية'
        ];
    }

    public function activateFinancialYear(int $id): array
    {
        $financialYear = $this->financialYearRepository->find($id);
        if (!$financialYear) {
            return [
                'success' => false,
                'message' => 'السنة المالية غير موجودة'
            ];
        }

        if ($financialYear->is_closed) {
            return [
                'success' => false,
                'message' => 'لا يمكن تفعيل سنة مالية مغلقة'
            ];
        }

        $activated = $this->financialYearRepository->activate($id);

        return [
            'success' => $activated,
            'message' => $activated ? 'تم تفعيل السنة المالية بنجاح' : 'فشل في تفعيل السنة المالية'
        ];
    }

    public function closeFinancialYear(int $id): array
    {
        $financialYear = $this->financialYearRepository->find($id);
        if (!$financialYear) {
            return [
                'success' => false,
                'message' => 'السنة المالية غير موجودة'
            ];
        }

        if ($financialYear->is_closed) {
            return [
                'success' => false,
                'message' => 'السنة المالية مغلقة بالفعل'
            ];
        }

        $closed = $this->financialYearRepository->close($id);

        return [
            'success' => $closed,
            'message' => $closed ? 'تم إغلاق السنة المالية بنجاح' : 'فشل في إغلاق السنة المالية'
        ];
    }

    public function getActiveFinancialYear(): ?FinancialYear
    {
        return $this->financialYearRepository->getActive();
    }

    public function getFinancialYearByDate(string $date): ?FinancialYear
    {
        return $this->financialYearRepository->getByDate($date);
    }

    public function assignFinancialYearId($date = null): ?int
    {
        $date = $date ? Carbon::parse($date) : now();

        // محاولة العثور على السنة المالية بناءً على التاريخ
        $financialYear = $this->getFinancialYearByDate($date);

        // إذا لم توجد، استخدم السنة المالية النشطة
        if (!$financialYear) {
            $financialYear = $this->getActiveFinancialYear();
        }

        return $financialYear ? $financialYear->id : null;
    }

    private function validateFinancialYearData(array $data): array
    {
        if (empty($data['name'])) {
            return [
                'success' => false,
                'message' => 'اسم السنة المالية مطلوب'
            ];
        }

        if (empty($data['start_date']) || empty($data['end_date'])) {
            return [
                'success' => false,
                'message' => 'تاريخ البداية والنهاية مطلوبان'
            ];
        }

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lte($startDate)) {
            return [
                'success' => false,
                'message' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية'
            ];
        }

        return ['success' => true];
    }
}
