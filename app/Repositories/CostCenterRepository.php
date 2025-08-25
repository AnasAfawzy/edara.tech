<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\CostCenter;
use App\Repositories\Interfaces\CostCenterRepositoryInterface;

class CostCenterRepository extends BaseRepository implements CostCenterRepositoryInterface
{
    public function __construct(CostCenter $model)
    {
        parent::__construct($model);
    }

    public function getTree()
    {
        return $this->model->with('children')->get();
    }

    public function getMainCostCenters()
    {
        return $this->model->where('has_sub', true)
            ->orWhere('slave', false)
            ->get();
    }

    public function find(int $id): Model
    {
        return $this->model->find($id);
    }
}
