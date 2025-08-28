@extends('layouts.app')

@section('title', 'السنوات المالية')

@section('content')
    {!! breadcrumb([['title' => 'البيانات الأساسية'], ['title' => 'السنوات المالية']]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('financial-years.index') }}"
                                class="d-flex align-items-center">
                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                <label for="perPage" class="form-label me-2 mb-0">عرض:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    style="width: auto;" onchange="this.form.submit()">
                                    <option value="5" {{ ($perPage ?? 10) == 5 ? 'selected' : '' }}>5</option>
                                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <span class="ms-2">عنصر</span>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- البحث -->
                            <form method="GET" action="{{ route('financial-years.index') }}"
                                class="d-flex align-items-center">
                                <input type="hidden" name="perPage" value="{{ $perPage ?? 10 }}">
                                <label for="search" class="form-label me-2 mb-0">البحث:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="البحث في السنوات المالية..." value="{{ $search ?? '' }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="icon-base ti tabler-search"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- زر إضافة سنة مالية جديدة -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                إضافة سنة مالية
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="financialYearsTable">
                            <thead>
                                <tr>
                                    <th>اسم السنة المالية</th>
                                    <th>تاريخ البداية</th>
                                    <th>تاريخ النهاية</th>
                                    <th>المدة</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            @include('financial_years.partials.table', [
                                'financialYears' => $financialYears,
                            ])
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($financialYears instanceof \Illuminate\Pagination\LengthAwarePaginator && $financialYears->hasPages())
                        <div>
                            {{ $financialYears->appends(request()->query())->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">إضافة سنة مالية جديدة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createFinancialYearForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="create-name">اسم السنة المالية</label>
                                <input type="text" id="create-name" name="name" class="form-control"
                                    placeholder="مثال: 2024-2025" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-start-date">تاريخ البداية</label>
                                <input type="date" id="create-start-date" name="start_date" class="form-control"
                                    required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-end-date">تاريخ النهاية</label>
                                <input type="date" id="create-end-date" name="end_date" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-plus me-1"></i>
                            إضافة السنة المالية
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">تعديل السنة المالية</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editFinancialYearForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-financial-year-id" name="financial_year_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="edit-name">اسم السنة المالية</label>
                                <input type="text" id="edit-name" name="name" class="form-control"
                                    placeholder="مثال: 2024-2025" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-start-date">تاريخ البداية</label>
                                <input type="date" id="edit-start-date" name="start_date" class="form-control"
                                    required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-end-date">تاريخ النهاية</label>
                                <input type="date" id="edit-end-date" name="end_date" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-device-floppy me-1"></i>
                            تحديث السنة المالية
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        function reloadFinancialYearsTable() {
            let search = document.getElementById('search').value;
            let perPage = document.getElementById('perPage').value;

            fetch(`{{ route('financial-years.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.querySelector('#financialYearsTable tbody').outerHTML = data.html;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createFinancialYearForm');
            const editForm = document.getElementById('editFinancialYearForm');
            const createModal = new bootstrap.Modal(document.getElementById('createModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));

            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

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

            // Auto generate name when dates change
            function updateName() {
                const startDate = document.getElementById('create-start-date').value;
                const endDate = document.getElementById('create-end-date').value;
                const nameInput = document.getElementById('create-name');

                if (startDate && endDate) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const startYear = start.getFullYear();
                    const endYear = end.getFullYear();

                    if (!nameInput.value || nameInput.value.includes('-')) {
                        nameInput.value = `${startYear}-${endYear}`;
                    }
                }
            }

            document.getElementById('create-start-date').addEventListener('change', updateName);
            document.getElementById('create-end-date').addEventListener('change', updateName);

            // Create Financial Year
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>جاري الإضافة...';

                fetch('{{ route('financial-years.store') }}', {
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
                            createModal.hide();
                            createForm.reset();

                            Swal.fire({
                                title: 'نجح',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                reloadFinancialYearsTable();
                            });
                        } else {
                            if (data.errors) {
                                showFormErrors(createForm, data.errors);
                            } else {
                                Swal.fire({
                                    title: 'خطأ',
                                    text: data.message,
                                    icon: 'error'
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'خطأ',
                            text: 'حدث خطأ أثناء إضافة السنة المالية',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Edit Financial Year - Load data
            document.addEventListener('click', function(e) {
                if (e.target.closest('.edit-financial-year')) {
                    const button = e.target.closest('.edit-financial-year');
                    const financialYearId = button.getAttribute('data-id');

                    fetch(`{{ url('financial-years') }}/${financialYearId}/edit`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.financialYear.id) {
                                document.getElementById('edit-financial-year-id').value = data
                                    .financialYear.id;
                                document.getElementById('edit-name').value = data.financialYear.name;
                                // Extract only the date part (YYYY-MM-DD) from ISO string
                                document.getElementById('edit-start-date').value = data.financialYear
                                    .start_date ? data.financialYear.start_date.substring(0, 10) : '';
                                document.getElementById('edit-end-date').value = data.financialYear
                                    .end_date ? data.financialYear.end_date.substring(0, 10) : '';

                                editModal.show();
                            } else {
                                Swal.fire({
                                    title: 'خطأ',
                                    text: data.message || 'تعذر تحميل بيانات السنة المالية',
                                    icon: 'error'
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'خطأ',
                                text: 'حدث خطأ أثناء تحميل بيانات السنة المالية',
                                icon: 'error'
                            });
                        });
                }
            });

            // Update Financial Year
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const financialYearId = document.getElementById('edit-financial-year-id').value;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>جاري التحديث...';

                fetch(`{{ url('financial-years') }}/${financialYearId}`, {
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
                            editModal.hide();

                            Swal.fire({
                                title: 'نجح',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                reloadFinancialYearsTable();
                            });
                        } else {
                            if (data.errors) {
                                showFormErrors(editForm, data.errors);
                            } else {
                                Swal.fire({
                                    title: 'خطأ',
                                    text: data.message,
                                    icon: 'error'
                                });
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            title: 'خطأ',
                            text: 'حدث خطأ أثناء تحديث السنة المالية',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Delete Financial Year
            document.addEventListener('click', function(e) {
                if (e.target.closest('.delete-financial-year')) {
                    const button = e.target.closest('.delete-financial-year');
                    const financialYearId = button.getAttribute('data-id');

                    Swal.fire({
                        title: "هل أنت متأكد؟",
                        text: "تأكيد رسالة الحذف",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "نعم احذف",
                        cancelButtonText: "إلغاء"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('financial-years') }}/${financialYearId}`, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: "نجح",
                                            text: data.message,
                                            icon: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            reloadFinancialYearsTable();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "خطأ",
                                            text: data.message,
                                            icon: "error"
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: "خطأ",
                                        text: "حدث خطأ أثناء حذف السنة المالية",
                                        icon: "error"
                                    });
                                });
                        }
                    });
                }
            });

            // Activate Financial Year
            document.addEventListener('click', function(e) {
                if (e.target.closest('.activate-financial-year')) {
                    const button = e.target.closest('.activate-financial-year');
                    const financialYearId = button.getAttribute('data-id');

                    Swal.fire({
                        title: "تفعيل السنة المالية",
                        text: "هل أنت متأكد من تفعيل هذه السنة المالية؟",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "نعم فعل",
                        cancelButtonText: "إلغاء"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('financial-years') }}/${financialYearId}/activate`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: "نجح",
                                            text: data.message,
                                            icon: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            reloadFinancialYearsTable();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "خطأ",
                                            text: data.message,
                                            icon: "error"
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: "خطأ",
                                        text: "حدث خطأ أثناء تفعيل السنة المالية",
                                        icon: "error"
                                    });
                                });
                        }
                    });
                }
            });

            // Close Financial Year
            document.addEventListener('click', function(e) {
                if (e.target.closest('.close-financial-year')) {
                    const button = e.target.closest('.close-financial-year');
                    const financialYearId = button.getAttribute('data-id');

                    Swal.fire({
                        title: "إغلاق السنة المالية",
                        text: "هل أنت متأكد من إغلاق هذه السنة المالية؟ لن تتمكن من التراجع عن هذا الإجراء.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "نعم أغلق",
                        cancelButtonText: "إلغاء"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('financial-years') }}/${financialYearId}/close`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            title: "نجح",
                                            text: data.message,
                                            icon: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            reloadFinancialYearsTable();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: "خطأ",
                                            text: data.message,
                                            icon: "error"
                                        });
                                    }
                                })
                                .catch(error => {
                                    console.error('Error:', error);
                                    Swal.fire({
                                        title: "خطأ",
                                        text: "حدث خطأ أثناء إغلاق السنة المالية",
                                        icon: "error"
                                    });
                                });
                        }
                    });
                }
            });

            // Clear form when modal is hidden
            document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
                createForm.reset();
                clearFormErrors(createForm);
            });

            document.getElementById('editModal').addEventListener('hidden.bs.modal', function() {
                editForm.reset();
                clearFormErrors(editForm);
            });

            // Live search
            document.getElementById('search').addEventListener('keyup', function() {
                let search = this.value;
                let perPage = document.getElementById('perPage').value;

                fetch(`{{ route('financial-years.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.querySelector('#financialYearsTable tbody').outerHTML = data.html;
                    });
            });
        });
    </script>
@endsection
