<?php

namespace App\Repositories;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Interfaces\JournalEntryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class JournalEntryRepository extends BaseRepository implements JournalEntryRepositoryInterface
{
    public function __construct(JournalEntry $model)
    {
        parent::__construct($model);
    }

    public function allWithDetails(): Collection
    {
        return $this->model->with('details')->get();
    }

    public function findWithDetails(int $id): ?Model
    {
        return $this->model->with('details')->find($id);
    }

    public function createWithDetails(array $data, array $details)
    {
        return DB::transaction(function () use ($data, $details) {

            $journalEntry = $this->model->create($data);
            foreach ($details as $detail) {
                $journalEntry->details()->create($detail);
            }

            return $journalEntry->fresh();
        });
    }
}
