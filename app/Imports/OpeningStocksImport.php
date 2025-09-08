<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\OpeningStock;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class OpeningStocksImport implements ToModel, WithHeadingRow, WithValidation
{
    private $defaultOpeningDate;
    private $importedCount = 0;
    private $errors = [];

    public function __construct($defaultOpeningDate = null)
    {
        $this->defaultOpeningDate = $defaultOpeningDate ?? now()->format('Y-m-d');
    }

    public function model(array $row)
    {
        try {
            $product = Product::where('code', $row['product_code'])->first();

            if (!$product) {
                $this->errors[] = "Product with code {$row['product_code']} not found";
                return null;
            }

            $openingDate = !empty($row['opening_date']) ? $row['opening_date'] : $this->defaultOpeningDate;

            // Check if opening stock already exists
            $existing = OpeningStock::where('product_id', $product->id)
                ->where('opening_date', $openingDate)
                ->first();

            if ($existing) {
                $this->errors[] = "Opening stock already exists for product {$row['product_code']} on {$openingDate}";
                return null;
            }

            $this->importedCount++;

            return new OpeningStock([
                'product_id' => $product->id,
                'quantity' => $row['quantity'],
                'unit_cost' => $row['unit_cost'],
                'total_cost' => $row['quantity'] * $row['unit_cost'],
                'opening_date' => $openingDate,
                'notes' => $row['notes'] ?? null,
                'is_active' => true,
                'created_by' => Auth::id()
            ]);

        } catch (\Exception $e) {
            $this->errors[] = "Error processing row: " . $e->getMessage();
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'product_code' => 'required',
            'quantity' => 'required|numeric|min:0.001',
            'unit_cost' => 'required|numeric|min:0',
        ];
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
