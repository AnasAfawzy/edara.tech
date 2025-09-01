<tbody>
    @forelse($journalEntries as $entry)
        <tr id="entry-row-{{ $entry->id }}">
            <td class="text-center">
                <span
                    class="badge bg-primary">{{ $entry->entry_number ?? 'JV-' . str_pad($entry->id, 4, '0', STR_PAD_LEFT) }}</span>
            </td>
            <td class="text-center">{{ \Carbon\Carbon::parse($entry->entry_date)->format('Y-m-d') }}</td>
            <td>{{ Str::limit($entry->description, 50) }}</td>
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
                @php
                    $statusClasses = [
                        'draft' => 'bg-light text-dark',
                        'pending' => 'bg-warning',
                        'approved' => 'bg-success',
                        'posted' => 'bg-primary',
                        'reversed' => 'bg-danger',
                    ];
                @endphp
                <span
                    class="badge {{ $statusClasses[$entry->status] ?? 'bg-secondary' }}">{{ __($entry->status) }}</span>
            </td>
            <td class="text-center">{{ $entry->financialYear->name ?? 'N/A' }}</td>
            <td class="text-center">
                <div class="d-flex gap-1 justify-content-center">
                    <!-- View Button -->
                    @can('view journal entry')
                        <button type="button" class="btn btn-icon bg-transparent shadow-none border-0 view-entry"
                            data-id="{{ $entry->id }}" title="{{ __('View') }}">
                            <i class="icon-base ti tabler-eye"></i>
                        </button>
                    @endcan

                    <!-- Duplicate Button -->
                    @if (!$entry->reversed_by_entry_id)
                        @can('create journal entry')
                            <a href="{{ route('journal-entries.duplicate', $entry->id) }}"
                                class="btn btn-icon bg-transparent shadow-none border-0" title="{{ __('Duplicate') }}">
                                <i class="icon-base ti tabler-copy"></i>
                            </a>
                        @endcan
                    @endif

                    @if ($entry->status === 'draft' && !$entry->reversed_by_entry_id)
                        @can('submit journal entry')
                            <button type="button"
                                class="btn btn-icon bg-transparent shadow-none border-0 text-primary workflow-action"
                                data-action="submit" data-id="{{ $entry->id }}"
                                title="{{ __('Submit for Approval') }}">
                                <i class="icon-base ti tabler-send"></i>
                            </button>
                        @endcan
                        @can('edit journal entry')
                            <a href="{{ route('journal-entries.edit', $entry->id) }}"
                                class="btn btn-icon bg-transparent shadow-none border-0" title="{{ __('Edit') }}">
                                <i class="icon-base ti tabler-pencil"></i>
                            </a>
                        @endcan
                        @can('delete journal entry')
                            <button type="button" class="btn btn-icon bg-transparent shadow-none border-0 delete-entry"
                                data-id="{{ $entry->id }}" title="{{ __('Delete') }}">
                                <i class="icon-base ti tabler-trash"></i>
                            </button>
                        @endcan
                    @endif

                    @if ($entry->status === 'pending' && !$entry->reversed_by_entry_id)
                        @can('approve journal entry')
                            <button type="button"
                                class="btn btn-icon bg-transparent shadow-none border-0 text-success workflow-action"
                                data-action="approve" data-id="{{ $entry->id }}" title="{{ __('Approve') }}">
                                <i class="icon-base ti tabler-thumb-up"></i>
                            </button>
                        @endcan
                        @can('reject journal entry')
                            <button type="button"
                                class="btn btn-icon bg-transparent shadow-none border-0 text-danger workflow-action"
                                data-action="reject" data-id="{{ $entry->id }}" title="{{ __('Reject') }}">
                                <i class="icon-base ti tabler-thumb-down"></i>
                            </button>
                        @endcan
                    @endif

                    @if (
                        ($entry->status === 'approved' || $entry->status === 'posted') &&
                            !$entry->reverses_entry_id &&
                            !$entry->reversed_by_entry_id)
                        {{-- We can add a specific permission for reversing later if needed --}}
                        <a href="{{ route('journal-entries.reverse', $entry->id) }}"
                            class="btn btn-icon bg-transparent shadow-none border-0" title="{{ __('Reverse') }}">
                            <i class="icon-base ti tabler-repeat"></i>
                        </a>
                    @endif
                </div>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="10" class="text-center py-4">
                <div class="empty-state">
                    <i class="icon-base ti tabler-receipt" style="font-size: 3rem; color: #d1d5db;"></i>
                    <h6 class="mt-2">{{ __('No journal entries found') }}</h6>
                    <p class="text-muted">{{ __('Start by creating your first journal entry') }}</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
