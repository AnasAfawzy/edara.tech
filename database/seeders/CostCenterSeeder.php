<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CostCenter;
use App\Services\CostCenterService;

class CostCenterSeeder extends Seeder
{
    protected CostCenterService $costCenterService;

    public function __construct(CostCenterService $costCenterService)
    {
        $this->costCenterService = $costCenterService;
    }

    public function run()
    {
        $path = database_path('seeders/CostCenters.csv');

        if (!file_exists($path)) {
            $this->command->error("⚠️ CSV file not found: {$path}");
            return;
        }

        $file = fopen($path, 'r');
        $header = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $data = array_combine($header, $row);

            $data = $this->costCenterService->generateCostCenterData($data);

            if (CostCenter::where('code', $data['code'])->exists()) {
                $this->command->warn("⏩ Skipped duplicate code: {$data['code']}");
                continue;
            }

            CostCenter::updateOrCreate(
                ['id' => $data['id']],
                [
                    'name'     => $data['name'] ?? null,
                    'position' => $data['position'],
                    'ownerEl'  => $data['ownerEl'] ?? 0,
                    'slave'    => $this->toBool($data['slave'] ?? 0),
                    'code'     => $data['code'],
                    'level'    => $data['level'],
                    'creditor' => $data['creditor'] ?? 0,
                    'debtor'   => $data['debtor'] ?? 0,
                    'has_sub'  => $this->toBool($data['has_sub'] ?? 0),
                    'is_sub'   => $this->toBool($data['is_sub'] ?? 0),
                ]
            );
        }

        fclose($file);

        $this->command->info("✅ Data imported from CSV successfully!");
    }

    private function toBool($value)
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
    }
}
