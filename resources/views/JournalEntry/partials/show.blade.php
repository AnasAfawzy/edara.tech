<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('Entry Information') }}</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>{{ __('Entry Number') }}:</strong></td>
                                <td>
                                    <span
                                        class="badge bg-primary">{{ $journalEntry->entry_number ?? 'JV-' . str_pad($journalEntry->id, 4, '0', STR_PAD_LEFT) }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Date') }}:</strong></td>
                                <td>{{ \Carbon\Carbon::parse($journalEntry->entry_date)->format('Y-m-d') }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Currency') }}:</strong></td>
                                <td>
                                    <span class="badge bg-info">{{ $journalEntry->currency->name ?? 'N/A' }}
                                        ({{ $journalEntry->currency->code ?? 'N/A' }})</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td style="width: 40%;"><strong>{{ __('Financial Year') }}:</strong></td>
                                <td>{{ $journalEntry->financialYear->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Source Type') }}:</strong></td>
                                <td>
                                    <span
                                        class="badge {{ $journalEntry->source_type === 'manual' ? 'bg-secondary' : 'bg-warning' }}">
                                        {{ $journalEntry->source_type_display ?? ucfirst($journalEntry->source_type ?? 'Manual') }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('Created At') }}:</strong></td>
                                <td>{{ $journalEntry->created_at ? $journalEntry->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Description ثانياً -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('Description') }}</h6>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $journalEntry->description }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Entry Details ثالثاً -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">{{ __('Entry Details') }}</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">{{ __('Account') }}</th>
                                <th style="width: 25%;">{{ __('Statement') }}</th>
                                <th style="width: 20%;">{{ __('Cost Center') }}</th>
                                <th style="width: 15%;" class="text-end">{{ __('Debit') }}</th>
                                <th style="width: 15%;" class="text-end">{{ __('Credit') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($journalEntry->details as $detail)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong>{{ $detail->account->code ?? 'N/A' }}</strong>
                                            <small class="text-muted">{{ $detail->account->name ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-muted">
                                            {{ $detail->statement ?? '-' }}
                                        </div>
                                    </td>
                                    <td>
                                        @if ($detail->costCenter)
                                            <div class="d-flex flex-column">
                                                <strong>{{ $detail->costCenter->code }}</strong>
                                                <small class="text-muted">{{ $detail->costCenter->name }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($detail->debit > 0)
                                            <strong
                                                class="text-success">{{ number_format($detail->debit, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($detail->credit > 0)
                                            <strong
                                                class="text-danger">{{ number_format($detail->credit, 2) }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3">
                                        <i class="icon-base ti tabler-inbox"
                                            style="font-size: 2rem; color: #d1d5db;"></i>
                                        <p class="mb-0 text-muted">{{ __('No details found') }}</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($journalEntry->details->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="3" class="text-end">{{ __('Total') }}</th>
                                    <th class="text-end">
                                        <strong
                                            class="text-success">{{ number_format($journalEntry->details->sum('debit'), 2) }}</strong>
                                    </th>
                                    <th class="text-end">
                                        <strong
                                            class="text-danger">{{ number_format($journalEntry->details->sum('credit'), 2) }}</strong>
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Balance Summary رابعاً -->
@if ($journalEntry->details->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="d-flex flex-column">
                            <strong>{{ __('Total Debit') }}</strong>
                            <span
                                class="h5 text-success mb-0">{{ number_format($journalEntry->details->sum('debit'), 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column">
                            <strong>{{ __('Total Credit') }}</strong>
                            <span
                                class="h5 text-danger mb-0">{{ number_format($journalEntry->details->sum('credit'), 2) }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex flex-column">
                            <strong>{{ __('Balance Status') }}</strong>
                            @php
                                $balance = $journalEntry->details->sum('debit') - $journalEntry->details->sum('credit');
                            @endphp
                            <span class="h5 mb-0 {{ abs($balance) < 0.01 ? 'text-success' : 'text-danger' }}">
                                @if (abs($balance) < 0.01)
                                    <i class="icon-base ti tabler-check-circle me-1"></i>{{ __('Balanced') }}
                                @else
                                    <i class="icon-base ti tabler-alert-circle me-1"></i>{{ __('Unbalanced') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
