<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CategoryService;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        // 🔥 تحميل البيانات بدون معاملات في الـ URL
        $categories = $this->categoryService->searchCategories('', 10);

        return view('categories.index', compact('categories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        try {
            $category = $this->categoryService->createCategory($data);

            return response()->json([
                'success' => true,
                'message' => __('Category added successfully'),
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit($id)
    {
        try {
            $category = $this->categoryService->findCategoryOrFail($id);

            return response()->json([
                'success' => true,
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
        ]);

        try {
            $category = $this->categoryService->updateCategory($id, $data);

            return response()->json([
                'success' => true,
                'message' => __('Category updated successfully'),
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy($id)
    {
        try {
            $this->categoryService->deleteCategory($id);
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
     * Search categories (AJAX)
     */
    public function search(Request $request)
    {
        try {
            $search = $request->get('search', '');
            $perPage = $request->get('perPage', 10);
            $page = $request->get('page', 1);

            $categories = $this->categoryService->searchCategories($search, $perPage);

            $tableHtml = view('categories.partials.table', compact('categories'))->render();

            // إنشاء HTML للـ pagination
            $paginationHtml = '';
            if ($categories->hasPages()) {
                $paginationHtml = view('pagination::bootstrap-4', ['paginator' => $categories])->render();
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
     * Toggle category status
     */
    public function toggleStatus($id)
    {
        try {
            $category = $this->categoryService->toggleStatus($id);

            return response()->json([
                'success' => true,
                'message' => __('Status updated successfully'),
                'category' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
