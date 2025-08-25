<?php

namespace App\Repositories;

use App\Models\Bank;
use App\Repositories\Interfaces\BankRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BankRepository extends BaseRepository implements BankRepositoryInterface
{

    public function __construct(Bank $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function find(int $id): Model
    {
        return $this->model->find($id);
    }

    public function findOrFail($id): Model
    {
        return $this->model->findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
