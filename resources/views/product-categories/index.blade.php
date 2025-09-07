@extends('layouts.app')

@section('title', __('Product Categories'))

@section('content')
    {!! breadcrumb([['title' => __('Inventory')], ['title' => __('Product Categories')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('product-categories.index') }}"
                                class="d-flex align-items-center">
                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    style="width: auto;" onchange="this.form.submit()">
                                    <option value="5" {{ ($perPage ?? 10) == 5 ? 'selected' : '' }}>5</option>
                                    <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <span class="ms-2">{{ __('entries') }}</span>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- البحث -->
                            <form method="GET" action="{{ route('product-categories.index') }}"
                                class="d-flex align-items-center">
                                <input type="hidden" name="perPage" value="{{ $perPage ?? 10 }}">
                                <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search categories...') }}" value="{{ $search ?? '' }}">
                                    {{-- <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="icon-base ti tabler-search"></i>
                                    </button> --}}
                                </div>
                            </form>

                            <!-- زر إضافة فئة جديدة -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Category') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="categoriesTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Category Name') }}</th>
                                    <th>{{ __('English Name') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('product-categories.partials.table', ['categories' => $categories])
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($categories instanceof \Illuminate\Pagination\LengthAwarePaginator && $categories->hasPages())
                        <div>
                            {{ $categories->appends(request()->query())->links() }}
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
                    <h5 class="modal-title">{{ __('Add New Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createCategoryForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="create-category-name">{{ __('Category Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="create-category-name" name="name" class="form-control"
                                    placeholder="{{ __('Category Name') }}" required>
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
                            {{ __('Add Category') }}
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
                    <h5 class="modal-title">{{ __('Edit Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editCategoryForm">
                    @csrf
                    {{-- @method('PUT') --}}
                    <input type="hidden" id="edit-category-id" name="category_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="edit-category-name">{{ __('Category Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="edit-category-name" name="name" class="form-control"
                                    placeholder="{{ __('Category Name') }}" required>
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
                            {{ __('Update Category') }}
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

        function reloadCategoriesTable() {
            let search = document.getElementById('search').value;
            let perPage = document.getElementById('perPage').value;

            fetch(`{{ route('product-categories.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('#categoriesTable tbody').outerHTML = data.html;
                        attachEventListeners();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function attachEventListeners() {
            // Edit buttons
            document.querySelectorAll('.edit-category').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryId = this.getAttribute('data-id');
                    if (categoryId) {
                        loadCategoryForEdit(categoryId);
                    }
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-category').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const categoryId = this.getAttribute('data-id');
                    if (categoryId) {
                        deleteCategory(categoryId);
                    }
                });
            });

            // Status toggle switches
            document.querySelectorAll('.toggle-status').forEach(toggle => {
                toggle.onchange = null;
                toggle.addEventListener('change', function(e) {
                    const categoryId = this.getAttribute('data-id');
                    if (categoryId) {
                        toggleCategoryStatus(categoryId, this);
                    }
                });
            });
        }

        function loadCategoryForEdit(categoryId) {
            fetch(`{{ url('product-categories') }}/${categoryId}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('edit-category-id').value = data.category.id;
                        document.getElementById('edit-category-name').value = data.category.name;
                        document.getElementById('edit-name-en').value = data.category.name_en || '';
                        document.getElementById('edit-notes').value = data.category.notes || '';

                        const statusValue = data.category.status ? '1' : '0';
                        document.getElementById('edit-status').value = statusValue;

                        // 🔥 استخدام المتغير العام
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
                        text: '{{ __('An error occurred while loading the category data') }}',
                        icon: 'error'
                    });
                });
        }

        function deleteCategory(categoryId) {
            Swal.fire({
                title: "{{ __('Are You Sure') }}",
                text: "{{ __('This category will be permanently deleted') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, Delete') }}",
                cancelButtonText: "{{ __('Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('product-categories') }}/${categoryId}`, {
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
                                    reloadCategoriesTable();
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
                                text: "{{ __('An error occurred while deleting the category') }}",
                                icon: "error"
                            });
                        });
                }
            });
        }

        function toggleCategoryStatus(categoryId, switchElement) {
            const originalState = switchElement.checked;
            switchElement.disabled = true;

            fetch(`{{ url('product-categories') }}/${categoryId}/toggle-status`, {
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
                            reloadCategoriesTable();
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
                        text: 'حدث خطأ أثناء تغيير حالة الفئة: ' + error.message,
                        icon: "error"
                    });
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createCategoryForm');
            const editForm = document.getElementById('editCategoryForm');

            // 🔥 تعريف المودالات في النطاق العام
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

            // Create Category
            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors(this);

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Adding...') }}';

                    fetch('{{ route('product-categories.store') }}', {
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

                                reloadCategoriesTable();
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
                                text: '{{ __('An error occurred while adding the category') }}',
                                icon: 'error'
                            });
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            // Update Category
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors(this);

                    const categoryId = document.getElementById('edit-category-id').value;

                    // 🔥 تجميع البيانات بشكل مختلف
                    const data = {
                        name: document.getElementById('edit-category-name').value,
                        name_en: document.getElementById('edit-name-en').value || '',
                        notes: document.getElementById('edit-notes').value || '',
                        status: document.getElementById('edit-status').value,
                        _token: '{{ csrf_token() }}',
                        _method: 'PUT'
                    };

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}';

                    fetch(`{{ url('product-categories') }}/${categoryId}`, {
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
                                // 🔥 ترتيب العمليات بشكل صحيح
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

                                reloadCategoriesTable();
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
                                text: '{{ __('An error occurred while updating the category') }}',
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

            // Live search
            document.getElementById('search').addEventListener('keyup', function() {
                reloadCategoriesTable();
            });

            attachEventListeners();
        });
    </script>
@endsection
