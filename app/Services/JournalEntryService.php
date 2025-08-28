<?php

namespace App\Services;

use App\Repositories\Interfaces\JournalEntryRepositoryInterface;
use App\Helpers\FinancialYearHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class JournalEntryService
{
    protected JournalEntryRepositoryInterface $repository;

    public function __construct(JournalEntryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEntries(): Collection
    {
        return $this->repository->allWithDetails();
    }

    public function getEntryWithDetails($id)
    {
        return $this->repository->findWithDetails($id);
    }

    public function searchEntries(array $filters): LengthAwarePaginator
    {
        return $this->repository->searchEntries($filters);
    }

    public function createEntry(array $data, array $details, ?string $sourceType = null, ?int $sourceId = null)
    {
        // التحقق من صحة البيانات أولاً
        $this->validateJournalEntryData($data, $details);

        // إنشاء رقم القيد إذا لم يكن موجوداً
        if (!isset($data['entry_number'])) {
            $data['entry_number'] = $this->generateEntryNumber();
        }

        // تحديد السنة المالية
        if (!isset($data['financial_year_id'])) {
            $data['financial_year_id'] = FinancialYearHelper::assignFinancialYear($data['entry_date']);

            if (!$data['financial_year_id']) {
                throw new \InvalidArgumentException('No active financial year found for the given date');
            }
        }

        // تحديد نوع ومصدر القيد
        $data['source_type'] = $sourceType ?? 'manual';
        $data['source_id'] = $sourceId ?? 0;


        // تنظيف بيانات التفاصيل
        $cleanDetails = $this->cleanDetailsData($details);

        return $this->repository->createWithDetails($data, $cleanDetails);
    }

    public function updateEntry($id, array $data, array $details)
    {
        // التحقق من إمكانية التعديل
        if (!$this->canEditEntry($id)) {
            throw new \InvalidArgumentException('Cannot edit this journal entry');
        }

        // التحقق من صحة البيانات
        $this->validateJournalEntryData($data, $details);

        // تحديد السنة المالية إذا تم تغيير التاريخ
        if (isset($data['entry_date'])) {
            $data['financial_year_id'] = FinancialYearHelper::assignFinancialYear($data['entry_date']);

            if (!$data['financial_year_id']) {
                throw new \InvalidArgumentException('No active financial year found for the given date');
            }
        }
        // الحفاظ على source_type و source_id الحاليين إذا لم يتم تمريرهم
        $existingEntry = $this->repository->find($id);
        if ($existingEntry) {
            $data['source_type'] = $data['source_type'] ?? $existingEntry->source_type ?? 'manual';
            $data['source_id'] = $data['source_id'] ?? $existingEntry->source_id ?? 0;
        } else {
            $data['source_type'] = $data['source_type'] ?? 'manual';
            $data['source_id'] = $data['source_id'] ?? 0;
        }
        // تنظيف بيانات التفاصيل
        $cleanDetails = $this->cleanDetailsData($details);

        return $this->repository->updateWithDetails($id, $data, $cleanDetails);
    }

    public function deleteEntry($id): bool
    {
        // التحقق من إمكانية الحذف
        if (!$this->canDeleteEntry($id)) {
            throw new \InvalidArgumentException('Cannot delete this journal entry');
        }

        $entry = $this->repository->find($id);

        if (!$entry) {
            return false;
        }

        return $this->repository->delete($id);
    }

    protected function generateEntryNumber(): string
    {
        return $this->repository->getNextEntryNumber();
    }

    protected function cleanDetailsData(array $details): array
    {
        $cleanDetails = [];

        foreach ($details as $detail) {
            // تجاهل الصفوف الفارغة
            if (empty($detail['account_id'])) {
                continue;
            }

            $cleanDetail = [
                'account_id' => $detail['account_id'],
                'debit' => floatval($detail['debit'] ?? 0),
                'credit' => floatval($detail['credit'] ?? 0),
                'cost_center_id' => !empty($detail['cost_center_id']) ? $detail['cost_center_id'] : null,
                'statement' => $detail['statement'] ?? null, 
            ];

            // التأكد من وجود مبلغ
            if ($cleanDetail['debit'] > 0 || $cleanDetail['credit'] > 0) {
                $cleanDetails[] = $cleanDetail;
            }
        }

        return $cleanDetails;
    }

    protected function validateJournalEntryData(array $data, array $details): void
    {
        // التحقق من البيانات الأساسية
        if (empty($data['entry_date'])) {
            throw new \InvalidArgumentException('Date is required');
        }

        if (empty($data['description'])) {
            throw new \InvalidArgumentException('Description is required');
        }

        if (empty($data['currency_id'])) {
            throw new \InvalidArgumentException('Currency is required');
        }

        // تنظيف وفحص التفاصيل
        $cleanDetails = $this->cleanDetailsData($details);

        // التحقق من وجود تفاصيل كافية
        if (empty($cleanDetails) || count($cleanDetails) < 2) {
            throw new \InvalidArgumentException('Journal entry must have at least 2 details');
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($cleanDetails as $detail) {
            $debit = $detail['debit'];
            $credit = $detail['credit'];

            // التحقق من عدم وجود مبلغ في المدين والدائن معاً
            if ($debit > 0 && $credit > 0) {
                throw new \InvalidArgumentException('Detail cannot have both debit and credit amounts');
            }

            // التحقق من وجود مبلغ
            if ($debit === 0.0 && $credit === 0.0) {
                throw new \InvalidArgumentException('Detail must have either debit or credit amount');
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        // التحقق من توازن القيد
        if (abs($totalDebit - $totalCredit) > 0.01) {
            throw new \InvalidArgumentException("Journal entry must be balanced. Debit: {$totalDebit}, Credit: {$totalCredit}");
        }

        // التحقق من أن المجموع أكبر من صفر
        if ($totalDebit === 0.0) {
            throw new \InvalidArgumentException('Journal entry total amount must be greater than zero');
        }
    }

    public function canEditEntry($id): bool
    {
        $entry = $this->repository->find($id);

        if (!$entry) {
            return false;
        }

        // التحقق من أن السنة المالية لم يتم إغلاقها
        if ($entry->financialYear && $entry->financialYear->is_closed) {
            return false;
        }

        return true;
    }

    public function canDeleteEntry($id): bool
    {
        return $this->canEditEntry($id);
    }

    public function getEntriesByFinancialYear(int $financialYearId): Collection
    {
        return $this->repository->getByFinancialYear($financialYearId);
    }

    public function getEntriesByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->repository->getByDateRange($startDate, $endDate);
    }

    public function getTotalDebitCredit(): array
    {
        $entries = $this->repository->allWithDetails();
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($entries as $entry) {
            $totalDebit += $entry->details->sum('debit');
            $totalCredit += $entry->details->sum('credit');
        }

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balance' => $totalDebit - $totalCredit
        ];
    }
}
