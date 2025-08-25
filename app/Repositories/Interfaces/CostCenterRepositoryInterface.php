<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface CostCenterRepositoryInterface
{
    public function getTree();
    public function getMainCostCenters();
    public function find(int $id): ?Model;
    public function getModel();
}
