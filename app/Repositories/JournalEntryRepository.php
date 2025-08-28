<?php

namespace App\Repositories;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
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
        return $this->model->with([
            'details' => fn ($q) => $q->orderBy('id'),
            'details.account', 'currency', 'financialYear', 'creator', 'updater'
        ])
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function allWithDetailsPaginated(int $perPage = 25): LengthAwarePaginator
    {
        return $this->model->with([
            'details' => fn ($q) => $q->orderBy('id'),
            'details.account', 'currency', 'financialYear', 'creator', 'updater'
        ])
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function findWithDetails(int $id): ?Model
    {
        return $this->model->with([
            'details' => fn ($q) => $q->orderBy('id'),
            'details.account', 'details.costCenter', 'currency', 'financialYear', 'creator', 'updater'
        ])->find($id);
    }

    public function createWithDetails(array $data, array $details)
    {
        return DB::transaction(function () use ($data, $details) {
            $journalEntry = $this->model->create($data);

            foreach ($details as $detail) {
                $journalEntry->details()->create([
                    'account_id' => $detail['account_id'],
                    'debit' => $detail['debit'] ?? 0,
                    'credit' => $detail['credit'] ?? 0,
                    'cost_center_id' => $detail['cost_center_id'] ?? null,
                    'statement' => $detail['statement'] ?? null,
                ]);
            }

            // If this is a reversing entry, link it to the original entry
            if (!empty($data['reverses_entry_id'])) {
                $originalEntry = $this->find($data['reverses_entry_id']);
                if ($originalEntry) {
                    $originalEntry->reversed_by_entry_id = $journalEntry->id;
                    $originalEntry->save();
                }
            }

            return $journalEntry->fresh(['details.account', 'currency', 'financialYear']);
        });
    }

    public function updateWithDetails(int $id, array $data, array $details)
    {
        return DB::transaction(function () use ($id, $data, $details) {
            $journalEntry = $this->model->findOrFail($id);
            $journalEntry->update($data);

            // Delete existing details
            $journalEntry->details()->delete();

            // Create new details
            foreach ($details as $detail) {
                $journalEntry->details()->create([
                    'account_id' => $detail['account_id'],
                    'debit' => $detail['debit'] ?? 0,
                    'credit' => $detail['credit'] ?? 0,
                    'cost_center_id' => $detail['cost_center_id'] ?? null,
                    'statement' => $detail['statement'] ?? null,

                ]);
            }

            return $journalEntry->fresh(['details.account', 'currency', 'financialYear']);
        });
    }

    public function searchEntries(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with([
            'details' => fn ($q) => $q->orderBy('id'),
            'details.account', 'currency', 'financialYear', 'creator', 'updater'
        ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('entry_number', 'like', "%{$search}%")
                    ->orWhereHas('details.account', function ($accountQuery) use ($search) {
                        $accountQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($filters['date_from'])) {
            $query->where('entry_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('entry_date', '<=', $filters['date_to']);
        }

        if (!empty($filters['currency_id'])) {
            $query->where('currency_id', $filters['currency_id']);
        }

        if (!empty($filters['financial_year_id'])) {
            $query->where('financial_year_id', $filters['financial_year_id']);
        }

        return $query->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($filters['per_page'] ?? 25);
    }

    public function getByFinancialYear(int $financialYearId): Collection
    {
        return $this->model->with(['details.account', 'currency'])
            ->where('financial_year_id', $financialYearId)
            ->orderBy('entry_date', 'desc')
            ->get();
    }

    public function getByDateRange(string $startDate, string $endDate): Collection
    {
        return $this->model->with(['details.account', 'currency', 'financialYear'])
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date', 'desc')
            ->get();
    }

    public function getLastEntryNumber(): ?string
    {
        $lastEntry = $this->model->latest('id')->first();
        return $lastEntry ? $lastEntry->entry_number : null;
    }

    public function getNextEntryNumber(): string
    {
        $lastEntry = $this->model->latest('id')->first();
        $nextId = $lastEntry ? $lastEntry->id + 1 : 1;
        return 'JV-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }
}
