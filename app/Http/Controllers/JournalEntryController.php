<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Account;
use App\Models\Currency;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use App\Services\UserService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use App\Services\AttachmentService;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JournalEntriesExport;
use App\Services\JournalEntryService;
use Illuminate\Support\Facades\Storage;

class JournalEntryController extends Controller
{
    protected $journalEntryService;
    protected $attachmentService;
    protected $userService;

    public function __construct(JournalEntryService $journalEntryService, AttachmentService $attachmentService, UserService $userService)
    {
        $this->journalEntryService = $journalEntryService;
        $this->attachmentService = $attachmentService;
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 25);
        $journalEntries = $this->journalEntryService->getAllEntries($perPage);
        return view('JournalEntry.index', compact('journalEntries'));
    }

    public function create(JournalEntry $journalEntry = null)
    {
        $currencies = Currency::get();
        $accounts = Account::where('slave', 1)->get();
        $costCenters = CostCenter::all();

        if ($journalEntry && $journalEntry->exists === false) {
            $journalEntry = null;
        }

        return view('JournalEntry.create', compact('currencies', 'accounts', 'costCenters'));
    }

    public function store(Request $request): JsonResponse
    {
        $isDraft = $request->has('is_draft') && $request->boolean('is_draft');
        $status = $isDraft ? JournalEntry::STATUS_DRAFT : JournalEntry::STATUS_PENDING;

        $rules = [
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'details' => 'required|array|min:1',
            'details.*.account_id' => 'required|exists:accounts,id',
            'details.*.statement' => 'nullable|string|max:255',
            'details.*.debit' => 'nullable|numeric|min:0',
            'details.*.credit' => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
        ];

        if ($isDraft) {
            $rules['details.*.debit'] = 'nullable|numeric';
            $rules['details.*.credit'] = 'nullable|numeric';
        }

        $request->validate($rules);

        try {
            $journalEntry = $this->journalEntryService->createEntry(
                $request->only(['entry_date', 'description', 'currency_id']),
                $request->input('details'),
                JournalEntry::SOURCE_MANUAL,
                0,
                $status
            );

            return response()->json([
                'success' => true,
                'message' => __('Journal entry created successfully.'),
                'data' => $journalEntry
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show(string $id)
    {
        $journalEntry = $this->journalEntryService->getEntryWithDetails($id);

        if (!$journalEntry) {
            abort(404, __('Journal entry not found.'));
        }

        $attachments = $this->attachmentService->getAttachments($journalEntry);

        // إذا كان AJAX request وعايز HTML
        if (request()->ajax() && request()->header('Accept') === 'text/html') {
            return view('JournalEntry.partials.show', compact('journalEntry', 'attachments'))->render();
        }

        // إذا كان عايز JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $journalEntry,
                'attachments' => $attachments
            ]);
        }

        return view('JournalEntry.show', compact('journalEntry', 'attachments'));
    }

    public function edit(string $id)
    {
        $journalEntry = $this->journalEntryService->getEntryWithDetails($id);

        if (!$journalEntry) {
            abort(404, __('Journal entry not found.'));
        }

        $currencies = Currency::get();
        $accounts = Account::where('slave', 1)->get();
        $costCenters = CostCenter::all();
        $attachments = $this->attachmentService->getAttachments($journalEntry);

        return view('JournalEntry.edit', compact('journalEntry', 'currencies', 'accounts', 'costCenters', 'attachments'));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $isDraft = $request->has('is_draft') && $request->boolean('is_draft');

        $rules = [
            'entry_date' => 'required|date',
            'description' => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'details' => 'required|array|min:1',
            'details.*.account_id' => 'required|exists:accounts,id',
            'details.*.statement' => 'nullable|string|max:255',
            'details.*.debit' => 'nullable|numeric|min:0',
            'details.*.credit' => 'nullable|numeric|min:0',
            'details.*.cost_center_id' => 'nullable|exists:cost_centers,id',
        ];

        if ($isDraft) {
            $rules['details.*.debit'] = 'nullable|numeric';
            $rules['details.*.credit'] = 'nullable|numeric';
        }

        $request->validate($rules);

        try {
            $status = $isDraft ? JournalEntry::STATUS_DRAFT : null;

            $journalEntry = $this->journalEntryService->updateEntry(
                $id,
                $request->only(['entry_date', 'description', 'currency_id']),
                $request->input('details'),
                $status
            );

            return response()->json([
                'success' => true,
                'message' => __('Journal entry updated successfully.'),
                'data' => $journalEntry
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $this->journalEntryService->deleteEntry($id);

            return response()->json([
                'success' => true,
                'message' => __('Journal entry deleted successfully.')
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function submit(Request $request, $id)
    {
        try {
            $journalEntry = $this->journalEntryService->submitForApproval($id);
            return response()->json([
                'success' => true,
                'message' => __('Journal entry submitted for approval.')
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function approve(Request $request, $id)
    {
        Log::info("Controller: Starting approval process for journal entry: {$id}");

        try {
            $journalEntry = JournalEntry::find($id);
            if (!$journalEntry) {
                return response()->json([
                    'success' => false,
                    'message' => __('Journal entry not found')
                ], 404);
            }

            if ($journalEntry->status !== JournalEntry::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => __('Only pending entries can be approved')
                ], 422);
            }

            // اعتماد القيد أولاً
            $journalEntry = $this->journalEntryService->approveEntry($id);

            // ترحيل القيد مباشرة (زي الأرصدة الافتتاحية)
            $journalEntry = $this->journalEntryService->postEntry($id);

            return response()->json([
                'success' => true,
                'message' => __('Journal entry approved and posted successfully.')
            ]);
        } catch (\Exception $e) {
            Log::error("Controller: Error approving journal entry {$id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function post(Request $request, $id)
    {
        try {
            $journalEntry = $this->journalEntryService->postEntry($id);
            return response()->json([
                'success' => true,
                'message' => __('Journal entry posted successfully.')
            ]);
        } catch (\Exception $e) {
            Log::error('Error posting journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function reject(Request $request, $id)
    {
        try {
            $journalEntry = $this->journalEntryService->rejectEntry($id);
            return response()->json([
                'success' => true,
                'message' => __('Journal entry rejected successfully.')
            ]);
        } catch (\Exception $e) {
            Log::error('Error rejecting journal entry: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function duplicate(string $id)
    {
        try {
            $journalEntry = $this->journalEntryService->duplicateEntry($id);
            return redirect()->route('journal-entries.edit', $journalEntry->id)
                ->with('success', __('Journal entry duplicated successfully.'));
        } catch (\Exception $e) {
            Log::error('Error duplicating journal entry: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        try {
            $query = JournalEntry::with(['currency', 'financialYear']);

            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'LIKE', "%{$search}%")
                        ->orWhere('entry_number', 'LIKE', "%{$search}%")
                        ->orWhere('reference_number', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->filled('source_type')) {
                $query->where('source_type', $request->input('source_type'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('entry_date', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('entry_date', '<=', $request->input('date_to'));
            }

            $perPage = $request->input('per_page', 25);
            $journalEntries = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return view('JournalEntry.partials.table', compact('journalEntries'))->render();
        } catch (\Exception $e) {
            Log::error('Error searching journal entries: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $query = JournalEntry::with(['currency', 'financialYear', 'details.account']);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            $journalEntries = $query->get();

            return Excel::download(new JournalEntriesExport($journalEntries), 'journal-entries-' . date('Y-m-d') . '.xlsx');
        } catch (\Exception $e) {
            Log::error('Error exporting journal entries: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $query = JournalEntry::with(['currency', 'financialYear', 'details.account']);

            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            $journalEntries = $query->get();

            $pdf = Pdf::loadView('JournalEntry.exports.pdf', compact('journalEntries'));

            return $pdf->download('journal-entries-' . date('Y-m-d') . '.pdf');
        } catch (\Exception $e) {
            Log::error('Error exporting journal entries PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
