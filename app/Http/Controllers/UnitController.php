<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\UnitService;

class UnitController extends Controller
{
    protected $unitService;

    public function __construct(UnitService $unitService)
    {
        $this->unitService = $unitService;
    }

    /**
     * Display a listing of units
     */
    public function index(Request $request)
    {
        // 🔥 تحميل البيانات بدون معاملات في الـ URL
        $units = $this->unitService->searchUnits('', 10);

        return view('units.index', compact('units'));
    }

    /**
     * Store a newly created unit
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        try {
            $unit = $this->unitService->createUnit($data);

            return response()->json([
                'success' => true,
                'message' => __('Unit added successfully'),
                'unit' => $unit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show the form for editing the specified unit
     */
    public function edit($id)
    {
        try {
            $unit = $this->unitService->findUnitOrFail($id);

            return response()->json([
                'success' => true,
                'unit' => $unit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified unit
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'symbol' => 'required|string|max:10',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        try {
            $unit = $this->unitService->updateUnit($id, $data);

            return response()->json([
                'success' => true,
                'message' => __('Unit updated successfully'),
                'unit' => $unit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified unit
     */
    public function destroy($id)
    {
        try {
            $this->unitService->deleteUnit($id);
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

    /**
     * Search units (AJAX)
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('perPage', 10);
            $page = $request->get('page', 1);

            $units = $this->unitService->searchUnits($search, $perPage);

            $tableHtml = view('units.partials.table', compact('units'))->render();

            // إنشاء HTML للـ pagination
            $paginationHtml = '';
            if ($units->hasPages()) {
                $paginationHtml = view('pagination::bootstrap-4', ['paginator' => $units])->render();
            }

            return response()->json([
                'success' => true,
                'html' => $tableHtml,
                'pagination' => $paginationHtml
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle unit status
     */
    public function toggleStatus($id)
    {
        try {
            $unit = $this->unitService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'message' => __('Status updated successfully'),
                'unit' => $unit
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
