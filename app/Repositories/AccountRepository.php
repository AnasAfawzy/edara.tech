<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\Account;
use App\Repositories\Interfaces\AccountRepositoryInterface;

class AccountRepository extends BaseRepository implements AccountRepositoryInterface
{
    public function __construct(Account $model)
    {
        parent::__construct($model);
    }

    public function getTree()
    {
        return $this->model->with('children')->get();
    }

    public function getMainAccounts()
    {
        return $this->model->where('has_sub', 1)->orWhere('slave', 0)->get();
    }

    public function getParentAccounts()
    {
        return $this->model->where('slave', 0)->orWhere('has_sub', 1)->get();
    }

    public function getAccountDetails(int $id): ?Model
    {
        return $this->model->with('parent')->find($id);
    }
}
