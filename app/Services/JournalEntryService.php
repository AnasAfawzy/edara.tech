<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Events\JournalEntryPosted;
use App\Models\JournalEntryDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\FinancialYearHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Interfaces\JournalEntryRepositoryInterface;

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
        $data['source_type'] = $sourceType;
        $data['source_id'] = $sourceId;
        $data['status'] = $status;
        // إضافة هذا السطر لتوليد entry_number
        $data['entry_number'] = $this->generateEntryNumber($sourceType);
        $this->validateJournalEntryData($data, $details, $sourceType);

        return DB::transaction(function () use ($data, $details) {
            $journalEntry = JournalEntry::create($data);
            $this->createDetails($journalEntry, $details);
            $journalEntry->calculateTotalsFromDetails();
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

        if ($journalEntry->status === JournalEntry::STATUS_POSTED && $journalEntry->source_type !== JournalEntry::SOURCE_OPENING_BALANCE) {
            throw new \Exception(__('Cannot modify a posted journal entry.'));
        }

        $data['source_type'] = $journalEntry->source_type;
        $this->validateJournalEntryData($data, $details, $journalEntry->source_type);

        return DB::transaction(function () use ($journalEntry, $data, $details, $status) {
            $journalEntry->update($data);

            if ($status) {
                $journalEntry->update(['status' => $status]);
            }

            $journalEntry->details()->delete();
            $this->createDetails($journalEntry, $details);
            $journalEntry->calculateTotalsFromDetails();

            return $journalEntry->load(['details.account', 'currency', 'financialYear']);
        });
    }

    /**
     * حذف قيد
     */
    public function deleteEntry(int $id): bool
    {
        $journalEntry = JournalEntry::findOrFail($id);

        if ($journalEntry->status === JournalEntry::STATUS_POSTED) {
            throw new \Exception(__('Cannot delete a posted journal entry.'));
        }

        return DB::transaction(function () use ($journalEntry) {
            $journalEntry->details()->delete();
            return $journalEntry->delete();
        });
    }

    /**
     * الحصول على قيد مع التفاصيل
     */
    public function getEntryWithDetails(int $id): ?JournalEntry
    {
        return JournalEntry::with(['details.account', 'currency', 'financialYear'])->find($id);
    }

    /**
     * الحصول على جميع القيود
     */
    public function getAllEntries(int $perPage = 25): LengthAwarePaginator
    {
        return JournalEntry::with(['currency', 'financialYear'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * تقديم قيد للاعتماد
     */
    public function submitForApproval(int $id): JournalEntry
    {
        Log::info("JournalEntryService: Starting submission for entry {$id}");

        try {
            return DB::transaction(function () use ($id) {
                $updateResult = DB::table('journal_entries')
                    ->where('id', $id)
                    ->where('status', JournalEntry::STATUS_DRAFT)
                    ->update([
                        'status' => JournalEntry::STATUS_PENDING,
                        'updated_by' => Auth::id(),
                        'updated_at' => now()
                    ]);

                if ($updateResult === 0) {
                    throw new \Exception('Failed to submit journal entry - entry not found or not in draft status');
                }

                $journalEntry = JournalEntry::with(['details.account'])->find($id);

                $validationErrors = $journalEntry->validate();
                if (!empty($validationErrors)) {
                    DB::table('journal_entries')
                        ->where('id', $id)
                        ->update(['status' => JournalEntry::STATUS_DRAFT]);

                    throw new \Exception(implode(', ', $validationErrors));
                }

                Log::info("JournalEntryService: Entry {$id} submitted successfully");
                return $journalEntry;
            });
        } catch (\Exception $e) {
            Log::error("JournalEntryService: Error submitting entry {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * اعتماد قيد
     */
    public function approveEntry(int $id): JournalEntry
    {
        Log::info("JournalEntryService: Starting approval for entry {$id}");

        try {
            return DB::transaction(function () use ($id) {
                $updateResult = DB::table('journal_entries')
                    ->where('id', $id)
                    ->where('status', JournalEntry::STATUS_PENDING)
                    ->update([
                        'status' => JournalEntry::STATUS_APPROVED,
                        'updated_by' => Auth::id(),
                        'updated_at' => now()
                    ]);

                if ($updateResult === 0) {
                    throw new \Exception('Failed to approve journal entry - entry not found or not in pending status');
                }

                $journalEntry = JournalEntry::with(['details.account'])->find($id);
                Log::info("JournalEntryService: Entry {$id} approved successfully");

                return $journalEntry;
            });
        } catch (\Exception $e) {
            Log::error("JournalEntryService: Error approving entry {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * رفض قيد
     */
    public function rejectEntry(int $id): JournalEntry
    {
        return DB::transaction(function () use ($id) {
            $updateResult = DB::table('journal_entries')
                ->where('id', $id)
                ->where('status', JournalEntry::STATUS_PENDING)
                ->update([
                    'status' => JournalEntry::STATUS_DRAFT,
                    'updated_by' => Auth::id(),
                    'updated_at' => now()
                ]);

            if ($updateResult === 0) {
                throw new \Exception('Failed to reject journal entry - entry not found or not in pending status');
            }

            return JournalEntry::with(['details.account'])->find($id);
        });
    }

    /**
     * ترحيل قيد
     */
    public function postEntry(int $id): JournalEntry
    {
        Log::info("JournalEntryService: Starting posting for entry {$id}");

        try {
            return DB::transaction(function () use ($id) {
                // التحديث باستخدام raw query
                $updateResult = DB::table('journal_entries')
                    ->where('id', $id)
                    ->where('status', JournalEntry::STATUS_APPROVED)
                    ->update([
                        'status' => JournalEntry::STATUS_POSTED,
                        'updated_by' => Auth::id(),
                        'updated_at' => now()
                    ]);

                if ($updateResult === 0) {
                    throw new \Exception('Failed to post journal entry - entry not found or not approved');
                }

                // إعادة تحميل القيد مع كل التفاصيل والحسابات
                $journalEntry = JournalEntry::with([
                    'details.account',
                    'currency',
                    'financialYear'
                ])->find($id);

                if (!$journalEntry) {
                    throw new \Exception('Journal entry not found after update');
                }

                Log::info("JournalEntryService: Entry {$id} loaded with " . $journalEntry->details->count() . " details");

                // إطلاق حدث الترحيل
                event(new JournalEntryPosted($journalEntry));

                Log::info("JournalEntryService: Entry {$id} posted successfully and event dispatched");

                return $journalEntry;
            });
        } catch (\Exception $e) {
            Log::error("JournalEntryService: Error posting entry {$id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * نسخ قيد
     */
    public function duplicateEntry(int $id): JournalEntry
    {
        $originalEntry = $this->getEntryWithDetails($id);

        if (!$originalEntry) {
            throw new \Exception(__('Original entry not found.'));
        }

        $duplicatedData = $originalEntry->only([
            'description',
            'currency_id',
            'financial_year_id'
        ]);
        $duplicatedData['entry_date'] = Carbon::today();
        $duplicatedData['description'] = __('Copy of') . ' ' . $originalEntry->description;

        $duplicatedDetails = $originalEntry->details->map(function ($detail) {
            return $detail->only(['account_id', 'statement', 'debit', 'credit', 'cost_center_id']);
        })->toArray();

        return $this->createEntry($duplicatedData, $duplicatedDetails);
    }

    /**
     * التحقق من صحة بيانات القيد
     */
    protected function validateJournalEntryData(array $data, array $details, string $sourceType = 'manual'): void
    {
        if (empty($details)) {
            throw new \Exception(__('Journal entry must have at least one detail.'));
        }

        $totals = $this->calculateTotals($details);

        if (abs($totals['total_debit'] - $totals['total_credit']) >= 0.01) {
            throw new \Exception(__('Journal entry is not balanced.'));
        }

        if ($totals['total_debit'] == 0 && $totals['total_credit'] == 0) {
            throw new \Exception(__('Journal entry cannot have zero amounts.'));
        }
    }

    /**
     * حساب المجاميع
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
                'statement' => $detail['statement'] ?? '',
                'debit' => $detail['debit'] ?? 0,
                'credit' => $detail['credit'] ?? 0,
                'cost_center_id' => $detail['cost_center_id'] ?? null,
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
            default => 'JE'
        };

        $year = date('Y');
        $lastEntry = JournalEntry::where('source_type', $sourceType)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastEntry ?
            (int) substr($lastEntry->entry_number, -6) + 1 :
            1;

        return $prefix . '-' . $year . '-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    /**
     * توليد الرقم المرجعي
     */
    protected function generateReferenceNumber(string $sourceType = 'manual'): string
    {
        return $this->generateEntryNumber($sourceType);
    }
}
