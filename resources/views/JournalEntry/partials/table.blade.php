<tbody>
    @forelse($journalEntries as $entry)
        <tr id="entry-row-{{ $entry->id }}">
            <td class="text-center">
                <span
                    class="badge bg-primary">{{ $entry->entry_number ?? 'JV-' . str_pad($entry->id, 4, '0', STR_PAD_LEFT) }}</span>
            </td>
            <td class="text-center">{{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}</td>
            <td class="text-center">{{ Str::limit($entry->description, 50) }}</td>
            <td class="text-center">
                <span class="badge bg-info">{{ $entry->currency->code ?? 'N/A' }}</span>
            </td>
            <td class="text-center">
                <strong class="text-success">{{ number_format($entry->details->sum('debit'), 2) }}</strong>
            </td>
            <td class="text-center">
                <strong class="text-danger">{{ number_format($entry->details->sum('credit'), 2) }}</strong>
            </td>
            <td class="text-center">
                <span class="badge {{ $entry->source_type === 'manual' ? 'bg-secondary' : 'bg-warning' }}">
                    {{ $entry->source_type_display ?? ucfirst($entry->source_type ?? 'Manual') }}
                </span>
            </td>
            <td class="text-center">{{ $entry->financialYear->name ?? 'N/A' }}</td>
            <td class="text-center">
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-icon bg-transparent shadow-none border-0 view-entry"
                        data-id="{{ $entry->id }}" title="{{ __('View') }}">
                        <i class="icon-base ti tabler-eye"></i>
                    </button>

                    <a href="{{ route('journal-entries.edit', $entry->id) }}"
                        class="btn btn-icon bg-transparent shadow-none border-0" title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-pencil"></i>
                    </a>

                    <button type="button" class="btn btn-icon bg-transparent shadow-none border-0 delete-entry"
                        data-id="{{ $entry->id }}" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </div>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center py-4">
                <div class="empty-state">
                    <i class="icon-base ti tabler-receipt" style="font-size: 3rem; color: #d1d5db;"></i>
                    <h6 class="mt-2">{{ __('No journal entries found') }}</h6>
                    <p class="text-muted">{{ __('Start by creating your first journal entry') }}</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
