<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Currency;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\JournalEntryService;

class JournalEntryController extends Controller
{
    protected $journalEntryService;

    public function __construct(JournalEntryService $journalEntryService)
    {
        $this->journalEntryService = $journalEntryService;
    }

    public function index()
    {
        $journalEntries = $this->journalEntryService->getAllEntries();
        $currencies = Currency::get();
        $accounts = Account::get();
        $costCenters = CostCenter::get();

        return view('JournalEntry.index', compact('journalEntries', 'currencies', 'accounts', 'costCenters'));
    }

    public function create()
    {
        $currencies = Currency::get();
        $accounts = Account::get();
        $costCenters = CostCenter::get();

        return view('JournalEntry.create', compact('currencies', 'accounts', 'costCenters'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'currency_id' => 'required|exists:currencies,id',
            'details' => 'required|array|min:2',
            'details.*.account_id' => 'required|exists:accounts,id',
            'details.*.debit' => 'nullable|numeric|min:0',
            'details.*.credit' => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
            'details.*.statement' => 'nullable|string|max:255',
        ]);

        try {
            // Validate that each detail has either debit or credit
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($request->details as $detail) {
                if (empty($detail['debit']) && empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Each detail must have either debit or credit amount')
                    ], 422);
                }

                if (!empty($detail['debit']) && !empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Each detail can have either debit or credit, not both')
                    ], 422);
                }

                $totalDebit += floatval($detail['debit'] ?? 0);
                $totalCredit += floatval($detail['credit'] ?? 0);
            }

            // Validate balance
            if (abs($totalDebit - $totalCredit) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => __('Journal entry must be balanced')
                ], 422);
            }

            // إنشاء القيد مع تحديد نوع المصدر كـ manual
            $journalEntry = $this->journalEntryService->createEntry(
                $request->only(['entry_date', 'description', 'currency_id']),
                $request->details,
                'manual', // source_type
                0         // source_id
            );

            return response()->json([
                'success' => true,
                'message' => __('Journal entry created successfully'),
                'data' => $journalEntry
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    public function show(string $id)
    {
        $journalEntry = $this->journalEntryService->getEntryWithDetails($id);

        if (!$journalEntry) {
            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Journal entry not found')
                ], 404);
            }
            abort(404);
        }

        // إذا كان AJAX request، أرجع محتوى المودال فقط
        if (request()->wantsJson() || request()->ajax()) {
            return view('JournalEntry.partials.show', compact('journalEntry'))->render();
        }

        // إذا كان طلب عادي، أرجع الصفحة كاملة
        return view('JournalEntry.show', compact('journalEntry'));
    }

    public function edit(string $id)
    {
        $journalEntry = $this->journalEntryService->getEntryWithDetails($id);

        if (!$journalEntry) {
            abort(404);
        }

        $currencies = Currency::get();
        $accounts = Account::get();
        $costCenters = CostCenter::get();

        return view('JournalEntry.edit', compact('journalEntry', 'currencies', 'accounts', 'costCenters'));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'entry_date' => 'required|date',
            'description' => 'required|string|max:500',
            'currency_id' => 'required|exists:currencies,id',
            'details' => 'required|array|min:2',
            'details.*.account_id' => 'required|exists:accounts,id',
            'details.*.debit' => 'nullable|numeric|min:0',
            'details.*.credit' => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
            'details.*.statement' => 'nullable|string|max:255',
        ]);

        try {
            // نفس التحقق من التوازن...
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($request->details as $detail) {
                if (empty($detail['debit']) && empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Each detail must have either debit or credit amount')
                    ], 422);
                }

                if (!empty($detail['debit']) && !empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Each detail can have either debit or credit, not both')
                    ], 422);
                }

                $totalDebit += floatval($detail['debit'] ?? 0);
                $totalCredit += floatval($detail['credit'] ?? 0);
            }

            if (abs($totalDebit - $totalCredit) > 0.01) {
                return response()->json([
                    'success' => false,
                    'message' => __('Journal entry must be balanced')
                ], 422);
            }

            $journalEntry = $this->journalEntryService->updateEntry(
                $id,
                $request->only(['entry_date', 'description', 'currency_id']),
                $request->details
            );

            return response()->json([
                'success' => true,
                'message' => __('Journal entry updated successfully'),
                'data' => $journalEntry
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }


    public function destroy(string $id): JsonResponse
    {
        try {
            $this->journalEntryService->deleteEntry($id);

            return response()->json([
                'success' => true,
                'message' => __('Journal entry deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('An error occurred while deleting journal entry')
            ], 500);
        }
    }


    public function search(Request $request)
    {
        try {
            $search = $request->get('search');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $perPage = $request->get('per_page', 25);

            $query = JournalEntry::with(['currency', 'financialYear', 'details.account', 'details.costCenter']);

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'LIKE', "%{$search}%")
                        ->orWhere('entry_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('details.account', function ($accountQuery) use ($search) {
                            $accountQuery->where('name', 'LIKE', "%{$search}%")
                                ->orWhere('code', 'LIKE', "%{$search}%");
                        });
                });
            }

            if (!empty($dateFrom)) {
                $query->whereDate('entry_date', '>=', $dateFrom);
            }

            if (!empty($dateTo)) {
                $query->whereDate('entry_date', '<=', $dateTo);
            }

            $journalEntries = $query->orderBy('entry_date', 'desc')->paginate($perPage);

            $html = view('JournalEntry.partials.table', compact('journalEntries'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $journalEntries->appends($request->all())->links()->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error occurred while searching: ' . $e->getMessage()
            ], 500);
        }
    }
}
