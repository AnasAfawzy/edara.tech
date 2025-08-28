<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Currency;
use App\Models\CostCenter;
use App\Models\JournalEntry;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\JournalEntryService;
use App\Services\AttachmentService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use App\Services\UserService;

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
            // This is a new entry being reversed, so we pass it to the view
            return view('JournalEntry.create', compact('currencies', 'accounts', 'costCenters', 'journalEntry'));
        }

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
            'reverses_entry_id' => 'nullable|exists:journal_entries,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // Max 10MB per file
        ]);

        try {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($request->details as $index => $detail) {
                $row = $index + 1;
                if (empty($detail['debit']) && empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Row #:row must have either a debit or credit amount', ['row' => $row])
                    ], 422);
                }

                if (!empty($detail['debit']) && !empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Row #:row can have either debit or credit, not both', ['row' => $row])
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

            $data = $request->only(['entry_date', 'description', 'currency_id', 'reverses_entry_id']);

            $journalEntry = $this->journalEntryService->createEntry(
                $data,
                $request->details,
                'manual',
                0
            );

            // Handle attachments
            if ($request->hasFile('attachments')) {
                $this->attachmentService->uploadAttachments($journalEntry, $request->file('attachments'), __('Journal Entry Attachments'));
            }

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

        // Load attachments for the view
        $attachments = $this->attachmentService->getAttachments($journalEntry);

        if (request()->wantsJson() || request()->ajax()) {
            return view('JournalEntry.partials.show', compact('journalEntry', 'attachments'))->render();
        }

        return view('JournalEntry.show', compact('journalEntry', 'attachments'));
    }

    public function edit(string $id)
    {
        $journalEntry = $this->journalEntryService->getEntryWithDetails($id);

        if (!$journalEntry) {
            abort(404);
        }

        $currencies = Currency::get();
        $accounts = Account::where('slave', 1)->get();
        $costCenters = CostCenter::all();
        $attachments = $this->attachmentService->getAttachments($journalEntry);

        return view('JournalEntry.edit', compact('journalEntry', 'currencies', 'accounts', 'costCenters', 'attachments'));
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
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240', // Max 10MB per file
            'deleted_attachments' => 'nullable|array',
            'deleted_attachments.*' => 'integer|exists:attachment_files,id',
        ]);

        try {
            $totalDebit = 0;
            $totalCredit = 0;

            foreach ($request->details as $index => $detail) {
                $row = $index + 1;
                if (empty($detail['debit']) && empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Row #:row must have either a debit or credit amount', ['row' => $row])
                    ], 422);
                }

                if (!empty($detail['debit']) && !empty($detail['credit'])) {
                    return response()->json([
                        'success' => false,
                        'message' => __('Row #:row can have either debit or credit, not both', ['row' => $row])
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

            // Handle new attachments
            if ($request->hasFile('attachments')) {
                $this->attachmentService->uploadAttachments($journalEntry, $request->file('attachments'), __('Journal Entry Attachments'));
            }

            // Handle deleted attachments
            if ($request->has('deleted_attachments')) {
                foreach ($request->input('deleted_attachments') as $fileId) {
                    $this->attachmentService->deleteAttachmentFile($fileId);
                }
            }

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
            $journalEntry = JournalEntry::find($id);

            if (!$journalEntry) {
                return response()->json(['success' => false, 'message' => __('Journal entry not found')], 404);
            }

            // Optionally delete attachments when journal entry is deleted
            foreach ($journalEntry->attachments as $attachment) {
                $this->attachmentService->deleteAttachment($attachment->id);
            }

            $this->journalEntryService->deleteEntry($id);

            return response()->json([
                'success' => true,
                'message' => __('Journal entry deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
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
            $createdByUserId = $request->get('created_by_user_id'); // New filter
            $sourceType = $request->get('source_type'); // New filter
            $reversalStatus = $request->get('reversal_status'); // New filter: 'original', 'reversed', 'reversing'

            $query = JournalEntry::with(['currency', 'financialYear', 'details.account', 'details.costCenter', 'creator', 'updater']);

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

            // New filters
            if (!empty($createdByUserId)) {
                $query->where('created_by', $createdByUserId);
            }

            if (!empty($sourceType)) {
                $query->where('source_type', $sourceType);
            }

            if (!empty($reversalStatus)) {
                if ($reversalStatus === 'original') {
                    $query->whereNull('reverses_entry_id')->whereNull('reversed_by_entry_id');
                } elseif ($reversalStatus === 'reversed') {
                    $query->whereNotNull('reverses_entry_id');
                } elseif ($reversalStatus === 'reversing') {
                    $query->whereNotNull('reversed_by_entry_id');
                }
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

    public function searchAccounts(Request $request)
    {
        $searchTerm = $request->get('q');
        $accounts = Account::where('slave', 1)->where(function ($query) use ($searchTerm) {
            $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                ->orWhere('code', 'LIKE', '%' . $searchTerm . '%');
        })
            ->limit(20)
            ->get([
                'id',
                'name as text',
                'code'
            ]);

        return response()->json($accounts);
    }

    public function searchCostCenters(Request $request)
    {
        $searchTerm = $request->get('q');
        $costCenters = CostCenter::where('name', 'LIKE', '%' . $searchTerm . '%')
            ->orWhere('code', 'LIKE', '%' . $searchTerm . '%')
            ->limit(20)
            ->get([
                'id',
                'name as text',
                'code'
            ]);

        return response()->json($costCenters);
    }

    public function reverse(string $id)
    {
        $originalEntry = $this->journalEntryService->getEntryWithDetails($id);

        if (!$originalEntry || $originalEntry->reversed_by_entry_id || $originalEntry->reverses_entry_id) {
            return redirect()->route('journal-entries.index')->with('error', __('This entry cannot be reversed.'));
        }

        $journalEntry = new JournalEntry();
        $journalEntry->entry_date = Carbon::now();
        $journalEntry->description = __('Reversing entry for entry #') . $originalEntry->entry_number;
        $journalEntry->currency_id = $originalEntry->currency_id;
        $journalEntry->reverses_entry_id = $originalEntry->id; // Set the link to the original entry

        $reversedDetails = $originalEntry->details->map(function ($detail) {
            $newDetail = $detail->replicate();
            $newDetail->debit = $detail->credit;
            $newDetail->credit = $detail->debit;
            return $newDetail;
        });

        $journalEntry->setRelation('details', $reversedDetails);

        return $this->create($journalEntry);
    }

    public function downloadAttachmentFile(int $fileId)
    {
        $file = $this->attachmentService->getAttachmentFile($fileId);

        if (!$file) {
            abort(404);
        }

        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    public function deleteAttachment(int $attachmentId): JsonResponse
    {
        try {
            $this->attachmentService->deleteAttachment($attachmentId);
            return response()->json(['success' => true, 'message' => __('Attachment deleted successfully')]);
        } catch (\Exception $e) {
        }
    }

    public function deleteAttachmentFile(int $fileId): JsonResponse
    {
        try {
            $this->attachmentService->deleteAttachmentFile($fileId);
            return response()->json(['success' => true, 'message' => __('Attachment file deleted successfully')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
