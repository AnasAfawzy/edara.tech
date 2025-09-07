<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\StoreBrandRequest;
use App\Http\Requests\UpdateBrandRequest;

class BrandController extends Controller
{
    protected BrandService $brandService;

    public function __construct(BrandService $brandService)
    {
        $this->brandService = $brandService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $brands = $this->brandService->searchBrands('', 10);
        return view('brands.index', compact('brands'));
    }

    /**
     * Search brands via AJAX
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('perPage', 10);

            $brands = $this->brandService->searchBrands($search, $perPage);

            $tableHtml = view('brands.partials.table', compact('brands'))->render();

            $paginationHtml = '';
            if ($brands->hasPages()) {
                $paginationHtml = view('pagination::bootstrap-4', ['paginator' => $brands])->render();
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name',
                'name_en' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'required|boolean'
            ]);

            $brand = $this->brandService->createBrand($validatedData);

            return response()->json([
                'success' => true,
                'message' => __('Brand added successfully'),
                'brand' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): JsonResponse
    {
        try {
            $brand = $this->brandService->findBrandOrFail($id);

            return response()->json([
                'success' => true,
                'brand' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:brands,name,' . $id,
                'name_en' => 'nullable|string|max:255',
                'notes' => 'nullable|string',
                'status' => 'required|boolean'
            ]);

            $brand = $this->brandService->updateBrand($id, $validatedData);

            return response()->json([
                'success' => true,
                'message' => __('Brand updated successfully'),
                'brand' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->brandService->deleteBrand($id);

            return response()->json([
                'success' => true,
                'message' => __('Brand deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $brand = $this->brandService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'message' => __('Brand status updated successfully'),
                'brand' => $brand
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }
}
