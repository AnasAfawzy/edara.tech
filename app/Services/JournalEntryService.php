<?php

namespace App\Services;

use App\Repositories\Interfaces\JournalEntryRepositoryInterface;

class JournalEntryService
{
    protected JournalEntryRepositoryInterface $repository;

    public function __construct(JournalEntryRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllEntries(): array
    {
        return $this->repository->allWithDetails()->toArray();
    }

    protected function generateEntryNumber(): string
    {
        $last = $this->repository->getModel()->latest('id')->first();
        $nextId = $last ? $last->id + 1 : 1;
        return 'JV-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
    }

    public function createEntry(array $data, array $details, ?string $sourceType = null, ?int $sourceId = null)
    {
        if (!isset($data['entry_number'])) {
            $data['entry_number'] = $this->generateEntryNumber();
        }

        $data['source_type'] = $sourceType ?? 'manual';
        $data['source_id']   = $sourceId ?? 0;

        return $this->repository->createWithDetails($data, $details);
    }
}
