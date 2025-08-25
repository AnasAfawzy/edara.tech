<?php


namespace App\Services;

use App\Models\CostCenter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\CostCenterRepository;
use App\Services\TreeCodeGeneratorService;
use Illuminate\Database\Eloquent\Model;

class CostCenterService extends BaseService
{
    protected $repository;
    protected $treeCodeGenerator;

    public function __construct(CostCenterRepository $repository, TreeCodeGeneratorService $treeCodeGenerator)
    {
        parent::__construct($repository);
        $this->repository = $repository;
        $this->treeCodeGenerator = $treeCodeGenerator;
    }

    public function getCostCenterTree()
    {
        return $this->repository->getTree();
    }

    public function getMainCostCenters()
    {
        return $this->repository->getMainCostCenters();
    }

    public function generateCostCenterData(array $data): array
    {
        return $this->treeCodeGenerator->generate($data, $this->repository->getModel());
    }

    public function getChildrenOf(int $parentId)
    {
        return $this->repository->getModel()
            ->where('ownerEl', $parentId)
            ->get();
    }

    // إنشاء مركز تكلفة جديد
    public function createCostCenter(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $data = $this->generateCostCenterData($data);
                return $this->repository->create($data);
            });
        } catch (\Exception $e) {
            Log::error(__('Cost Center creation failed') . " - " . $e->getMessage());
            throw new \Exception(__('Cost Center creation failed'));
        }
    }

    // تعديل مركز تكلفة
    public function update($id, array $data): Model
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                if (!$id) {
                    throw new \Exception(__('Cost Center Not Found'));
                }
                $data = $this->generateCostCenterData($data);
                return $this->repository->update($id, $data);
            });
        } catch (\Exception $e) {
            Log::error(__('Cost Center update failed') . " - " . $e->getMessage());
            throw new \Exception(__('Cost Center update failed'));
        }
    }

    // حذف مركز تكلفة
    public function delete($id): bool
    {
        try {
            return DB::transaction(function () use ($id) {
                $costCenter = $this->repository->find($id);
                if (!$costCenter) {
                    throw new \Exception(__('Cost Center not found'));
                }

                // تحقق من وجود أبناء
                if ($costCenter->children()->exists()) {
                    throw new \Exception(__('Cannot delete this cost center because it has children'));
                }

                return (bool) $this->repository->delete($id);
            });
        } catch (\Exception $e) {
            Log::error(__('Cost Center deletion failed') . " - " . $e->getMessage());
            throw new \Exception(__('Cost Center deletion failed'));
        }
    }
}
