<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface JournalEntryRepositoryInterface
{
    public function allWithDetails(): Collection;
    public function findWithDetails(int $id): ?Model;
    public function getModel();
    public function createWithDetails(array $data, array $details);
}
