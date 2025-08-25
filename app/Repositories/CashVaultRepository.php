<?php

namespace App\Repositories;

use App\Models\CashVault;
use App\Repositories\Interfaces\CashVaultRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CashVaultRepository extends BaseRepository implements CashVaultRepositoryInterface
{
    public function __construct(CashVault $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }


    public function getModel(): Model
    {
        return $this->model;
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }
}
