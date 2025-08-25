<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface CashVaultRepositoryInterface
{
    public function all(): Collection;
    public function find(int $id): ?Model;
    public function getModel(): Model;
    public function create(array $data): Model;
}
