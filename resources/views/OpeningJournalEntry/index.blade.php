@extends('layouts.app')

@section('title', __('Opening Journal Entry'))

@section('content')
    {!! breadcrumb([['title' => __('Accounting')], ['title' => __('Opening Journal Entry')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">{{ __('Opening Journal Entry') }}</h5>
                            <div class="d-flex gap-2">
                                @if ($activeFinancialYear && $openingJournalEntry)
                                    <a href="{{ route('opening-journal-entry.report') }}" class="btn btn-info btn-sm">
                                        <i class="icon-base ti tabler-report"></i>
                                        {{ __('View Report') }}
                                    </a>
                                    <a href="{{ route('opening-journal-entry.export') }}" class="btn btn-success btn-sm">
                                        <i class="icon-base ti tabler-download"></i>
                                        {{ __('Export CSV') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if (!$activeFinancialYear)
                            <div class="alert alert-danger">
                                <i class="icon-base ti tabler-alert-circle me-2"></i>
                                {{ __('لا توجد سنة مالية نشطة. يرجى تفعيل سنة مالية أولاً قبل إدخال الأرصدة الافتتاحية.') }}
                            </div>
                        @elseif($activeFinancialYear->is_closed)
                            <div class="alert alert-warning">
                                <i class="icon-base ti tabler-lock me-2"></i>
                                {{ __('السنة المالية النشطة مغلقة. لا يمكن إدخال أو تعديل الأرصدة الافتتاحية.') }}
                            </div>
                        @else
                            <form action="{{ route('opening-journal-entry.store') }}" method="POST" id="openingEntryForm">
                                @csrf

                                {{-- Fixed Entry Information --}}
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Financial Year') }}</label>
                                            <div class="form-control-plaintext">
                                                <span class="badge bg-primary">
                                                    <i class="icon-base ti tabler-calendar-stats me-1"></i>
                                                    {{ $activeFinancialYear->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Entry Date') }}</label>
                                            <div class="form-control-plaintext">
                                                <span class="badge bg-info">
                                                    <i class="icon-base ti tabler-calendar me-1"></i>
                                                    {{ $activeFinancialYear->start_date }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Reference') }}</label>
                                            <div class="form-control-plaintext">
                                                <span class="badge bg-secondary">
                                                    {{ $openingJournalEntry->reference_number ?? 'OB-' . date('Y') . '-001' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label">{{ __('Description') }}</label>
                                            <div class="form-control-plaintext">
                                                <span class="text-muted">
                                                    {{ __('Opening Balance Entry for Financial Year') }}
                                                    {{ $activeFinancialYear->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Display validation errors --}}
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                {{-- Entry Summary --}}
                                @if ($openingJournalEntry)
                                    <div class="alert alert-info">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <strong>{{ __('Total Debit') }}:</strong>
                                                {{ number_format($openingJournalEntry->total_debit, 2) }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>{{ __('Total Credit') }}:</strong>
                                                {{ number_format($openingJournalEntry->total_credit, 2) }}
                                            </div>
                                            <div class="col-md-3">
                                                <strong>{{ __('Status') }}:</strong>
                                                <span class="badge bg-success">
                                                    {{ __('Posted') }}
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <strong>{{ __('Last Updated') }}:</strong>
                                                {{ $openingJournalEntry->updated_at ? $openingJournalEntry->updated_at->format('Y-m-d H:i') : '' }}
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Accounts Chart --}}
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">{{ __('Chart of Accounts') }}</h6>
                                            <div class="d-flex gap-3">
                                                <span class="badge bg-success" id="totalDebit">{{ __('Total Debit') }}:
                                                    0.00</span>
                                                <span class="badge bg-danger" id="totalCredit">{{ __('Total Credit') }}:
                                                    0.00</span>
                                                <span class="badge bg-info" id="difference">{{ __('Difference') }}:
                                                    0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover mb-0" id="accountsTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 15%;">{{ __('Account Code') }}</th>
                                                        <th style="width: 40%;">{{ __('Account Name') }}</th>
                                                        <th style="width: 15%;" class="text-center">{{ __('Debit') }}
                                                        </th>
                                                        <th style="width: 15%;" class="text-center">{{ __('Credit') }}
                                                        </th>
                                                        <th style="width: 15%;">{{ __('Statement') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {{-- عرض الحسابات --}}
                                                    @foreach ($accounts as $account)
                                                        @include(
                                                            'OpeningJournalEntry.partials.account_row',
                                                            [
                                                                'account' => $account,
                                                                'level' => $account->level ?? 0,
                                                                'openingJournalEntryDetailsMapped' => $openingJournalEntryDetailsMapped,
                                                            ]
                                                        )
                                                    @endforeach
                                                </tbody>
                                                <tfoot class="table-light">
                                                    <tr>
                                                        <td colspan="2" class="text-end fw-bold">{{ __('Total') }}:
                                                        </td>
                                                        <td class="text-center fw-bold" id="footerTotalDebit">0.00</td>
                                                        <td class="text-center fw-bold" id="footerTotalCredit">0.00</td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Save Button Only --}}
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-end">
                                            <button type="submit" class="btn btn-primary btn-lg" id="saveButton">
                                                <i class="icon-base ti tabler-device-floppy me-2"></i>
                                                @if ($openingJournalEntry)
                                                    {{ __('Update Opening Balances') }}
                                                @else
                                                    {{ __('Save Opening Balances') }}
                                                @endif
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // العناصر الأساسية
            const debitInputs = document.querySelectorAll('.debit-input');
            const creditInputs = document.querySelectorAll('.credit-input');
            const form = document.getElementById('openingEntryForm');

            // متغيرات للكاش
            let calculationTimeout = null;
            const DEBOUNCE_DELAY = 300; // زيادة التأخير

            /**
             * إرسال النموذج بأقصى سرعة
             */
            function submitFormUltraFast() {
                // إظهار شاشة التحميل البسيطة
                Swal.fire({
                    title: '{{ __('Saving...') }}',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                // تحضير FormData بأقل عمليات ممكنة
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');

                let hasData = false;

                // جمع البيانات بطريقة مُحسنة
                for (let input of debitInputs) {
                    const value = parseFloat(input.value);
                    if (value > 0) {
                        const accountId = input.dataset.accountId;
                        formData.append(`details[${accountId}][debit]`, value.toFixed(2));
                        hasData = true;

                        // إضافة statement إذا وجد
                        const statementInput = document.querySelector(`input[name="details[${accountId}][statement]"]`);
                        if (statementInput) {
                            formData.append(`details[${accountId}][statement]`, statementInput.value || '');
                        }
                    }
                }

                for (let input of creditInputs) {
                    const value = parseFloat(input.value);
                    if (value > 0) {
                        const accountId = input.dataset.accountId;
                        formData.append(`details[${accountId}][credit]`, value.toFixed(2));
                        hasData = true;

                        // إضافة statement إذا لم يتم إضافته من قبل
                        if (!formData.has(`details[${accountId}][statement]`)) {
                            const statementInput = document.querySelector(`input[name="details[${accountId}][statement]"]`);
                            if (statementInput) {
                                formData.append(`details[${accountId}][statement]`, statementInput.value || '');
                            }
                        }
                    }
                }

                if (!hasData) {
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('Warning') }}',
                        text: '{{ __('Please enter at least one debit or credit amount') }}',
                        confirmButtonText: '{{ __('OK') }}'
                    });
                    return;
                }

                // إرسال AJAX بأقل headers ممكنة
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('Success') }}',
                            text: data.message,
                            confirmButtonText: '{{ __('OK') }}'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        throw new Error(data.error || 'Unknown error');
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('Error') }}',
                        text: error.message || '{{ __('Error saving data. Please try again.') }}',
                        confirmButtonText: '{{ __('OK') }}'
                    });
                });
            }

            /**
             * تحديث المجاميع بشكل محسن
             */
            function updateTotalsOptimized() {
                let totalDebit = 0;
                let totalCredit = 0;

                // حساب المجاميع بحلقة واحدة
                for (let input of debitInputs) {
                    if (input.value) totalDebit += parseFloat(input.value) || 0;
                }
                for (let input of creditInputs) {
                    if (input.value) totalCredit += parseFloat(input.value) || 0;
                }

                // تحديث العرض
                const totalDebitBadge = document.getElementById('totalDebit');
                const totalCreditBadge = document.getElementById('totalCredit');
                const differenceBadge = document.getElementById('difference');

                if (totalDebitBadge) {
                    totalDebitBadge.textContent = `{{ __('Total Debit') }}: ${totalDebit.toFixed(2)}`;
                    totalCreditBadge.textContent = `{{ __('Total Credit') }}: ${totalCredit.toFixed(2)}`;
                    differenceBadge.textContent = `{{ __('Difference') }}: ${Math.abs(totalDebit - totalCredit).toFixed(2)}`;
                }

                const footerTotalDebit = document.getElementById('footerTotalDebit');
                const footerTotalCredit = document.getElementById('footerTotalCredit');
                if (footerTotalDebit) {
                    footerTotalDebit.textContent = totalDebit.toFixed(2);
                    footerTotalCredit.textContent = totalCredit.toFixed(2);
                }

                // تحديث زر الحفظ
                const saveButton = document.getElementById('saveButton');
                if (saveButton) {
                    saveButton.disabled = totalDebit === 0 && totalCredit === 0;
                }
            }

            /**
             * تحديث مع debouncing
             */
            function debouncedUpdate() {
                if (calculationTimeout) clearTimeout(calculationTimeout);
                calculationTimeout = setTimeout(updateTotalsOptimized, DEBOUNCE_DELAY);
            }

            // إضافة Event Listeners محسنة
            function addEventListeners() {
                // معالج المدين
                for (let input of debitInputs) {
                    input.addEventListener('input', function() {
                        if (this.value && parseFloat(this.value) > 0) {
                            const accountId = this.dataset.accountId;
                            const creditInput = document.querySelector(`input[name="details[${accountId}][credit]"]`);
                            if (creditInput) creditInput.value = '';
                        }
                        debouncedUpdate();
                    }, { passive: true });
                }

                // معالج الدائن
                for (let input of creditInputs) {
                    input.addEventListener('input', function() {
                        if (this.value && parseFloat(this.value) > 0) {
                            const accountId = this.dataset.accountId;
                            const debitInput = document.querySelector(`input[name="details[${accountId}][debit]"]`);
                            if (debitInput) debitInput.value = '';
                        }
                        debouncedUpdate();
                    }, { passive: true });
                }

                // معالج النموذج
                if (form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        // حساب سريع للمجاميع
                        let totalDebit = 0, totalCredit = 0;
                        for (let input of debitInputs) {
                            if (input.value) totalDebit += parseFloat(input.value) || 0;
                        }
                        for (let input of creditInputs) {
                            if (input.value) totalCredit += parseFloat(input.value) || 0;
                        }

                        if (totalDebit === 0 && totalCredit === 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: '{{ __('Warning') }}',
                                text: '{{ __('Please enter at least one debit or credit amount') }}',
                                confirmButtonText: '{{ __('OK') }}'
                            });
                            return;
                        }

                        // تأكيد سريع
                        Swal.fire({
                            title: '{{ __('Save Opening Balances?') }}',
                            text: `{{ __('Total Debit') }}: ${totalDebit.toFixed(2)} | {{ __('Total Credit') }}: ${totalCredit.toFixed(2)}`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: '{{ __('Yes, save!') }}',
                            cancelButtonText: '{{ __('Cancel') }}'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitFormUltraFast();
                            }
                        });
                    });
                }
            }

            // التهيئة
            addEventListeners();
            updateTotalsOptimized();

            console.log('Opening Journal Entry JS initialized (Ultra Fast Mode)');
        });
    </script>
@endpush
