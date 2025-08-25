<?php

namespace App\Services;

use App\Repositories\Interfaces\CostCenterRepositoryInterface;

class CostCenterService extends BaseService
{
    protected $repository;
    protected $treeCodeGenerator;

    public function __construct(CostCenterRepositoryInterface $repository, TreeCodeGeneratorService $treeCodeGenerator)
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
        $this->treeCodeGenerator->generate($data, $this->repository->getModel());
        return $data;
    }
}
