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
        try {
            $perPage = (int) $request->get('perPage', 10);
            $search = (string) $request->get('search', '');

            Log::info('Warehouse index called', [
                'perPage' => $perPage,
                'search' => $search
            ]);

            $warehouses = $this->warehouseService->searchWarehouses($search, $perPage);

            return view('warehouses.index', compact(
                'warehouses',
                'perPage',
                'search'
            ));
        } catch (Exception $e) {
            Log::error('Warehouse index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'حدث خطأ أثناء تحميل المخازن');
        }
    }

    /**
     * Search warehouses (AJAX)
     */
    public function search(Request $request)
    {
        try {
            $search = (string) ($request->get('search') ?? '');
            $perPage = (int) ($request->get('perPage') ?? 10);

            Log::info('Warehouse search called', [
                'search' => $search,
                'perPage' => $perPage,
                'user_id' => Auth::id()
            ]);

            if ($perPage < 1 || $perPage > 100) {
                $perPage = 10;
            }

            $warehouses = $this->warehouseService->searchWarehouses($search, $perPage);

            Log::info('Warehouses found', [
                'count' => $warehouses->count(),
                'total' => $warehouses->total()
            ]);

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
                'message' => 'حدث خطأ أثناء البحث: ' . $e->getMessage(),
                'error_code' => 'SEARCH_ERROR'
            ], 500);
        }
    }

    /**
     * Store a newly created warehouse
     */
    public function store(Request $request)
    {
        try {
            Log::info('Warehouse store called', [
                'data' => $request->except(['_token']),
                'user_id' => Auth::id()
            ]);

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'status' => 'required|boolean', // تغيير إلى boolean
            ]);

            $warehouse = $this->warehouseService->createWarehouse($data);

            Log::info('Warehouse created successfully', [
                'warehouse_id' => $warehouse->id,
                'name' => $warehouse->name,
                'status' => $warehouse->status
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Warehouse added successfully'),
                'warehouse' => $warehouse
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Warehouse store validation error', [
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Warehouse store error: ' . $e->getMessage(), [
                'data' => $request->except(['_token']),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إضافة المخزن: ' . $e->getMessage(),
                'error_code' => 'STORE_ERROR'
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified warehouse
     */
    public function edit($id)
    {
        try {
            Log::info('Warehouse edit called', [
                'warehouse_id' => $id,
                'user_id' => Auth::id()
            ]);

            $warehouse = $this->warehouseService->findWarehouseOrFail($id);

            // تأكد من إرجاع status كـ boolean
            $warehouseData = $warehouse->toArray();
            $warehouseData['status'] = (bool) $warehouse->status;

            Log::info('Warehouse data for edit', [
                'warehouse' => $warehouseData
            ]);

            return response()->json([
                'success' => true,
                'warehouse' => $warehouseData
            ]);
        } catch (Exception $e) {
            Log::error('Warehouse edit error: ' . $e->getMessage(), [
                'warehouse_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'المخزن غير موجود',
                'error_code' => 'NOT_FOUND'
            ], 404);
        }
    }

    /**
     * Update the specified warehouse
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Warehouse update called', [
                'warehouse_id' => $id,
                'data' => $request->except(['_token', '_method']),
                'user_id' => Auth::id()
            ]);

            $data = $request->validate([
                'name' => 'required|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'status' => 'required|boolean', // تغيير إلى boolean
            ]);

            $warehouse = $this->warehouseService->updateWarehouse($id, $data);

            Log::info('Warehouse updated successfully', [
                'warehouse_id' => $id,
                'name' => $warehouse->name,
                'status' => $warehouse->status
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Warehouse updated successfully'),
                'warehouse' => $warehouse
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('Warehouse update validation error', [
                'warehouse_id' => $id,
                'errors' => $e->errors(),
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'بيانات غير صحيحة',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Warehouse update error: ' . $e->getMessage(), [
                'warehouse_id' => $id,
                'data' => $request->except(['_token', '_method']),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث المخزن: ' . $e->getMessage(),
                'error_code' => 'UPDATE_ERROR'
            ], 500);
        }
    }

    /**
     * Remove the specified warehouse
     */
    public function destroy($id)
    {
        try {
            Log::info('Warehouse destroy called', [
                'warehouse_id' => $id,
                'user_id' => Auth::id()
            ]);

            $this->warehouseService->deleteWarehouse($id);

            Log::info('Warehouse deleted successfully', [
                'warehouse_id' => $id
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Deleted Successfully')
            ]);
        } catch (Exception $e) {
            Log::error('Warehouse destroy error: ' . $e->getMessage(), [
                'warehouse_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف المخزن: ' . $e->getMessage(),
                'error_code' => 'DELETE_ERROR'
            ], 500);
        }
    }

    /**
     * Toggle warehouse status
     */
    public function toggleStatus($id)
    {
        try {
            Log::info('Warehouse toggle status called', [
                'warehouse_id' => $id,
                'user_id' => Auth::id()
            ]);

            $warehouse = $this->warehouseService->toggleStatus($id);

            Log::info('Warehouse status toggled successfully', [
                'warehouse_id' => $id,
                'new_status' => $warehouse->status
            ]);

            return response()->json([
                'success' => true,
                'message' => __('Status updated successfully'),
                'warehouse' => $warehouse
            ]);
        } catch (Exception $e) {
            Log::error('Warehouse toggle status error: ' . $e->getMessage(), [
                'warehouse_id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تغيير حالة المخزن: ' . $e->getMessage(),
                'error_code' => 'TOGGLE_ERROR'
            ], 500);
        }
    }
}
