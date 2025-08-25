<?php

namespace App\Repositories;

use App\Repositories\Interfaces\CurrencyRepositoryInterface;
use App\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CurrencyRepository extends BaseRepository implements CurrencyRepositoryInterface
{
    public function __construct(Currency $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return $this->model->all(); // تصحيح: استخدام all() بدلاً من paginate()
    }

    public function paginate($perPage = 10): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function searchAndPaginate(string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('code', 'like', '%' . $search . '%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function find(int $id): Model
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
