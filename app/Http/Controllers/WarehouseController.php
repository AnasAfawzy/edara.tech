<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use App\Services\WarehouseService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    protected $warehouseService;

    public function __construct(WarehouseService $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    /**
     * Display a listing of warehouses
     */
    public function index(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $search = $request->get('search', '');

        $warehouses = $this->warehouseService->searchWarehouses($search, $perPage);

        // تحقق من وجود حساب مخزن افتراضي
        $defaultWarehouseAccount = acc_setting('default_warehouse_account');
        $showWarehouseAccountAlert = empty($defaultWarehouseAccount);

        return view('warehouses.index', compact(
            'warehouses',
            'perPage',
            'search',
            'showWarehouseAccountAlert'
        ));
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        $warehouse = $this->warehouseService->createWarehouse($data);

        return response()->json([
            'success' => true,
            'message' => __('Warehouse added successfully'),
            'warehouse' => $warehouse
        ]);
    }

    /**
     * Show the form for editing the specified warehouse
     */
    public function edit($id)
    {
        $warehouse = $this->warehouseService->findWarehouseOrFail($id);

        return response()->json([
            'success' => true,
            'warehouse' => $warehouse
        ]);
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        $warehouse = $this->warehouseService->updateWarehouse($id, $data);

        return response()->json([
            'success' => true,
            'message' => __('Warehouse updated successfully'),
            'warehouse' => $warehouse
        ]);
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy($id)
    {
        try {
            $this->warehouseService->deleteWarehouse($id);
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
     * Search warehouses (AJAX)
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('perPage', 10);

            Log::info('Warehouse search request', [
                'search' => $search,
                'perPage' => $perPage,
                'user_id' => Auth::id()
            ]);

            $warehouses = $this->warehouseService->searchWarehouses($search, $perPage);

            $view = view('warehouses.partials.table', compact('warehouses'))->render();

            return response()->json([
                'success' => true,
                'html' => $view,
                'count' => $warehouses->count(),
                'total' => $warehouses->total()
            ]);
        } catch (Exception $e) {
            Log::error('Warehouse search error: ' . $e->getMessage(), [
                'search' => $request->get('search'),
                'perPage' => $request->get('perPage'),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => __('An error occurred while searching'),
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle warehouse status
     */
    public function toggleStatus($id)
    {
        try {
            $warehouse = $this->warehouseService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'message' => __('Status updated successfully'),
                'warehouse' => $warehouse
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'TOGGLE_ERROR'
            ], 400);
        }
    }
}
