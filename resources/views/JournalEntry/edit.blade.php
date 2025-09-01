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
                            <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary btn-sm">
                                <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back to List') }}
                            </a>
                        </div>
                    </div>

                    <form id="editEntryForm" enctype="multipart/form-data">
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

                            <!-- Datalists for search -->
                            <datalist id="accountsList">
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->name }} — {{ $account->code }}"
                                        data-id="{{ $account->id }}">
                                @endforeach
                            </datalist>

                            <datalist id="costCentersList">
                                @foreach ($costCenters as $costCenter)
                                    <option value="{{ $costCenter->name }} — {{ $costCenter->code }}"
                                        data-id="{{ $costCenter->id }}">
                                @endforeach
                            </datalist>

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
                                                    <tr class="detail-row">
                                                        <td>
                                                            <input class="form-control account-search" list="accountsList"
                                                                value="{{ $detail->account ? $detail->account->name . ' — ' . $detail->account->code : '' }}"
                                                                required placeholder="{{ __('Search for account') }}">
                                                            <input type="hidden"
                                                                name="details[{{ $index }}][account_id]"
                                                                class="account-id" value="{{ $detail->account_id }}">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control statement-input"
                                                                name="details[{{ $index }}][statement]"
                                                                value="{{ $detail->statement ?? '' }}"
                                                                placeholder="{{ __('Enter statement') }}">
                                                        </td>
                                                        <td>
                                                            <input class="form-control cost-center-search"
                                                                list="costCentersList"
                                                                value="{{ $detail->costCenter ? $detail->costCenter->name . ' — ' . $detail->costCenter->code : '' }}"
                                                                placeholder="{{ __('Search for cost center') }}">
                                                            <input type="hidden"
                                                                name="details[{{ $index }}][cost_center_id]"
                                                                class="cost-center-id"
                                                                value="{{ $detail->cost_center_id }}">
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
                                                                name="details[{{ $index }}][credit]"
                                                                step="0.01" min="0"
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
                                                        <strong>{{ __('Total') }}:</strong>
                                                    </td>
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

                            <!-- Attachments -->
                            <div class="card border mt-4">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ __('Attachments') }}</h6>
                                </div>
                                <div class="card-body">
                                    <input type="file" class="form-control" id="attachments" name="attachments[]"
                                        multiple>
                                    <div class="form-text mb-3">
                                        {{ __('Max file size: 10MB per file. Allowed types: images, PDF.') }}</div>

                                    @if ($attachments->isNotEmpty())
                                        <div class="list-group" id="existingAttachments">
                                            @foreach ($attachments as $attachmentGroup)
                                                @foreach ($attachmentGroup->files as $file)
                                                    <div
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        <a href="{{ route('journal-entries.attachments.download', $file->id) }}"
                                                            target="_blank">
                                                            <i
                                                                class="icon-base ti tabler-file me-2"></i>{{ $file->file_name }}
                                                            ({{ round($file->size / 1024, 2) }} KB)
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-link text-danger p-1 delete-attachment-file"
                                                            title="{{ __('Remove') }}" data-id="{{ $file->id }}">
                                                            <i class="icon-base ti tabler-trash"></i>
                                                        </button>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    @else
                                        <p class="text-muted">{{ __('No attachments yet.') }}</p>
                                    @endif
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
                                <button type="button" class="btn btn-info" id="saveDraftBtn">
                                    <i class="icon-base ti tabler-file-text me-1"></i>{{ __('Save as Draft') }}
                                </button>
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

            function addDetailRow() {
                detailRowCounter++;
                const tableBody = document.getElementById('detailsTableBody');
                const newRow = document.createElement('tr');
                const prevRow = tableBody.querySelector('tr:last-child');
                const statement = prevRow ? prevRow.querySelector('.statement-input').value : '';

                newRow.innerHTML = `
                    <td>
                        <input class="form-control account-search" list="accountsList" placeholder="{{ __('Search for account') }}" required>
                        <input type="hidden" name="details[${detailRowCounter}][account_id]" class="account-id">
                    </td>
                    <td>
                        <input type="text" class="form-control statement-input" name="details[${detailRowCounter}][statement]"
                               value="${statement}" placeholder="{{ __('Enter statement') }}">
                    </td>
                    <td>
                        <input class="form-control cost-center-search" list="costCentersList" placeholder="{{ __('Search for cost center') }}">
                        <input type="hidden" name="details[${detailRowCounter}][cost_center_id]" class="cost-center-id">
                    </td>
                    <td>
                        <input type="number" class="form-control debit-input" name="details[${detailRowCounter}][debit]"
                               step="0.01" min="0" value="" placeholder="0.00">
                    </td>
                    <td>
                        <input type="number" class="form-control credit-input" name="details[${detailRowCounter}][credit]"
                               step="0.01" min="0" value="" placeholder="0.00">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-link text-danger p-1 remove-row" title="{{ __('Remove') }}">
                            <i class="icon-base ti tabler-trash"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(newRow);
                attachRowEventListeners(newRow);
            }

            function calculateTotals() {
                let totalDebit = 0,
                    totalCredit = 0;
                document.querySelectorAll('.debit-input').forEach(input => totalDebit += parseFloat(input.value ||
                    0));
                document.querySelectorAll('.credit-input').forEach(input => totalCredit += parseFloat(input.value ||
                    0));

                document.getElementById('totalDebit').textContent = totalDebit.toFixed(2);
                document.getElementById('totalCredit').textContent = totalCredit.toFixed(2);

                const balanceAlert = document.getElementById('balanceAlert');
                const balanceMessage = document.getElementById('balanceMessage');
                const diff = Math.abs(totalDebit - totalCredit);

                if (diff > 0.01) {
                    balanceAlert.style.display = 'block';
                    balanceAlert.className = 'alert alert-danger mt-3';
                    balanceMessage.textContent =
                        `{{ __('Entry is not balanced. Difference:') }} ${diff.toFixed(2)}`;
                } else if (totalDebit > 0 && totalCredit > 0 && diff < 0.01) {
                    balanceAlert.style.display = 'block';
                    balanceAlert.className = 'alert alert-success mt-3';
                    balanceMessage.textContent = '{{ __('Entry is balanced') }}';
                } else {
                    balanceAlert.style.display = 'none';
                }
            }

            function attachRowEventListeners(row) {
                // Event listeners for search inputs
                row.querySelector('.account-search').addEventListener('input', function() {
                    const value = this.value;
                    const accountIdInput = row.querySelector('.account-id');
                    const option = document.querySelector(`#accountsList option[value="${value}"]`);
                    if (option) {
                        accountIdInput.value = option.dataset.id;
                    } else {
                        accountIdInput.value = '';
                    }
                });

                row.querySelector('.cost-center-search').addEventListener('input', function() {
                    const value = this.value;
                    const costCenterIdInput = row.querySelector('.cost-center-id');
                    const option = document.querySelector(`#costCentersList option[value="${value}"]`);
                    if (option) {
                        costCenterIdInput.value = option.dataset.id;
                    } else {
                        costCenterIdInput.value = '';
                    }
                });

                const debitInput = row.querySelector('.debit-input');
                const creditInput = row.querySelector('.credit-input');

                debitInput.addEventListener('input', function() {
                    if (this.value && this.value > 0) creditInput.value = '';
                    calculateTotals();
                });

                creditInput.addEventListener('input', function() {
                    if (this.value && this.value > 0) debitInput.value = '';
                    calculateTotals();
                });

                row.querySelector('.remove-row').addEventListener('click', function() {
                    if (document.querySelectorAll('#detailsTableBody tr').length > 1) {
                        row.remove();
                        calculateTotals();
                    } else {
                        Swal.fire('{{ __('Warning') }}',
                            '{{ __('At least one detail row is required') }}', 'warning');
                    }
                });
            }

            document.getElementById('addDetailRow').addEventListener('click', addDetailRow);

            document.querySelectorAll('#detailsTableBody tr').forEach(row => {
                attachRowEventListeners(row);
            });

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const totalDebit = parseFloat(document.getElementById('totalDebit').textContent);
                const totalCredit = parseFloat(document.getElementById('totalCredit').textContent);

                if (Math.abs(totalDebit - totalCredit) > 0.01) {
                    Swal.fire('{{ __('Error') }}', '{{ __('Journal entry must be balanced') }}',
                        'error');
                    return;
                }
                if (totalDebit === 0) {
                    Swal.fire('{{ __('Error') }}',
                        '{{ __('Journal entry must have at least one debit and credit entry') }}',
                        'error');
                    return;
                }

                const submitBtn = document.getElementById('submitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    `<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}`;

                // Collect IDs of files to be deleted
                const deletedFileIds = [];
                document.querySelectorAll('#existingAttachments .deleted-file-input').forEach(input => {
                    deletedFileIds.push(input.value);
                });

                const formData = new FormData(form);
                deletedFileIds.forEach(id => {
                    formData.append('deleted_attachments[]', id);
                });

                if (e.submitter && e.submitter.id === 'saveDraftBtn') {
                    formData.append('is_draft', 'true');
                }

                fetch('{{ route('journal-entries.update', $journalEntry->id) }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
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
                                })
                                .then(() => window.location.href =
                                    '{{ route('journal-entries.index') }}');
                        } else {
                            Swal.fire('{{ __('Error') }}', data.message ||
                                '{{ __('An error occurred') }}', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('{{ __('Error') }}',
                            '{{ __('An error occurred while updating') }}', 'error');
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML =
                            `<i class="icon-base ti tabler-device-floppy me-1"></i>{{ __('Update Journal Entry') }}`;
                    });
            });

            // Handle delete attachment file button clicks
            document.querySelectorAll('.delete-attachment-file').forEach(button => {
                button.addEventListener('click', function() {
                    const fileId = this.dataset.id;
                    const listItem = this.closest('.list-group-item');

                    Swal.fire({
                        title: '{{ __('Are you sure?') }}',
                        text: '{{ __('You will not be able to recover this file!') }}',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '{{ __('Yes, delete it!') }}',
                        cancelButtonText: '{{ __('Cancel') }}'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Add a hidden input to mark for deletion on form submission
                            const hiddenInput = document.createElement('input');
                            hiddenInput.type = 'hidden';
                            hiddenInput.name = 'deleted_attachments[]';
                            hiddenInput.value = fileId;
                            hiddenInput.classList.add(
                            'deleted-file-input'); // Add a class to easily find them
                            form.appendChild(hiddenInput);

                            listItem.remove(); // Remove from UI immediately

                            Swal.fire('{{ __('Marked for deletion') }}',
                                '{{ __('File will be deleted upon saving the entry.') }}',
                                'info');
                        }
                    });
                });
            });

            calculateTotals();
        });
    </script>
@endsection
