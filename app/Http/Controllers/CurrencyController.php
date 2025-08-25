<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\CurrencyService;
use App\Exports\CurrencyExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CurrencyController extends Controller
{
    protected $currencyService;
    protected $currencyModel;

    public function __construct(CurrencyService $currencyService, Currency $model)
    {
        $this->currencyService = $currencyService;
        $this->currencyModel = $model;
    }

    /**
     * عرض قائمة العملات
     */
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $currencies = $this->currencyService->getAllCurrenciesPaginated($perPage, $search);

        return view('currency.index', compact('currencies', 'perPage', 'search'));
    }

    /**
     * حفظ عملة جديدة (AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:currencies,code',
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        try {
            $currency = $this->currencyService->createCurrency([
                'name' => trim($request->name),
                'code' => strtoupper(trim($request->code)),
                'exchange_rate' => $request->exchange_rate,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Currency created successfully'),
                    'currency' => $currency
                ]);
            }

            return redirect()->route('currencies.index')
                ->with('success', __('Currency created successfully'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Error creating currency: ') . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', __('Error creating currency: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * عرض بيانات العملة للتعديل (AJAX)
     */
    public function edit($id)
    {
        try {
            $currency = $this->currencyModel::findOrFail($id);

            return response()->json([
                'success' => true,
                'currency' => $currency
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Currency not found'
            ], 404);
        }
    }

    /**
     * تحديث العملة (AJAX)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:10|unique:currencies,code,' . $id,
            'exchange_rate' => 'required|numeric|min:0',
        ]);

        try {
            $currency = $this->currencyModel::findOrFail($id);
            $currency->update([
                'name' => trim($request->name),
                'code' => strtoupper(trim($request->code)),
                'exchange_rate' => $request->exchange_rate,
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => __('Currency updated successfully'),
                    'currency' => $currency
                ]);
            }

            return redirect()->route('currencies.index')
                ->with('success', __('Currency updated successfully'));
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => __('Error updating currency: ') . $e->getMessage()
                ], 422);
            }

            return redirect()->back()
                ->with('error', __('Error updating currency: ') . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * حذف العملة
     */
    public function destroy($id)
    {
        try {
            $currency = $this->currencyModel::findOrFail($id);
            $currency->delete();

            return response()->json([
                'success' => true,
                'message' => __('Currency deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => __('Error deleting currency: ') . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تصدير Excel
     */
    public function exportExcel(Request $request)
    {
        $search = $request->get('search', '');
        $fileName = 'currencies_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new CurrencyExport($search), $fileName);
    }

    /**
     * تصدير CSV
     */
    public function exportCsv(Request $request)
    {
        $search = $request->get('search', '');
        $fileName = 'currencies_' . date('Y-m-d_H-i-s') . '.csv';

        return Excel::download(new CurrencyExport($search), $fileName, \Maatwebsite\Excel\Excel::CSV);
    }

    /**
     * تصدير PDF
     */
    public function exportPdf(Request $request)
    {
        $search = $request->get('search', '');
        $currencies = $this->currencyService->getAllCurrenciesForExport($search);

        $pdf = Pdf::loadView('exports.currencies-pdf', compact('currencies'));
        $fileName = 'currencies_' . date('Y-m-d_H-i-s') . '.pdf';

        return $pdf->download($fileName);
    }
}
