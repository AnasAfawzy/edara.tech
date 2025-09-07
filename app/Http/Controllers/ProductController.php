<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\UnitService;
use App\Services\BrandService;
use App\Services\ProductService;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    protected ProductService $productService;
    protected CategoryService $categoryService;
    protected BrandService $brandService;
    protected UnitService $unitService;

    public function __construct(
        ProductService $productService,
        CategoryService $categoryService,
        BrandService $brandService,
        UnitService $unitService
    ) {
        $this->productService = $productService;
        $this->categoryService = $categoryService;
        $this->brandService = $brandService;
        $this->unitService = $unitService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = $this->productService->searchProducts('', [], 10);
        $categories = $this->categoryService->getAllActiveCategories();
        $brands = $this->brandService->getAllActiveBrands();
        $units = $this->unitService->getAllActiveUnits();

        return view('products.index', compact('products', 'categories', 'brands', 'units'));
    }

    /**
     * Search products via AJAX
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('perPage', 10);

            $filters = [];
            if ($request->filled('category_id')) {
                $filters['category_id'] = $request->get('category_id');
            }
            if ($request->filled('brand_id')) {
                $filters['brand_id'] = $request->get('brand_id');
            }
            if ($request->filled('unit_id')) {
                $filters['unit_id'] = $request->get('unit_id');
            }
            if ($request->has('is_active')) {
                $filters['is_active'] = $request->get('is_active');
            }

            $products = $this->productService->searchProducts($search, $filters, $perPage);

            $tableHtml = view('products.partials.table', compact('products'))->render();

            $paginationHtml = '';
            if ($products->hasPages()) {
                $paginationHtml = view('pagination::bootstrap-4', ['paginator' => $products])->render();
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
                'name' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'code' => 'required|string|max:100|unique:products,code',
                'barcode' => 'nullable|string|unique:products,barcode',
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'unit_id' => 'required|exists:units,id',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'min_stock' => 'nullable|numeric|min:0',
                'max_stock' => 'nullable|numeric|gte:min_stock',
                'current_stock' => 'nullable|numeric|min:0',
                'reorder_level' => 'nullable|numeric|min:0',
                'has_expiry' => 'boolean',
                'is_active' => 'boolean'
            ]);

            $product = $this->productService->createProduct($validatedData);

            return response()->json([
                'success' => true,
                'message' => __('Product added successfully'),
                'product' => $product
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
            $product = $this->productService->findProductOrFail($id);

            return response()->json([
                'success' => true,
                'product' => $product
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
                'name' => 'required|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'code' => 'required|string|max:100|unique:products,code,' . $id,
                'barcode' => 'nullable|string|unique:products,barcode,' . $id,
                'category_id' => 'required|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'unit_id' => 'required|exists:units,id',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'purchase_price' => 'required|numeric|min:0',
                'sale_price' => 'required|numeric|min:0',
                'min_stock' => 'nullable|numeric|min:0',
                'max_stock' => 'nullable|numeric|gte:min_stock',
                'current_stock' => 'nullable|numeric|min:0',
                'reorder_level' => 'nullable|numeric|min:0',
                'has_expiry' => 'boolean',
                'is_active' => 'boolean'
            ]);

            $product = $this->productService->updateProduct($id, $validatedData);

            return response()->json([
                'success' => true,
                'message' => __('Product updated successfully'),
                'product' => $product
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
            $this->productService->deleteProduct($id);

            return response()->json([
                'success' => true,
                'message' => __('Product deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $product = $this->productService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'message' => __('Product status updated successfully'),
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Generate product code
     */
    public function generateCode(): JsonResponse
    {
        try {
            $code = $this->productService->generateProductCode();

            return response()->json([
                'success' => true,
                'code' => $code
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('perPage', 10);
            $products = $this->productService->getLowStockProducts($perPage);

            return response()->json([
                'success' => true,
                'products' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
