<?php

namespace App\Exports;

use App\Models\JournalEntry;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;

class JournalEntriesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $query = JournalEntry::with(['currency', 'financialYear', 'details']);

        if ($this->request->filled('search')) {
            $search = $this->request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                    ->orWhere('entry_number', 'LIKE', "%{$search}%");
            });
        }

        if ($this->request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $this->request->input('date_from'));
        }

        if ($this->request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $this->request->input('date_to'));
        }

        if ($this->request->filled('source_type')) {
            $query->where('source_type', $this->request->input('source_type'));
        }

        if ($this->request->filled('reversal_status')) {
            $status = $this->request->input('reversal_status');
            if ($status === 'original') {
                $query->whereNull('reverses_entry_id')->whereNull('reversed_by_entry_id');
            } elseif ($status === 'reversed') {
                $query->whereNotNull('reverses_entry_id');
            } elseif ($status === 'reversing') {
                $query->whereNotNull('reversed_by_entry_id');
            }
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->input('status'));
        }

        return $query->orderBy('entry_date', 'desc');
    }

    public function headings(): array
    {
        return [
            __('Entry Number'),
            __('Date'),
            __('Description'),
            __('Currency'),
            __('Total Debit'),
            __('Total Credit'),
            __('Status'),
            __('Source Type'),
            __('Financial Year'),
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->entry_number,
            $entry->entry_date->format('Y-m-d'),
            $entry->description,
            $entry->currency->code ?? 'N/A',
            $entry->details->sum('debit'),
            $entry->details->sum('credit'),
            ucfirst($entry->status),
            ucfirst($entry->source_type),
            $entry->financialYear->name ?? 'N/A',
        ];
    }
}