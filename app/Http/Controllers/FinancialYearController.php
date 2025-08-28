<?php

namespace App\Http\Controllers;

use App\Services\FinancialYearService;
use Illuminate\Http\Request;

class FinancialYearController extends Controller
{
    protected $financialYearService;

    public function __construct(FinancialYearService $financialYearService)
    {
        $this->financialYearService = $financialYearService;
    }

    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '') ?? '';
        $financialYears = $this->financialYearService->searchFinancialYears($search, $perPage);

        return view('financial_years.index', compact('financialYears', 'perPage', 'search'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:financial_years,name',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $financialYear = $this->financialYearService->createFinancialYear($request->all());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم إنشاء السنة المالية بنجاح',
                    'data' => $financialYear
                ]);
            }

            return redirect()->route('financial-years.index')
                ->with('success', 'تم إنشاء السنة المالية بنجاح');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : []
                ]);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        try {
            $financialYear = $this->financialYearService->getFinancialYear($id);

            if (!$financialYear) {
                return response()->json([
                    'success' => false,
                    'message' => 'السنة المالية غير موجودة'
                ]);
            }

            return response()->json([
                'success' => true,
                'financialYear' => $financialYear
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:financial_years,name,' . $id,
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
            ]);

            $financialYear = $this->financialYearService->updateFinancialYear($id, $request->all());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث السنة المالية بنجاح',
                    'data' => $financialYear
                ]);
            }

            return redirect()->route('financial-years.index')
                ->with('success', 'تم تحديث السنة المالية بنجاح');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'errors' => session('errors') ? session('errors')->getBag('default')->getMessages() : []
                ]);
            }

            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $this->financialYearService->deleteFinancialYear($id);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف السنة المالية بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function activate($id)
    {
        try {
            $this->financialYearService->activateFinancialYear($id);

            return response()->json([
                'success' => true,
                'message' => 'تم تفعيل السنة المالية بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function close($id)
    {
        try {
            $this->financialYearService->closeFinancialYear($id);

            return response()->json([
                'success' => true,
                'message' => 'تم إغلاق السنة المالية بنجاح'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function search(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '') ?? '';
        $financialYears = $this->financialYearService->searchFinancialYears($search, $perPage);

        $view = view('financial_years.partials.table', compact('financialYears'))->render();
        return response()->json(['html' => $view]);
    }
}
