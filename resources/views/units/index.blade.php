@extends('layouts.app')

@section('title', __('Units'))

@section('content')
    {!! breadcrumb([['title' => __('Inventory')], ['title' => __('Units')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                            <select id="perPage" class="form-select form-select-sm" style="width: auto;">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span class="ms-2">{{ __('entries') }}</span>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- البحث -->
                            <div class="d-flex align-items-center">
                                <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search units...') }}">
                                </div>
                            </div>

                            <!-- زر إضافة وحدة جديدة -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Unit') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="unitsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Unit Name') }}</th>
                                    <th>{{ __('English Name') }}</th>
                                    <th>{{ __('Symbol') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('units.partials.table', ['units' => $units])
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container">
                        @if ($units instanceof \Illuminate\Pagination\LengthAwarePaginator && $units->hasPages())
                            <div>
                                {{ $units->links() }}
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add New Unit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createUnitForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="create-unit-name">{{ __('Unit Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="create-unit-name" name="name" class="form-control"
                                    placeholder="{{ __('Unit Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-name-en">{{ __('English Name') }}</label>
                                <input type="text" id="create-name-en" name="name_en" class="form-control"
                                    placeholder="{{ __('English Name') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-symbol">{{ __('Symbol') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="create-symbol" name="symbol" class="form-control"
                                    placeholder="{{ __('Symbol') }}" required maxlength="10">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-notes">{{ __('Notes') }}</label>
                                <textarea id="create-notes" name="notes" class="form-control" rows="3" placeholder="{{ __('Notes') }}"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-status">{{ __('Status') }}</label>
                                <select id="create-status" name="status" class="form-select" required>
                                    <option value="1">{{ __('Active') }}</option>
                                    <option value="0">{{ __('Inactive') }}</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-plus me-1"></i>
                            {{ __('Add Unit') }}
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
                    <h5 class="modal-title">{{ __('Edit Unit') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editUnitForm">
                    @csrf
                    <input type="hidden" id="edit-unit-id" name="unit_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="edit-unit-name">{{ __('Unit Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="edit-unit-name" name="name" class="form-control"
                                    placeholder="{{ __('Unit Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-name-en">{{ __('English Name') }}</label>
                                <input type="text" id="edit-name-en" name="name_en" class="form-control"
                                    placeholder="{{ __('English Name') }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-symbol">{{ __('Symbol') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="edit-symbol" name="symbol" class="form-control"
                                    placeholder="{{ __('Symbol') }}" required maxlength="10">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-notes">{{ __('Notes') }}</label>
                                <textarea id="edit-notes" name="notes" class="form-control" rows="3" placeholder="{{ __('Notes') }}"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-status">{{ __('Status') }}</label>
                                <select id="edit-status" name="status" class="form-select" required>
                                    <option value="1">{{ __('Active') }}</option>
                                    <option value="0">{{ __('Inactive') }}</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-device-floppy me-1"></i>
                            {{ __('Update Unit') }}
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
        // 🔥 تعريف المتغيرات في النطاق العام
        let createModal, editModal;
        let currentPage = 1;

        // 🔥 دالة تنظيف البحث
        function clearSearch() {
            document.getElementById('search').value = '';
            reloadUnitsTable();
        }

        function reloadUnitsTable(page = 1) {
            let search = document.getElementById('search').value.trim();
            let perPage = document.getElementById('perPage').value;

            // 🔥 بناء URL بدون معاملات في الـ URL الرئيسي
            let url = `{{ route('units.search') }}`;
            const params = new URLSearchParams();

            if (search && search !== '') {
                params.append('search', search);
            }

            params.append('perPage', perPage);
            params.append('page', page);

            url += '?' + params.toString();

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('#unitsTable tbody').outerHTML = data.html;

                        // تحديث الـ pagination
                        if (data.pagination) {
                            document.getElementById('pagination-container').innerHTML = data.pagination;
                            attachPaginationListeners();
                        }

                        attachEventListeners();

                        // 🔥 الحفاظ على URL نظيف (بدون معاملات)
                        window.history.replaceState({}, '', '{{ route('units.index') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function attachPaginationListeners() {
            // إضافة event listeners لروابط الـ pagination
            document.querySelectorAll('#pagination-container .pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    reloadUnitsTable(page);
                });
            });
        }

        function attachEventListeners() {
            // Edit buttons
            document.querySelectorAll('.edit-unit').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const unitId = this.getAttribute('data-id');
                    if (unitId) {
                        loadUnitForEdit(unitId);
                    }
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-unit').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const unitId = this.getAttribute('data-id');
                    if (unitId) {
                        deleteUnit(unitId);
                    }
                });
            });

            // Status toggle switches
            document.querySelectorAll('.toggle-status').forEach(toggle => {
                toggle.onchange = null;
                toggle.addEventListener('change', function(e) {
                    const unitId = this.getAttribute('data-id');
                    if (unitId) {
                        toggleUnitStatus(unitId, this);
                    }
                });
            });
        }

        function loadUnitForEdit(unitId) {
            fetch(`{{ url('units') }}/${unitId}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit-unit-id').value = data.unit.id;
                        document.getElementById('edit-unit-name').value = data.unit.name;
                        document.getElementById('edit-name-en').value = data.unit.name_en || '';
                        document.getElementById('edit-symbol').value = data.unit.symbol;
                        document.getElementById('edit-notes').value = data.unit.notes || '';

                        const statusValue = data.unit.status ? '1' : '0';
                        document.getElementById('edit-status').value = statusValue;

                        if (editModal) {
                            editModal.show();
                        }
                    } else {
                        Swal.fire({
                            title: '{{ __('Error') }}',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: '{{ __('Error') }}',
                        text: '{{ __('An error occurred while loading the unit data') }}',
                        icon: 'error'
                    });
                });
        }

        function deleteUnit(unitId) {
            Swal.fire({
                title: "{{ __('Are You Sure') }}",
                text: "{{ __('This unit will be permanently deleted') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, Delete') }}",
                cancelButtonText: "{{ __('Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('units') }}/${unitId}`, {
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
                                    title: "{{ __('Success') }}",
                                    text: data.message,
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    reloadUnitsTable();
                                });
                            } else {
                                Swal.fire({
                                    title: "{{ __('Error') }}",
                                    text: data.message,
                                    icon: "error"
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: "{{ __('Error') }}",
                                text: "{{ __('An error occurred while deleting the unit') }}",
                                icon: "error"
                            });
                        });
                }
            });
        }

        function toggleUnitStatus(unitId, switchElement) {
            const originalState = switchElement.checked;
            switchElement.disabled = true;

            fetch(`{{ url('units') }}/${unitId}/toggle-status`, {
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
                            title: "{{ __('Success') }}",
                            text: data.message,
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            reloadUnitsTable();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to update status');
                    }
                })
                .catch(error => {
                    console.error('Toggle status error:', error);
                    switchElement.checked = !originalState;
                    switchElement.disabled = false;

                    Swal.fire({
                        title: "{{ __('Error') }}",
                        text: 'حدث خطأ أثناء تغيير حالة الوحدة: ' + error.message,
                        icon: "error"
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createUnitForm');
            const editForm = document.getElementById('editUnitForm');

            // تعريف المودالات في النطاق العام
            createModal = new bootstrap.Modal(document.getElementById('createModal'));
            editModal = new bootstrap.Modal(document.getElementById('editModal'));

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

            // Create Unit
            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors(this);

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Adding...') }}';

                    fetch('{{ route('units.store') }}', {
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
                                createForm.reset();
                                clearFormErrors(createForm);
                                createModal.hide();

                                Swal.fire({
                                    title: '{{ __('Success') }}',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                reloadUnitsTable();
                            } else {
                                if (data.errors) {
                                    showFormErrors(createForm, data.errors);
                                } else {
                                    Swal.fire({
                                        title: '{{ __('Error') }}',
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: '{{ __('Error') }}',
                                text: '{{ __('An error occurred while adding the unit') }}',
                                icon: 'error'
                            });
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            // Update Unit
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors(this);

                    const unitId = document.getElementById('edit-unit-id').value;

                    const data = {
                        name: document.getElementById('edit-unit-name').value,
                        name_en: document.getElementById('edit-name-en').value || '',
                        symbol: document.getElementById('edit-symbol').value,
                        notes: document.getElementById('edit-notes').value || '',
                        status: document.getElementById('edit-status').value,
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    };

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}';

                    fetch(`{{ url('units') }}/${unitId}`, {
                            method: 'POST',
                            body: JSON.stringify(data),
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                editForm.reset();
                                clearFormErrors(editForm);
                                editModal.hide();

                                Swal.fire({
                                    title: '{{ __('Success') }}',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                reloadUnitsTable();
                            } else {
                                if (data.errors) {
                                    showFormErrors(editForm, data.errors);
                                } else {
                                    Swal.fire({
                                        title: '{{ __('Error') }}',
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: '{{ __('Error') }}',
                                text: '{{ __('An error occurred while updating the unit') }}',
                                icon: 'error'
                            });
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            // Clear form when modal is hidden
            document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
                if (createForm) {
                    createForm.reset();
                    clearFormErrors(createForm);
                }
            });

            document.getElementById('editModal').addEventListener('hidden.bs.modal', function() {
                if (editForm) {
                    editForm.reset();
                    clearFormErrors(editForm);
                }
            });

            // 🔥 Event listeners للبحث وتغيير عدد العناصر
            let searchTimeout;
            document.getElementById('search').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    reloadUnitsTable();
                }, 300);
            });

            document.getElementById('perPage').addEventListener('change', function() {
                reloadUnitsTable();
            });

            attachEventListeners();
            attachPaginationListeners();
        });
    </script>
@endsection
