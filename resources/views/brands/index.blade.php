@extends('layouts.app')

@section('title', __('Brands'))

@section('content')
    {!! breadcrumb([['title' => __('Inventory')], ['title' => __('Brands')]]) !!}

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
                                        placeholder="{{ __('Search brands...') }}">
                                </div>
                            </div>

                            <!-- زر إضافة ماركة جديدة -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Brand') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="brandsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Brand Name') }}</th>
                                    <th>{{ __('English Name') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('brands.partials.table', ['brands' => $brands])
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container">
                        @if ($brands instanceof \Illuminate\Pagination\LengthAwarePaginator && $brands->hasPages())
                            <div>
                                {{ $brands->links() }}
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
                    <h5 class="modal-title">{{ __('Add New Brand') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createBrandForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="create-brand-name">{{ __('Brand Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="create-brand-name" name="name" class="form-control"
                                    placeholder="{{ __('Brand Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-name-en">{{ __('English Name') }}</label>
                                <input type="text" id="create-name-en" name="name_en" class="form-control"
                                    placeholder="{{ __('English Name') }}">
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
                            {{ __('Add Brand') }}
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
                    <h5 class="modal-title">{{ __('Edit Brand') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBrandForm">
                    @csrf
                    <input type="hidden" id="edit-brand-id" name="brand_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="edit-brand-name">{{ __('Brand Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="edit-brand-name" name="name" class="form-control"
                                    placeholder="{{ __('Brand Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-name-en">{{ __('English Name') }}</label>
                                <input type="text" id="edit-name-en" name="name_en" class="form-control"
                                    placeholder="{{ __('English Name') }}">
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
                            {{ __('Update Brand') }}
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
            reloadBrandsTable();
        }

        function reloadBrandsTable(page = 1) {
            let search = document.getElementById('search').value.trim();
            let perPage = document.getElementById('perPage').value;

            // 🔥 بناء URL بدون معاملات في الـ URL الرئيسي
            let url = `{{ route('brands.search') }}`;
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
                        document.querySelector('#brandsTable tbody').outerHTML = data.html;

                        // تحديث الـ pagination
                        if (data.pagination) {
                            document.getElementById('pagination-container').innerHTML = data.pagination;
                            attachPaginationListeners();
                        }

                        attachEventListeners();

                        // 🔥 الحفاظ على URL نظيف (بدون معاملات)
                        window.history.replaceState({}, '', '{{ route('brands.index') }}');
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
                    reloadBrandsTable(page);
                });
            });
        }

        function attachEventListeners() {
            // Edit buttons
            document.querySelectorAll('.edit-brand').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const brandId = this.getAttribute('data-id');
                    if (brandId) {
                        loadBrandForEdit(brandId);
                    }
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-brand').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const brandId = this.getAttribute('data-id');
                    if (brandId) {
                        deleteBrand(brandId);
                    }
                });
            });

            // Status toggle switches
            document.querySelectorAll('.toggle-status').forEach(toggle => {
                toggle.onchange = null;
                toggle.addEventListener('change', function(e) {
                    const brandId = this.getAttribute('data-id');
                    if (brandId) {
                        toggleBrandStatus(brandId, this);
                    }
                });
            });
        }

        function loadBrandForEdit(brandId) {
            fetch(`{{ url('brands') }}/${brandId}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit-brand-id').value = data.brand.id;
                        document.getElementById('edit-brand-name').value = data.brand.name;
                        document.getElementById('edit-name-en').value = data.brand.name_en || '';
                        document.getElementById('edit-notes').value = data.brand.notes || '';

                        const statusValue = data.brand.status ? '1' : '0';
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
                        text: '{{ __('An error occurred while loading the brand data') }}',
                        icon: 'error'
                    });
                });
        }

        function deleteBrand(brandId) {
            Swal.fire({
                title: "{{ __('Are You Sure') }}",
                text: "{{ __('This brand will be permanently deleted') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, Delete') }}",
                cancelButtonText: "{{ __('Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('brands') }}/${brandId}`, {
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
                                    reloadBrandsTable();
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
                                text: "{{ __('An error occurred while deleting the brand') }}",
                                icon: "error"
                            });
                        });
                }
            });
        }

        function toggleBrandStatus(brandId, switchElement) {
            const originalState = switchElement.checked;
            switchElement.disabled = true;

            fetch(`{{ url('brands') }}/${brandId}/toggle-status`, {
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
                            reloadBrandsTable();
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
                        text: 'حدث خطأ أثناء تغيير حالة الماركة: ' + error.message,
                        icon: "error"
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createBrandForm');
            const editForm = document.getElementById('editBrandForm');

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

            // Create Brand
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

                    fetch('{{ route('brands.store') }}', {
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

                                reloadBrandsTable();
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
                                text: '{{ __('An error occurred while adding the brand') }}',
                                icon: 'error'
                            });
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            // Update Brand
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors(this);

                    const brandId = document.getElementById('edit-brand-id').value;

                    const data = {
                        name: document.getElementById('edit-brand-name').value,
                        name_en: document.getElementById('edit-name-en').value || '',
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

                    fetch(`{{ url('brands') }}/${brandId}`, {
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

                                reloadBrandsTable();
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
                                text: '{{ __('An error occurred while updating the brand') }}',
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
                    reloadBrandsTable();
                }, 300);
            });

            document.getElementById('perPage').addEventListener('change', function() {
                reloadBrandsTable();
            });

            attachEventListeners();
            attachPaginationListeners();
        });
    </script>
@endsection
