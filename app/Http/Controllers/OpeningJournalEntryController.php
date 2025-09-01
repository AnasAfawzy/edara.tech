<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Currency;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use App\Services\AccountService;
use App\Services\JournalEntryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Helpers\FinancialYearHelper;
use App\Models\OpeningBalance;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Events\JournalEntryPosted;

class OpeningJournalEntryController extends Controller
{
    protected $accountService;
    protected $journalEntryService;

    public function __construct(AccountService $accountService, JournalEntryService $journalEntryService)
    {
        $this->accountService = $accountService;
        $this->journalEntryService = $journalEntryService;
    }

    /**
     * عرض صفحة الأرصدة الافتتاحية
     */
    public function index()
    {
        try {
            // استخدام cache للحسابات
            $accounts = Cache::remember('accounts_tree_opening', 300, function () {
                return $this->buildAccountTreeOptimized();
            });

            // الحصول على السنة المالية النشطة
            $activeFinancialYear = FinancialYearHelper::getActiveFinancialYear();

            $openingJournalEntry = null;
            $openingJournalEntryDetailsMapped = [];

            if ($activeFinancialYear) {
                // تحسين query باستخدام select محدد
                $openingJournalEntry = JournalEntry::select(['id', 'entry_date', 'description', 'total_debit', 'total_credit'])
                    ->with(['details' => function ($query) {
                        $query->select(['id', 'journal_entry_id', 'account_id', 'debit', 'credit', 'statement']);
                    }])
                    ->where('source_type', JournalEntry::SOURCE_OPENING_BALANCE)
                    ->where('financial_year_id', $activeFinancialYear->id)
                    ->latest('id')
                    ->first();

                if ($openingJournalEntry) {
                    // Map details for O(1) lookup in view
                    $openingJournalEntryDetailsMapped = $openingJournalEntry->details->keyBy('account_id');
                }
            }

            // تحسين جلب العملات والمراكز
            $currencies = Cache::remember('currencies_list', 3600, function () {
                return Currency::select(['id', 'name', 'code', 'is_default'])->get();
            });

            $costCenters = Cache::remember('cost_centers_list', 3600, function () {
                return CostCenter::select(['id', 'name', 'code'])->get();
            });

            return view('OpeningJournalEntry.index', compact(
                'accounts',
                'openingJournalEntry',
                'openingJournalEntryDetailsMapped',
                'currencies',
                'costCenters',
                'activeFinancialYear'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading opening journal entry page: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => __('Error loading page. Please try again.')]);
        }
    }

    /**
     * حفظ الأرصدة الافتتاحية - محسن للأداء الفائق مع Event Dispatch
     */
    public function store(Request $request)
    {
        try {
            // زيادة الموارد للعمليات الكبيرة
            set_time_limit(300);
            ini_set('memory_limit', '1024M');

            $activeFinancialYear = FinancialYearHelper::getActiveFinancialYear();

            if (!$activeFinancialYear) {
                return response()->json(['error' => __('لا توجد سنة مالية نشطة.')], 422);
            }

            if ($activeFinancialYear->is_closed) {
                return response()->json(['error' => __('السنة المالية مغلقة.')], 422);
            }

            // الحصول على البيانات
            $details = $request->input('details', []);

            if (!is_array($details) || empty($details)) {
                return response()->json(['error' => __('لم يتم إرسال بيانات الحسابات.')], 422);
            }

            // تحضير البيانات بأقصى سرعة
            $validDetails = $this->prepareValidDetailsUltraFast($details);

            if (empty($validDetails)) {
                return response()->json(['error' => __('يرجى إدخال مبلغ واحد على الأقل.')], 422);
            }

            // الحصول على العملة الافتراضية من الكاش
            $defaultCurrency = $this->getDefaultCurrencyCached();

            if (!$defaultCurrency) {
                return response()->json(['error' => __('لم يتم العثور على العملة الافتراضية.')], 422);
            }

            $entryDate = $activeFinancialYear->start_date;
            $description = __('Opening Balance Entry for Financial Year') . ' ' . $activeFinancialYear->name;

            $journalEntryId = null;

            // حفظ البيانات بأقصى سرعة
            DB::transaction(function () use ($validDetails, $activeFinancialYear, $entryDate, $description, $defaultCurrency, &$journalEntryId) {
                $journalEntryId = $this->saveOpeningJournalEntryUltraFast($validDetails, $activeFinancialYear, $entryDate, $description, $defaultCurrency);
                $this->syncOpeningBalancesUltraFast($validDetails, $activeFinancialYear, $entryDate);
            });

            // إطلاق Event بعد الحفظ الناجح لتسجيل حركات الحسابات
            if ($journalEntryId) {
                $journalEntry = JournalEntry::with(['details.account'])->find($journalEntryId);
                if ($journalEntry) {
                    event(new JournalEntryPosted($journalEntry));
                }
            }

            // مسح الكاش المتعلق بالحسابات
            Cache::forget('accounts_tree_opening');

            return response()->json([
                'success' => true,
                'message' => __('تم حفظ الأرصدة الافتتاحية بنجاح.'),
                'redirect' => route('opening-journal-entry.index')
            ]);
        } catch (\Exception $e) {
            Log::error('Error saving opening balances: ' . $e->getMessage());
            return response()->json(['error' => __('خطأ في الحفظ. يرجى المحاولة مرة أخرى.')], 500);
        }
    }

