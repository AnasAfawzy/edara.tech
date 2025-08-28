@extends('layouts.app')

@section('title', __('Edit Journal Entry'))

@section('content')
    {!! breadcrumb([
        ['title' => __('Accounting')],
        ['title' => __('Journal Entries'), 'url' => route('journal-entries.index')],
        ['title' => __('Edit Entry')],
    ]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Edit Journal Entry') }}</h5>
                        <div class="btn-group">
                            <a href="{{ route('journal-entries.show', $journalEntry->id) }}" class="btn btn-info btn-sm">
                                <i class="icon-base ti tabler-eye me-1"></i>{{ __('View') }}
                            </a>
                            <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary btn-sm">
                                <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back to List') }}
                            </a>
                        </div>
                    </div>

                    <form id="editEntryForm">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="entry-date" class="form-label">{{ __('Date') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="entry-date" name="entry_date"
                                        value="{{ $journalEntry->entry_date->format('Y-m-d') }}" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label for="currency-id" class="form-label">{{ __('Currency') }} <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="currency-id" name="currency_id" required>
                                        <option value="">{{ __('Select Currency') }}</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}"
                                                {{ $journalEntry->currency_id == $currency->id ? 'selected' : '' }}>
                                                {{ $currency->name }} ({{ $currency->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <label for="description" class="form-label">{{ __('Description') }} <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required
                                        placeholder="{{ __('Enter journal entry description') }}">{{ $journalEntry->description }}</textarea>
                                    <div class="invalid-feedback"></div>
                                </div>
                            </div>

                            <!-- Journal Entry Details -->
                            <div class="card border">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">{{ __('Journal Entry Details') }}</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="addDetailRow">
                                        <i class="icon-base ti tabler-plus me-1"></i>{{ __('Add Row') }}
                                    </button>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered mb-0" id="detailsTable">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 25%;">{{ __('Account') }}</th>
                                                    <th style="width: 25%;">{{ __('Statement') }}</th>
                                                    <th style="width: 20%;">{{ __('Cost Center') }}</th>
                                                    <th style="width: 12%;">{{ __('Debit') }}</th>
                                                    <th style="width: 12%;">{{ __('Credit') }}</th>
                                                    <th style="width: 6%;">{{ __('Action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody id="detailsTableBody">
                                                @foreach ($journalEntry->details as $index => $detail)
                                                    <tr>
                                                        <td>
                                                            <select class="form-select account-select"
                                                                name="details[{{ $index }}][account_id]" required>
                                                                <option value="">{{ __('Select Account') }}</option>
                                                                @foreach ($accounts as $account)
                                                                    <option value="{{ $account->id }}"
                                                                        {{ $detail->account_id == $account->id ? 'selected' : '' }}>
                                                                        {{ $account->code }} - {{ $account->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control statement-input"
                                                                name="details[{{ $index }}][statement]"
                                                                value="{{ $detail->statement ?? '' }}"
                                                                placeholder="{{ __('Enter statement') }}">
                                                        </td>
                                                        <td>
                                                            <select class="form-select cost-center-select"
                                                                name="details[{{ $index }}][cost_center_id]">
                                                                <option value="">{{ __('Select Cost Center') }}
                                                                </option>
                                                                @foreach ($costCenters as $costCenter)
                                                                    <option value="{{ $costCenter->id }}"
                                                                        {{ $detail->cost_center_id == $costCenter->id ? 'selected' : '' }}>
                                                                        {{ $costCenter->code }} - {{ $costCenter->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control debit-input"
                                                                name="details[{{ $index }}][debit]" step="0.01"
                                                                min="0"
                                                                value="{{ $detail->debit > 0 ? $detail->debit : '' }}"
                                                                placeholder="0.00">
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control credit-input"
                                                                name="details[{{ $index }}][credit]" step="0.01"
                                                                min="0"
                                                                value="{{ $detail->credit > 0 ? $detail->credit : '' }}"
                                                                placeholder="0.00">
                                                        </td>
                                                        <td class="text-center">
                                                            <button type="button"
                                                                class="btn btn-link text-danger p-1 remove-row"
                                                                title="{{ __('Remove') }}">
                                                                <i class="icon-base ti tabler-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-secondary">
                                                <tr>
                                                    <td colspan="3" class="text-end">
                                                        <strong>{{ __('Total') }}:</strong></td>
                                                    <td class="text-end"><strong
                                                            id="totalDebit">{{ number_format($journalEntry->details->sum('debit'), 2) }}</strong>
                                                    </td>
                                                    <td class="text-end"><strong
                                                            id="totalCredit">{{ number_format($journalEntry->details->sum('credit'), 2) }}</strong>
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-info mt-3" id="balanceAlert" style="display: none;">
                                <i class="icon-base ti tabler-info-circle me-2"></i>
                                <span id="balanceMessage"></span>
                            </div>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary">
                                    {{ __('Cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="icon-base ti tabler-device-floppy me-1"></i>{{ __('Update Journal Entry') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('editEntryForm');
            let detailRowCounter = {{ count($journalEntry->details) }};

            // Clear form errors
            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            // Show form errors
            function showFormErrors(form, errors) {
                Object.keys(errors).forEach(key => {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        const feedback = input.nextElementSibling;
                        if (feedback && feedback.classList.contains('invalid-feedback')) {
                            feedback.textContent = errors[key][0];
                        }
                    }
                });
            }

            // Add detail row function (same as create page)
            function addDetailRow(account = '', statement = '', costCenter = '', debit = '', credit = '') {
                detailRowCounter++;
                const tableBody = document.getElementById('detailsTableBody');
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <select class="form-select account-select" name="details[${detailRowCounter}][account_id]" required>
                            <option value="">{{ __('Select Account') }}</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="text" class="form-control statement-input" name="details[${detailRowCounter}][statement]"
                               value="${statement}" placeholder="{{ __('Enter statement') }}">
                    </td>
                    <td>
                        <select class="form-select cost-center-select" name="details[${detailRowCounter}][cost_center_id]">
                            <option value="">{{ __('Select Cost Center') }}</option>
                            @foreach ($costCenters as $costCenter)
                                <option value="{{ $costCenter->id }}">{{ $costCenter->code }} - {{ $costCenter->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control debit-input" name="details[${detailRowCounter}][debit]"
                               step="0.01" min="0" value="${debit}" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="form-control credit-input" name="details[${detailRowCounter}][credit]"
                               step="0.01" min="0" value="${credit}" placeholder="0.00">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-link text-danger p-1 remove-row" title="{{ __('Remove') }}">
                            <i class="icon-base ti tabler-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(row);

                // Add event listeners
                const debitInput = row.querySelector('.debit-input');
                const creditInput = row.querySelector('.credit-input');

                debitInput.addEventListener('input', function() {
                    if (this.value && this.value > 0) {
                        creditInput.value = '';
                    }
                    calculateTotals();
                });

                creditInput.addEventListener('input', function() {
                    if (this.value && this.value > 0) {
                        debitInput.value = '';
                    }
                    calculateTotals();
                });

                row.querySelector('.remove-row').addEventListener('click', function() {
                    if (tableBody.children.length > 1) {
                        row.remove();
                        calculateTotals();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: '{{ __('Warning') }}',
                            text: '{{ __('At least one detail row is required') }}'
                        });
                    }
                });

                calculateTotals();
            }

            // Calculate totals (same as create page)
            function calculateTotals() {
                let totalDebit = 0;
                let totalCredit = 0;

                document.querySelectorAll('.debit-input').forEach(input => {
                    if (input.value) {
                        totalDebit += parseFloat(input.value);
                    }
                });

                document.querySelectorAll('.credit-input').forEach(input => {
                    if (input.value) {
                        totalCredit += parseFloat(input.value);
                    }
                });

                document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
                document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);

                // Check balance
                const balanceAlert = document.getElementById('balanceAlert');
                const balanceMessage = document.getElementById('balanceMessage');

                if (totalDebit !== totalCredit) {
                    balanceAlert.style.display = 'block';
                    balanceAlert.className = 'alert alert-danger mt-3';
                    balanceMessage.textContent =
                        `{{ __('Entry is not balanced. Difference:') }} ${Math.abs(totalDebit - totalCredit).toFixed(2)}`;
                } else if (totalDebit > 0 && totalCredit > 0) {
                    balanceAlert.style.display = 'block';
                    balanceAlert.className = 'alert alert-success mt-3';
                    balanceMessage.textContent = '{{ __('Entry is balanced') }}';
                } else {
                    balanceAlert.style.display = 'none';
                }
            }

            // Add detail row button
            document.getElementById('addDetailRow').addEventListener('click', function() {
                addDetailRow();
            });

            // Attach events to existing rows
            document.querySelectorAll('.debit-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const creditInput = row.querySelector('.credit-input');
                    if (this.value && this.value > 0) {
                        creditInput.value = '';
                    }
                    calculateTotals();
                });
            });

            document.querySelectorAll('.credit-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('tr');
                    const debitInput = row.querySelector('.debit-input');
                    if (this.value && this.value > 0) {
                        debitInput.value = '';
                    }
                    calculateTotals();
                });
            });

            document.querySelectorAll('.remove-row').forEach(button => {
                button.addEventListener('click', function() {
                    const tableBody = document.getElementById('detailsTableBody');
                    if (tableBody.children.length > 1) {
                        this.closest('tr').remove();
                        calculateTotals();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: '{{ __('Warning') }}',
                            text: '{{ __('At least one detail row is required') }}'
                        });
                    }
                });
            });

            // Form submission
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                // Validate balance
                const totalDebit = parseFloat(document.getElementById('totalDebit').textContent);
                const totalCredit = parseFloat(document.getElementById('totalCredit').textContent);

                if (totalDebit !== totalCredit) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('Error') }}',
                        text: '{{ __('Journal entry must be balanced') }}'
                    });
                    return;
                }

                if (totalDebit === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('Error') }}',
                        text: '{{ __('Journal entry must have at least one debit and credit entry') }}'
                    });
                    return;
                }

                const formData = new FormData(this);
                const submitBtn = document.getElementById('submitBtn');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}';

                fetch('{{ route('journal-entries.update', $journalEntry->id) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '{{ __('Success') }}',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = '{{ route('journal-entries.index') }}';
                            });
                        } else {
                            if (data.errors) {
                                showFormErrors(form, data.errors);
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: '{{ __('Error') }}',
                                    text: data.message || '{{ __('An error occurred') }}'
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('Error') }}',
                            text: '{{ __('An error occurred while updating') }}'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Initial calculation
            calculateTotals();
        });
    </script>
@endsection
