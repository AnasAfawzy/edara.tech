<!-- Transactions Table -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Transactions') }} ({{ $transactions->count() }})</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table text-center table-bordered" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th class="text-center">{{ __('Date') }}</th>
                        <th class="text-center">{{ __('Journal Entry') }}</th>
                        <th class="text-center">{{ __('Description') }}</th>
                        <th class="text-center">{{ __('Debit') }}</th>
                        <th class="text-center">{{ __('Credit') }}</th>
                        <th class="text-center">{{ __('Balance') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td class="text-center">
                                {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') }}</td>
                            <td class="text-center">
                                @if ($transaction->journal_entry_id)
                                    <button type="button" class="btn btn-link p-0 view-journal-entry"
                                        data-id="{{ $transaction->journal_entry_id }}"
                                        title="{{ __('View Journal Entry') }}">
                                        <span class="badge bg-primary">
                                            {{ $transaction->journalEntry->entry_number ?? 'JV-' . str_pad($transaction->journal_entry_id, 4, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $transaction->description }}</td>
                            <td class="text-center">{{ number_format($transaction->debit, 2) }}</td>
                            <td class="text-center">{{ number_format($transaction->credit, 2) }}</td>
                            <td class="text-center">{{ number_format($transaction->balance, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">
                                {{ __('No transactions found for the selected period.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-center"><strong>{{ __('Closing Balance') }}</strong></td>
                        <td class="text-center">
                            <strong>
                                @if ($transactions->isEmpty())
                                    {{ number_format($openingBalance, 2) }}
                                @else
                                    @php
                                        // حساب الرصيد الختامي = الرصيد الافتتاحي + صافي الحركات
                                        $lastTransaction = $transactions->last();
                                        $netMovement = $totalDebit - $totalCredit;
                                        $closingBalance = $openingBalance + $netMovement;
                                    @endphp
                                    {{ number_format($closingBalance, 2) }}
                                @endif
                            </strong>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="d-flex justify-content-center">
            {{ $transactions->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<!-- Summary -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Summary') }}</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <p><strong>{{ __('Opening Balance') }}:</strong> {{ number_format($openingBalance, 2) }}</p>
                <p><strong>{{ __('Closing Balance') }}:</strong> {{ number_format($closingBalance, 2) }}</p>
            </div>
            <div class="col-md-4">
                <p><strong>{{ __('Total Debit') }}:</strong> {{ number_format($totalDebit, 2) }}</p>
                <p><strong>{{ __('Total Credit') }}:</strong> {{ number_format($totalCredit, 2) }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Journal Entry View Modal -->
<div class="modal fade" id="journalEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('View Journal Entry') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="journalEntryContent" style="min-height: 500px; padding: 1.5rem;">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
            </div>
        </div>
    </div>
</div>
