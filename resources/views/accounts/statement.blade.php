@extends('layouts.app')

@section('title', __('Account Statement'))

@section('content')
    {!! breadcrumb([['title' => __('Accounting')], ['title' => __('Account Statement')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث والفلاتر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <form id="statementForm" data-action="{{ route('accounts.statement.get') }}"
                                class="d-flex align-items-center flex-wrap gap-2">
                                @csrf

                                <!-- اختيار الحساب -->
                                <label for="account_name" class="form-label mb-0 me-2">{{ __('Account') }}:</label>
                                <input type="text" class="form-control form-control-sm" id="account_name" list="accounts"
                                    placeholder="{{ __('Select an account') }}" style="width: 350px;">
                                <datalist id="accounts">
                                    @foreach ($accounts as $account)
                                        <option value="{{ $account->name }} ({{ $account->code }})"
                                            data-id="{{ $account->id }}"></option>
                                    @endforeach
                                </datalist>
                                <input type="hidden" id="account_id" name="account_id">

                                <!-- فلاتر التاريخ -->
                                <label for="start_date" class="form-label mb-0 ms-3 me-2">{{ __('From') }}:</label>
                                <input type="date" class="form-control form-control-sm" id="start_date" name="start_date"
                                    value="{{ $startDate }}" style="width: 160px;">

                                <label for="end_date" class="form-label mb-0 ms-3 me-2">{{ __('To') }}:</label>
                                <input type="date" class="form-control form-control-sm" id="end_date" name="end_date"
                                    value="{{ $endDate }}" style="width: 160px;">

                                <!-- زر البحث (أيقونة فقط) -->
                                <button type="submit" class="btn p-0 ms-3" style="font-size: 1.3rem;">
                                    <i class="icon-base ti tabler-search"></i>
                                </button>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- يمكن إضافة عناصر أخرى هنا -->
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <div id="statementResults">
                            <!-- Statement details and table will be loaded here via AJAX -->
                            <div class="text-center py-4">
                                <i class="icon-base ti tabler-file-search" style="font-size: 48px; color: #ddd;"></i>
                                <p class="text-muted mt-2">
                                    {{ __('Please select an account and date range to view the statement') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/main_js/account_statement.js') }}"></script>

    <script>
        // إضافة التحقق من البيانات مع SweetAlert قبل إرسال الطلب
        document.addEventListener('DOMContentLoaded', function() {
            const statementForm = document.getElementById('statementForm');
            const accountIdInput = document.getElementById('account_id');
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');

            // إضافة التحقق من صحة البيانات قبل الإرسال
            statementForm.addEventListener('submit', function(e) {
                // التحقق من اختيار الحساب
                if (!accountIdInput.value || accountIdInput.value.trim() === '') {
                    console.log(accountIdInput.value);
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('Attention') }}',
                        text: '{{ __('Please select an account') }}',
                        confirmButtonText: '{{ __('OK') }}'
                    });
                    return false;
                }

                // التحقق من التواريخ (واحد على الأقل مطلوب)
                const hasStartDate = startDateInput.value && startDateInput.value.trim() !== '';
                const hasEndDate = endDateInput.value && endDateInput.value.trim() !== '';

                if (!hasStartDate && !hasEndDate) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    Swal.fire({
                        icon: 'warning',
                        title: '{{ __('Attention') }}',
                        text: '{{ __('Please select at least one date (From or To)') }}',
                        confirmButtonText: '{{ __('OK') }}'
                    });
                    return false;
                }

                // التحقق من أن تاريخ البداية أقل من أو يساوي تاريخ النهاية (فقط إذا كانا موجودين)
                if (hasStartDate && hasEndDate) {
                    const startDate = new Date(startDateInput.value);
                    const endDate = new Date(endDateInput.value);

                    if (startDate > endDate) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        Swal.fire({
                            icon: 'warning',
                            title: '{{ __('Attention') }}',
                            text: '{{ __('Start date must be before or equal to end date') }}',
                            confirmButtonText: '{{ __('OK') }}'
                        });
                        return false;
                    }
                }

                // إذا وصلنا هنا، فالبيانات صحيحة - دع account_statement.js يتولى المعالجة
            }, true); // استخدام capturing phase للتأكد من التنفيذ قبل الملف الخارجي
        });
    </script>
@endpush
