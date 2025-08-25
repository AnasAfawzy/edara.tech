<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface AccountRepositoryInterface
{
    public function getTree();
    public function getMainAccounts();
    public function getParentAccounts();
    public function find(int $id): ?Model;
    public function create(array $data): Model;
    public function update(int $id, array $data): ?Model;
    public function delete(int $id): bool;
    public function getModel();
    public function getAccountDetails(int $id): ?Model;
}
