<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Currency;
use Illuminate\Http\Request;
use App\Services\BankService;
use App\Services\AccountService;
use App\Services\CurrencyService;
use App\Http\Requests\BankRequest;
use App\Http\Requests\UpdateBankRequest;

class BankController extends Controller
{
    protected $bankService, $currencyService, $accountService;

    public function __construct(BankService $bankService, CurrencyService $currencyService, AccountService $accountService)
    {
        $this->bankService = $bankService;
        $this->currencyService = $currencyService;
        $this->accountService = $accountService;
    }

    // عرض كل البنوك مع البحث والتصفية
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $banks = Bank::with('currency')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('account_number', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate($perPage);
        $currencies = $this->currencyService->getAllCurrencies();

        return view('bank.index', compact('banks', 'currencies', 'perPage', 'search'));
    }

    public function show($id)
    {
        $bank = Bank::with('currency')->findOrFail($id);

        return response()->json([
            'success' => true,
            'bank' => $bank
        ]);
    }

    public function store(BankRequest $request)
    {
        $data = $request->validated();

        try {
            $bank = $this->bankService->createBank($data);

            return response()->json([
                'success' => true,
                'message' => __('Bank added successfully'),
                'bank'    => $bank,
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to add bank'),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }




    // جلب بيانات بنك للتعديل (AJAX)
    public function edit($id)
    {
        $bank = $this->bankService->findOrFail($id);

        return response()->json([
            'success' => true,
            'bank' => $bank
        ]);
    }

    // تحديث بنك (AJAX)
    public function update(UpdateBankRequest $request, $id)
    {
        $data = $request->validated();

        try {
            $bank = $this->bankService->updateBank($id, $data);

            return response()->json([
                'success' => true,
                'message' => __('Bank updated successfully'),
                'bank'    => $bank,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => __('Failed to update bank'),
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // حذف بنك (AJAX)
    public function destroy($id)
    {
        try {
            $this->bankService->delete($id);

            return response()->json([
                'success' => true,
                'message' => __('Bank deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = $request->get('perPage', 10);

        $banks = $this->bankService->searchBanks($search, $perPage);

        $view = view('bank.partials.table', compact('banks'))->render();

        return response()->json(['html' => $view]);
    }
}
