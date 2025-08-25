<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CurrencyService;
use App\Services\CashVaultService;

class CashVaultController extends Controller
{
    protected $cashVaultService;
    protected $currencyService;

    public function __construct(CashVaultService $cashVaultService, CurrencyService $currencyService)
    {
        $this->cashVaultService = $cashVaultService;
        $this->currencyService = $currencyService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $cashVaults = $this->cashVaultService->searchVaults($search, $perPage);
        $currencies = $this->currencyService->getAllCurrencies();
        return view('CashVault.index', compact('cashVaults', 'perPage', 'search', 'currencies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'balance'     => 'required|numeric',
            'status'      => 'required|string',
        ]);

        $vault = $this->cashVaultService->createVault($data);

        return response()->json([
            'success' => true,
            'message' => __('Vault added successfully'),
            'vault'   => $vault
        ]);
    }

    public function edit($id)
    {
        $vault = $this->cashVaultService->findOrFail($id);

        $currencies = $this->currencyService->getAllCurrencies();

        return response()->json([
            'success' => true,
            'vault'   => $vault,
            'currencies' => $currencies
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'currency_id' => 'required|exists:currencies,id',
            'balance'     => 'required|numeric',
            'status'      => 'required|string',
        ]);

        $vault = $this->cashVaultService->updateVault($id, $data);

        return response()->json([
            'success' => true,
            'message' => __('Vault updated successfully'),
            'vault'   => $vault
        ]);
    }

    public function destroy($id)
    {
        try {
            $this->cashVaultService->deleteVault($id);
            return response()->json([
                'success' => true,
                'message' => __('Deleted Successfully')
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

        $cashVaults = $this->cashVaultService->searchVaults($search, $perPage);

        $view = view('CashVault.partials.table', compact('cashVaults'))->render();

        return response()->json(['html' => $view]);
    }
}