    /**
     * عرض تقرير الأرصدة الافتتاحية
     */
    public function report(Request $request)
    {
        try {
            $activeFinancialYear = FinancialYearHelper::getActiveFinancialYear();

            if (!$activeFinancialYear) {
                return redirect()->back()
                    ->withErrors(['financial_year' => __('لا توجد سنة مالية نشطة.')]);
            }

            $openingJournalEntry = JournalEntry::select(['id', 'entry_date', 'description', 'total_debit', 'total_credit', 'entry_number'])
                ->with(['details.account:id,code,name'])
                ->where('source_type', JournalEntry::SOURCE_OPENING_BALANCE)
                ->where('financial_year_id', $activeFinancialYear->id)
                ->first();

            $openingBalances = OpeningBalance::select(['id', 'account_id', 'debit_balance', 'credit_balance', 'notes'])
                ->with(['account:id,code,name'])
                ->where('financial_year_id', $activeFinancialYear->id)
                ->orderBy('account_id')
                ->get();

            return view('OpeningJournalEntry.report', compact(
                'openingJournalEntry',
                'openingBalances',
                'activeFinancialYear'
            ));
        } catch (\Exception $e) {
            Log::error('Error loading opening balances report: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => __('Error loading report. Please try again.')]);
        }
    }

    /**
     * تصدير الأرصدة الافتتاحية إلى CSV
     */
    public function export(Request $request)
    {
        try {
            $activeFinancialYear = FinancialYearHelper::getActiveFinancialYear();

            if (!$activeFinancialYear) {
                return redirect()->back()
                    ->withErrors(['financial_year' => __('لا توجد سنة مالية نشطة.')]);
            }

            $openingBalances = OpeningBalance::select(['account_id', 'debit_balance', 'credit_balance', 'notes'])
                ->with(['account:id,code,name'])
                ->where('financial_year_id', $activeFinancialYear->id)
                ->orderBy('account_id')
                ->get();

            if ($openingBalances->isEmpty()) {
                return redirect()->back()
                    ->withErrors(['error' => __('No opening balances found to export.')]);
            }

            $fileName = "opening_balances_{$activeFinancialYear->name}.csv";

            return response()->streamDownload(function () use ($openingBalances) {
                $file = fopen('php://output', 'w');

                // إضافة BOM لدعم UTF-8 في Excel
                fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

                // Headers
                fputcsv($file, [
                    __('Account Code'),
                    __('Account Name'),
                    __('Debit Balance'),
                    __('Credit Balance'),
                    __('Net Balance'),
                    __('Balance Type'),
                    __('Notes')
                ]);

                foreach ($openingBalances as $balance) {
                    $netBalance = $balance->debit_balance - $balance->credit_balance;
                    $balanceType = $netBalance > 0 ? __('Debit') : ($netBalance < 0 ? __('Credit') : __('Zero'));

                    fputcsv($file, [
                        $balance->account->code ?? '',
                        $balance->account->name ?? '',
                        number_format($balance->debit_balance, 2),
                        number_format($balance->credit_balance, 2),
                        number_format(abs($netBalance), 2),
                        $balanceType,
                        $balance->notes ?? ''
                    ]);
                }

                fclose($file);
            }, $fileName, [
                'Content-Type' => 'text/csv; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ]);
        } catch (\Exception $e) {
            Log::error('Error exporting opening balances: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => __('Error exporting data. Please try again.')]);
        }
    }

    /**
     * حذف جميع الأرصدة الافتتاحية للسنة المالية النشطة
     */
    public function clear(Request $request)
    {
        try {
            $activeFinancialYear = FinancialYearHelper::getActiveFinancialYear();

            if (!$activeFinancialYear) {
                return redirect()->back()
                    ->withErrors(['financial_year' => __('لا توجد سنة مالية نشطة.')]);
            }

            if ($activeFinancialYear->is_closed) {
                return redirect()->back()
                    ->withErrors(['financial_year' => __('السنة المالية النشطة مغلقة. لا يمكن حذف الأرصدة.')]);
            }

            DB::transaction(function () use ($activeFinancialYear) {
                // البحث عن القيد المراد حذفه لإطلاق Event للتنظيف
                $openingJournalEntry = JournalEntry::where('source_type', JournalEntry::SOURCE_OPENING_BALANCE)
                    ->where('financial_year_id', $activeFinancialYear->id)
                    ->with(['details.account'])
                    ->first();

                // حذف قيد الافتتاح باستخدام raw query للسرعة
                $deletedEntries = DB::table('journal_entries')
                    ->where('source_type', JournalEntry::SOURCE_OPENING_BALANCE)
                    ->where('financial_year_id', $activeFinancialYear->id)
                    ->delete();

                // حذف الأرصدة الافتتاحية
                $deletedBalances = DB::table('opening_balances')
                    ->where('financial_year_id', $activeFinancialYear->id)
                    ->delete();

                // حذف account transactions للقيد المحذوف
                if ($openingJournalEntry) {
                    DB::table('account_transactions')
                        ->where('journal_entry_id', $openingJournalEntry->id)
                        ->delete();
                }

                Log::info("Cleared opening balances for financial year {$activeFinancialYear->id}: {$deletedEntries} entries, {$deletedBalances} balances");
            });

            // مسح الكاش
            Cache::forget('accounts_tree_opening');

            return redirect()->route('opening-journal-entry.index')
                ->with('success', __('Opening balances cleared successfully.'));
        } catch (\Exception $e) {
            Log::error('Error clearing opening balances: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => __('Error clearing opening balances. Please try again.')]);
        }
    }

    /**
     * بناء شجرة الحسابات محسنة للأداء
     */
    private function buildAccountTreeOptimized()
    {
        try {
            $allAccounts = Account::select(['id', 'code', 'name', 'ownerEl', 'slave', 'has_sub'])
                ->orderBy('code')
                ->get();

            if ($allAccounts->isEmpty()) {
                return collect();
            }

            // إنشاء مصفوفة للوصول السريع
            $accountsById = $allAccounts->keyBy('id');
            $accountsByParent = $allAccounts->groupBy('ownerEl');

            $processedAccounts = $allAccounts->map(function ($account) use ($accountsById) {
                $account->level = $this->calculateAccountLevelOptimized($account, $accountsById);
                $account->canInput = $account->slave == 1 && $account->has_sub == 0;
                $account->shouldShowSubtotal = $account->slave == 1 && $account->has_sub == 1;
                $account->isMainTitle = $account->slave == 0;
                return $account;
            });

            return $this->sortAccountsHierarchicallyOptimized($processedAccounts, $accountsByParent);
        } catch (\Exception $e) {
            Log::error('Error building account tree: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * حساب مستوى العمق للحساب محسن
     */
    private function calculateAccountLevelOptimized($account, $accountsById, $level = 0, $visitedIds = [])
    {
        if (in_array($account->id, $visitedIds) || $level > 10) { // منع التكرار اللانهائي
            return $level;
        }

        if ($account->ownerEl === null || $account->ownerEl == 0) {
            return $level;
        }

        $visitedIds[] = $account->id;
        $parent = $accountsById->get($account->ownerEl);

        if ($parent) {
            return $this->calculateAccountLevelOptimized($parent, $accountsById, $level + 1, $visitedIds);
        }

        return $level;
    }

    /**
     * ترتيب الحسابات هرمياً محسن
     */
    private function sortAccountsHierarchicallyOptimized($accounts, $accountsByParent)
    {
        $result = collect();
        $processed = [];

        // الحسابات الجذر
        $rootAccounts = $accountsByParent->get('') ?? $accountsByParent->get(null) ?? collect();
        $rootAccounts = $rootAccounts->merge($accountsByParent->get(0) ?? collect());
        $rootAccounts = $rootAccounts->sortBy('code');

        foreach ($rootAccounts as $rootAccount) {
            $this->addAccountWithChildrenOptimized($rootAccount, $accountsByParent, $result, $processed);
        }

        return $result;
    }

    /**
     * إضافة الحساب مع أطفاله تكرارياً محسن
     */
    private function addAccountWithChildrenOptimized($account, $accountsByParent, &$result, &$processed)
    {
        if (in_array($account->id, $processed)) {
            return;
        }

        $result->push($account);
        $processed[] = $account->id;

        $children = $accountsByParent->get($account->id, collect())->sortBy('code');

        foreach ($children as $child) {
            $this->addAccountWithChildrenOptimized($child, $accountsByParent, $result, $processed);
        }
    }

    /**
     * تحضير البيانات بأقصى سرعة - بدون logs
     */
    private function prepareValidDetailsUltraFast(?array $details): array
    {
        if (!is_array($details) || empty($details)) {
            return [];
        }

        $validDetails = [];

        // فلترة معرفات الحسابات الصحيحة
        $accountIds = array_filter(array_keys($details), function ($id) {
            return is_numeric($id) && $id > 0;
        });

        if (empty($accountIds)) {
            return [];
        }

        // جلب الحسابات الموجودة دفعة واحدة
        $existingAccounts = Account::whereIn('id', $accountIds)->pluck('id')->flip();

        foreach ($details as $accountId => $detail) {
            if (!is_numeric($accountId) || $accountId <= 0) continue;
            if (!isset($existingAccounts[$accountId])) continue;
            if (!is_array($detail)) continue;

            $debit = round(floatval($detail['debit'] ?? 0), 2);
            $credit = round(floatval($detail['credit'] ?? 0), 2);

            if ($debit < 0 || $credit < 0) continue;
            if ($debit > 0 && $credit > 0) $credit = 0; // أولوية للمدين

            if ($debit > 0 || $credit > 0) {
                $validDetails[] = [
                    'account_id' => (int) $accountId,
                    'debit' => $debit,
                    'credit' => $credit,
                    'statement' => !empty($detail['statement']) ? trim($detail['statement']) : null,
                ];
            }
        }

        return $validDetails;
    }

    /**
     * حفظ القيد بأقصى سرعة وإرجاع معرف القيد
     */
    private function saveOpeningJournalEntryUltraFast($validDetails, $activeFinancialYear, $entryDate, $description, $defaultCurrency)
    {
        $totalDebit = array_sum(array_column($validDetails, 'debit'));
        $totalCredit = array_sum(array_column($validDetails, 'credit'));
        $now = now();
        $userId = auth()->id();

        // البحث عن قيد موجود باستخدام raw query
        $existingEntry = DB::select("
            SELECT id
            FROM journal_entries
            WHERE source_type = ? AND financial_year_id = ?
            LIMIT 1
        ", [JournalEntry::SOURCE_OPENING_BALANCE, $activeFinancialYear->id]);

        if (!empty($existingEntry)) {
            $entryId = $existingEntry[0]->id;

            // تحديث القيد الموجود
            DB::update("
                UPDATE journal_entries
                SET entry_date = ?, description = ?, total_debit = ?, total_credit = ?, updated_by = ?, updated_at = ?
                WHERE id = ?
            ", [$entryDate, $description, $totalDebit, $totalCredit, $userId, $now, $entryId]);

            // حذف التفاصيل القديمة
            DB::delete("DELETE FROM journal_entry_details WHERE journal_entry_id = ?", [$entryId]);
        } else {
            // إنشاء قيد جديد
            $entryNumber = $this->generateEntryNumberUltraFast();

            $entryId = DB::table('journal_entries')->insertGetId([
                'entry_date' => $entryDate,
                'description' => $description,
                'currency_id' => $defaultCurrency->id,
                'financial_year_id' => $activeFinancialYear->id,
                'entry_number' => $entryNumber,
                'reference_number' => 'OB-REF-' . date('YmdHis'),
                'source_type' => JournalEntry::SOURCE_OPENING_BALANCE,
                'source_id' => 0,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'created_by' => $userId,
                'updated_by' => $userId,
                'status' => JournalEntry::STATUS_POSTED,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // إدخال التفاصيل دفعة واحدة
        if (!empty($validDetails)) {
            $detailsData = [];
            foreach ($validDetails as $detail) {
                $detailsData[] = [
                    'journal_entry_id' => $entryId,
                    'account_id' => $detail['account_id'],
                    'debit' => $detail['debit'],
                    'credit' => $detail['credit'],
                    'statement' => $detail['statement'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // تقسيم البيانات إلى chunks لتجنب مشاكل الذاكرة
            $chunks = array_chunk($detailsData, 500);
            foreach ($chunks as $chunk) {
                DB::table('journal_entry_details')->insert($chunk);
            }
        }

        return $entryId; // إرجاع معرف القيد للاستخدام في Event
    }

    /**
     * مزامنة الأرصدة بأقصى سرعة
     */
    private function syncOpeningBalancesUltraFast($validDetails, $activeFinancialYear, $balanceDate)
    {
        $now = now();
        $userId = auth()->id();

        // حذف الأرصدة القديمة باستخدام raw query
        DB::delete("DELETE FROM opening_balances WHERE financial_year_id = ?", [$activeFinancialYear->id]);

        // إدخال الأرصدة الجديدة دفعة واحدة
        if (!empty($validDetails)) {
            $balancesData = [];
            foreach ($validDetails as $detail) {
                $balancesData[] = [
                    'account_id' => $detail['account_id'],
                    'financial_year_id' => $activeFinancialYear->id,
                    'debit_balance' => $detail['debit'],
                    'credit_balance' => $detail['credit'],
                    'balance_date' => $balanceDate,
                    'notes' => $detail['statement'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // تقسيم البيانات إلى chunks
            $chunks = array_chunk($balancesData, 500);
            foreach ($chunks as $chunk) {
                DB::table('opening_balances')->insert($chunk);
            }
        }
    }

    /**
     * توليد رقم قيد بأقصى سرعة
     */
    private function generateEntryNumberUltraFast(): string
    {
        $year = date('Y');

        // استخدام raw query للسرعة
        $result = DB::select("
            SELECT entry_number
            FROM journal_entries
            WHERE source_type = ? AND YEAR(created_at) = ?
            ORDER BY id DESC
            LIMIT 1
        ", [JournalEntry::SOURCE_OPENING_BALANCE, $year]);

        if (!empty($result)) {
            $sequence = (int) substr($result[0]->entry_number, -4) + 1;
        } else {
            $sequence = 1;
        }

        return 'OB-' . $year . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * الحصول على العملة الافتراضية من الكاش
     */
    private function getDefaultCurrencyCached()
    {
        return Cache::remember('default_currency', 3600, function () {
            $currency = Currency::where('is_default', true)->first();
            return $currency ?: Currency::first();
        });
    }
}
