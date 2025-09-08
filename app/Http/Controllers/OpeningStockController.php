<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Exports\OpeningStocksExport;
use App\Imports\OpeningStocksImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\OpeningStockService;
use App\Exports\OpeningStocksTemplateExport;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OpeningStockController extends Controller
{
    protected OpeningStockService $openingStockService;

    public function __construct(OpeningStockService $openingStockService)
    {
        $this->openingStockService = $openingStockService;
    }

    public function index(Request $request): View
    {
        $openingStocks = $this->openingStockService->searchOpeningStocks(
            $request->get('search', ''),
            $request->get('perPage', 10)
        );

        $productsForEntry = $this->openingStockService->getProductsForBulkEntry();

        return view('opening-stocks.index', compact('openingStocks', 'productsForEntry'));
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $openingStocks = $this->openingStockService->searchOpeningStocks(
                $request->get('search', ''),
                $request->get('perPage', 10)
            );

            $html = view('opening-stocks.partials.table', compact('openingStocks'))->render();
            $pagination = $openingStocks->appends($request->query())->links()->toHtml();

            return response()->json([
                'success' => true,
                'html' => $html,
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_cost' => 'required|numeric|min:0',
            'opening_date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $openingStock = $this->openingStockService->createOpeningStock($request->all());

            return response()->json([
                'success' => true,
                'message' => __('Opening stock created successfully'),
                'data' => $openingStock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $request->validate([
            'opening_date' => 'required|date',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|numeric|min:0.001',
            'products.*.unit_cost' => 'required|numeric|min:0',
            'products.*.notes' => 'nullable|string|max:500'
        ]);

        try {
            $result = $this->openingStockService->bulkCreateOpeningStocks(
                $request->products,
                $request->opening_date
            );

            return response()->json([
                'success' => true,
                'message' => __('Opening stocks created successfully'),
                'created_count' => $result['created_count'],
                'errors' => $result['errors']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $openingStock = $this->openingStockService->findOpeningStockOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $openingStock->load(['product.category', 'product.brand', 'product.unit'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function edit(int $id): JsonResponse
    {
        try {
            $openingStock = $this->openingStockService->findOpeningStockOrFail($id);

            return response()->json([
                'success' => true,
                'openingStock' => $openingStock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:0.001',
            'unit_cost' => 'required|numeric|min:0',
            'opening_date' => 'required|date',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $openingStock = $this->openingStockService->updateOpeningStock($id, $request->all());

            return response()->json([
                'success' => true,
                'message' => __('Opening stock updated successfully'),
                'data' => $openingStock
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->openingStockService->deleteOpeningStock($id);

            return response()->json([
                'success' => true,
                'message' => __('Opening stock deleted successfully')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function getBulkProducts(): JsonResponse
    {
        try {
            $products = $this->openingStockService->getProductsForBulkEntry();

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

    public function export(): BinaryFileResponse
    {
        return Excel::download(new OpeningStocksExport, 'opening-stocks-' . date('Y-m-d') . '.xlsx');
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
            'default_opening_date' => 'nullable|date'
        ]);

        try {
            $import = new OpeningStocksImport($request->default_opening_date);
            Excel::import($import, $request->file('excel_file'));

            return response()->json([
                'success' => true,
                'message' => __('Opening stocks imported successfully'),
                'imported_count' => $import->getImportedCount(),
                'errors' => $import->getErrors()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new OpeningStocksTemplateExport, 'opening-stocks-template.xlsx');
    }
}
