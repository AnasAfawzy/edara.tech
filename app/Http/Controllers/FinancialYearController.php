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

        // إضافة Middleware للصلاحيات
        // $this->middleware('permission:view financial years')->only(['index', 'show']);
        // $this->middleware('permission:create financial years')->only(['create', 'store']);
        // $this->middleware('permission:edit financial years')->only(['edit', 'update']);
        // $this->middleware('permission:delete financial years')->only(['destroy']);
        // $this->middleware('permission:activate financial years')->only(['activate']);
        // $this->middleware('permission:close financial years')->only(['close']);
    }

    public function index()
    {
        $financialYears = $this->financialYearService->getAllFinancialYears();
        return view('financial_years.index', compact('financialYears'));
    }

    public function create()
    {
        return view('financial_years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:financial_years,name',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $result = $this->financialYearService->createFinancialYear($request->all());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return redirect()->route('financial-years.index')
            ->with('success', $result['message']);
    }

    public function show($id)
    {
        $financialYear = $this->financialYearService->getFinancialYear($id);

        if (!$financialYear) {
            return redirect()->route('financial-years.index')
                ->with('error', 'السنة المالية غير موجودة');
        }

        return view('financial_years.show', compact('financialYear'));
    }

    public function edit($id)
    {
        $financialYear = $this->financialYearService->getFinancialYear($id);

        if (!$financialYear) {
            return redirect()->route('financial-years.index')
                ->with('error', 'السنة المالية غير موجودة');
        }

        return view('financial_years.edit', compact('financialYear'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:financial_years,name,' . $id,
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $result = $this->financialYearService->updateFinancialYear($id, $request->all());

        if (!$result['success']) {
            return back()->withErrors(['error' => $result['message']])->withInput();
        }

        return redirect()->route('financial-years.index')
            ->with('success', $result['message']);
    }

    public function destroy($id)
    {
        $result = $this->financialYearService->deleteFinancialYear($id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('financial-years.index')
            ->with('success', $result['message']);
    }

    public function activate($id)
    {
        $result = $this->financialYearService->activateFinancialYear($id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('financial-years.index')
            ->with('success', $result['message']);
    }

    public function close($id)
    {
        $result = $this->financialYearService->closeFinancialYear($id);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return redirect()->route('financial-years.index')
            ->with('success', $result['message']);
    }
}
