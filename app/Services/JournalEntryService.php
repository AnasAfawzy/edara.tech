<?php

namespace App\Services;

use App\Events\JournalEntryPosted;
use App\Repositories\Interfaces\JournalEntryRepositoryInterface;
use App\Helpers\FinancialYearHelper;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetail;
use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class JournalEntryService
{
    protected $journalEntryRepository;

    public function __construct(JournalEntryRepositoryInterface $journalEntryRepository = null)
    {
        $this->journalEntryRepository = $journalEntryRepository;
    }

    /**
     * إنشاء قيد جديد
     */
    public function createEntry(
        array $data,
        array $details,
        string $sourceType = 'manual',
        int $sourceId = 0,
        string $status = JournalEntry::STATUS_PENDING
    ): JournalEntry {
        // إضافة البيانات الأساسية
        $data['source_type'] = $sourceType;
        $data['source_id'] = $sourceId;
        $data['status'] = $status;

        // التحقق من صحة البيانات
        $this->validateJournalEntryData($data, $details, $sourceType);

        return DB::transaction(function () use ($data, $details) {
            // تحديد السنة المالية إذا لم تكن محددة
            if (!isset($data['financial_year_id']) || !$data['financial_year_id']) {
                $data['financial_year_id'] = FinancialYearHelper::assignFinancialYear($data['entry_date']);
            }

            // توليد رقم القيد
            $data['entry_number'] = $this->generateEntryNumber($data['source_type']);

            // توليد الرقم المرجعي إذا لم يكن موجود
            if (!isset($data['reference_number']) || !$data['reference_number']) {
                $data['reference_number'] = $this->generateReferenceNumber($data['source_type']);
            }

            // حساب المجاميع
            $totals = $this->calculateTotals($details);
            $data['total_debit'] = $totals['total_debit'];
            $data['total_credit'] = $totals['total_credit'];

            // تحديد المستخدم المنشئ
            if (!isset($data['created_by']) && Auth::check()) {
                $data['created_by'] = Auth::id();
                $data['updated_by'] = Auth::id();
            }

            // إنشاء القيد
            $journalEntry = JournalEntry::create($data);

            // إنشاء التفاصيل
            $this->createDetails($journalEntry, $details);

            // إطلاق الأحداث إذا كان القيد مرحل
            if ($data['status'] === JournalEntry::STATUS_POSTED) {
                event(new JournalEntryPosted($journalEntry));
            }

            return $journalEntry->load(['details.account', 'currency', 'financialYear']);
        });
    }

    /**
     * تحديث قيد موجود
     */
    public function updateEntry(
        int $id,
        array $data,
        array $details,
        string $status = null
    ): JournalEntry {
        $journalEntry = JournalEntry::findOrFail($id);

        // التحقق من إمكانية التعديل
        if ($journalEntry->status === JournalEntry::STATUS_POSTED && $journalEntry->source_type !== JournalEntry::SOURCE_OPENING_BALANCE) {
            throw new \Exception(__('Cannot update a posted journal entry'));
        }

        // إضافة نوع المصدر للبيانات
        $data['source_type'] = $journalEntry->source_type;

        // التحقق من صحة البيانات
        $this->validateJournalEntryData($data, $details, $journalEntry->source_type);

        return DB::transaction(function () use ($journalEntry, $data, $details, $status) {
            // تحديث الحالة إذا تم تمريرها
            if ($status) {
                $data['status'] = $status;
            }

            // حساب المجاميع
            $totals = $this->calculateTotals($details);
            $data['total_debit'] = $totals['total_debit'];
            $data['total_credit'] = $totals['total_credit'];

            // تحديد المستخدم المحدث
            if (Auth::check()) {
                $data['updated_by'] = Auth::id();
            }

            // تحديث القيد
            $journalEntry->update($data);

            // حذف التفاصيل القديمة
            $journalEntry->details()->delete();

            // إنشاء التفاصيل الجديدة
            $this->createDetails($journalEntry, $details);

            // إطلاق الأحداث إذا كان القيد مرحل
            if (isset($data['status']) && $data['status'] === JournalEntry::STATUS_POSTED) {
                event(new JournalEntryPosted($journalEntry));
            }

            return $journalEntry->load(['details.account', 'currency', 'financialYear']);
        });
    }

    /**
     * حذف قيد
     */
    public function deleteEntry(int $id): bool
    {
        $journalEntry = JournalEntry::findOrFail($id);

        // التحقق من إمكانية الحذف
        if ($journalEntry->status === JournalEntry::STATUS_POSTED) {
            throw new \Exception(__('Cannot delete a posted journal entry'));
        }

        return DB::transaction(function () use ($journalEntry) {
            // حذف التفاصيل
            $journalEntry->details()->delete();

            // حذف القيد
            return $journalEntry->delete();
        });
    }

    /**
     * الحصول على قيد مع التفاصيل
     */
    public function getEntryWithDetails(int $id): ?JournalEntry
    {
        return JournalEntry::with([
            'details.account',
            'details.costCenter',
            'currency',
            'financialYear',
            'creator',
            'updater',
            'reversingEntry',
            'originalEntry'
        ])->find($id);
    }

    /**
     * الحصول على جميع القيود مع pagination
     */
    public function getAllEntries(int $perPage = 25): LengthAwarePaginator
    {
        return JournalEntry::with([
            'currency',
            'financialYear',
            'creator',
            'updater'
        ])
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * تقديم قيد للاعتماد
     */
    public function submitForApproval(int $id): JournalEntry
    {
        $journalEntry = JournalEntry::findOrFail($id);

        if ($journalEntry->status !== JournalEntry::STATUS_DRAFT) {
            throw new \Exception(__('Only draft entries can be submitted for approval'));
        }

        // التحقق من صحة القيد
        $validationErrors = $journalEntry->validate();
        if (!empty($validationErrors)) {
            throw new \Exception(implode(', ', $validationErrors));
        }

        $journalEntry->update(['status' => JournalEntry::STATUS_PENDING]);

        return $journalEntry;
    }

    /**
     * اعتماد قيد
     */
    public function approveEntry(int $id): JournalEntry
    {
        $journalEntry = JournalEntry::findOrFail($id);

        if ($journalEntry->status !== JournalEntry::STATUS_PENDING) {
            throw new \Exception(__('Only pending entries can be approved'));
        }

        $journalEntry->update(['status' => JournalEntry::STATUS_APPROVED]);

        return $journalEntry;
    }

    /**
     * رفض قيد
     */
    public function rejectEntry(int $id): JournalEntry
    {
        $journalEntry = JournalEntry::findOrFail($id);

        if (!in_array($journalEntry->status, [JournalEntry::STATUS_PENDING, JournalEntry::STATUS_APPROVED])) {
            throw new \Exception(__('Only pending or approved entries can be rejected'));
        }

        $journalEntry->update(['status' => JournalEntry::STATUS_DRAFT]);

        return $journalEntry;
    }

    /**
     * ترحيل قيد
     */
    public function postEntry(int $id): JournalEntry
    {
        $journalEntry = JournalEntry::findOrFail($id);

        if ($journalEntry->status !== JournalEntry::STATUS_APPROVED) {
            throw new \Exception(__('Only approved entries can be posted'));
        }

        // التحقق من صحة القيد
        $validationErrors = $journalEntry->validate();
        if (!empty($validationErrors)) {
            throw new \Exception(implode(', ', $validationErrors));
        }

        $journalEntry->update(['status' => JournalEntry::STATUS_POSTED]);

        // إطلاق حدث الترحيل
        event(new JournalEntryPosted($journalEntry));

        return $journalEntry;
    }

    /**
     * نسخ قيد
     */
    public function duplicateEntry(int $id): JournalEntry
    {
        $originalEntry = $this->getEntryWithDetails($id);

        if (!$originalEntry) {
            throw new \Exception(__('Journal entry not found'));
        }

        // إنشاء قيد جديد غير محفوظ
        $newEntry = $originalEntry->replicate();
        $newEntry->entry_date = Carbon::now();
        $newEntry->description = __('Copy of') . ' ' . $originalEntry->description;
        $newEntry->entry_number = null;
        $newEntry->reference_number = null;
        $newEntry->status = JournalEntry::STATUS_DRAFT;
        $newEntry->created_by = null;
        $newEntry->updated_by = null;
        $newEntry->reverses_entry_id = null;
        $newEntry->reversed_by_entry_id = null;

        // نسخ التفاصيل
        $newDetails = $originalEntry->details->map(function ($detail) {
            $newDetail = $detail->replicate();
            $newDetail->journal_entry_id = null;
            return $newDetail;
        });

        $newEntry->setRelation('details', $newDetails);

        return $newEntry;
    }

    /**
     * التحقق من صحة بيانات القيد
     */
    protected function validateJournalEntryData(array $data, array $details, string $sourceType = 'manual'): void
    {
        if (empty($details)) {
            throw new \InvalidArgumentException(__('Journal entry must have at least one detail.'));
        }

        // التحقق من الحد الأدنى للتفاصيل حسب نوع القيد
        if ($sourceType === JournalEntry::SOURCE_OPENING_BALANCE) {
            // القيود الافتتاحية تحتاج تفصيل واحد فقط
            if (count($details) < 1) {
                throw new \InvalidArgumentException(__('Opening balance entry must have at least one detail.'));
            }
        } else {
            // القيود العادية تحتاج تفصيلين على الأقل
            if (count($details) < 2) {
                throw new \InvalidArgumentException(__('Journal entry must have at least 2 details.'));
            }
        }

        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($details as $detail) {
            if (!isset($detail['account_id']) || !is_numeric($detail['account_id'])) {
                throw new \InvalidArgumentException(__('Each detail must have a valid account_id.'));
            }

            $debit = floatval($detail['debit'] ?? 0);
            $credit = floatval($detail['credit'] ?? 0);

            if ($debit < 0 || $credit < 0) {
                throw new \InvalidArgumentException(__('Debit and credit amounts must be non-negative.'));
            }

            if ($debit > 0 && $credit > 0) {
                throw new \InvalidArgumentException(__('Each detail must have either debit or credit, not both.'));
            }

            if ($debit == 0 && $credit == 0) {
                throw new \InvalidArgumentException(__('Each detail must have either a debit or credit amount.'));
            }

            $totalDebit += $debit;
            $totalCredit += $credit;
        }

        // التحقق من التوازن حسب نوع القيد
        if ($sourceType !== JournalEntry::SOURCE_OPENING_BALANCE) {
            // القيود العادية يجب أن تكون متوازنة
            if (abs($totalDebit - $totalCredit) > 0.01) {
                throw new \InvalidArgumentException(__("Journal entry must be balanced. Debit: :debit, Credit: :credit", [
                    'debit' => $totalDebit,
                    'credit' => $totalCredit
                ]));
            }
        }
        // القيود الافتتاحية لا تحتاج للتوازن

        if (!isset($data['entry_date'])) {
            throw new \InvalidArgumentException(__('Entry date is required.'));
        }

        if (!isset($data['description']) || trim($data['description']) === '') {
            throw new \InvalidArgumentException(__('Description is required.'));
        }

        if (!isset($data['currency_id']) || !is_numeric($data['currency_id'])) {
            throw new \InvalidArgumentException(__('Currency ID is required.'));
        }
    }

    /**
     * حساب مجاميع المدين والدائن
     */
    protected function calculateTotals(array $details): array
    {
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($details as $detail) {
            $totalDebit += floatval($detail['debit'] ?? 0);
            $totalCredit += floatval($detail['credit'] ?? 0);
        }

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit
        ];
    }

    /**
     * إنشاء تفاصيل القيد
     */
    protected function createDetails(JournalEntry $journalEntry, array $details): void
    {
        foreach ($details as $detail) {
            JournalEntryDetail::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $detail['account_id'],
                'cost_center_id' => $detail['cost_center_id'] ?? null,
                'debit' => floatval($detail['debit'] ?? 0),
                'credit' => floatval($detail['credit'] ?? 0),
                'statement' => $detail['statement'] ?? null,
            ]);
        }
    }

    /**
     * توليد رقم القيد
     */
    protected function generateEntryNumber(string $sourceType = 'manual'): string
    {
        $prefix = match ($sourceType) {
            JournalEntry::SOURCE_OPENING_BALANCE => 'OB',
            JournalEntry::SOURCE_MANUAL => 'JE',
            JournalEntry::SOURCE_INVOICE => 'INV',
            JournalEntry::SOURCE_PAYMENT => 'PAY',
            JournalEntry::SOURCE_RECEIPT => 'REC',
            JournalEntry::SOURCE_SYSTEM => 'SYS',
            default => 'JE'
        };

        $year = date('Y');
        $lastEntry = JournalEntry::where('source_type', $sourceType)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEntry ?
            (int) substr($lastEntry->entry_number, -4) + 1 :
            1;

        return $prefix . '-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * توليد رقم مرجعي
     */
    protected function generateReferenceNumber(string $sourceType = 'manual'): string
    {
        $prefix = match ($sourceType) {
            JournalEntry::SOURCE_OPENING_BALANCE => 'OB-REF',
            JournalEntry::SOURCE_MANUAL => 'JE-REF',
            JournalEntry::SOURCE_INVOICE => 'INV-REF',
            JournalEntry::SOURCE_PAYMENT => 'PAY-REF',
            JournalEntry::SOURCE_RECEIPT => 'REC-REF',
            JournalEntry::SOURCE_SYSTEM => 'SYS-REF',
            default => 'JE-REF'
        };

        $year = date('Y');
        $lastEntry = JournalEntry::where('source_type', $sourceType)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEntry ?
            (int) substr($lastEntry->reference_number, -4) + 1 :
            1;

        return $prefix . '-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
