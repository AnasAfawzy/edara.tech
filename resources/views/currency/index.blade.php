@extends('layouts.app')

@section('title', __('Currency'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('currencies.index') }}" class="d-flex align-items-center">
                                <input type="hidden" name="search" value="{{ $search }}">
                                <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    style="width: auto;" onchange="this.form.submit()">
                                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <span class="ms-2">{{ __('entries') }}</span>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- أزرار التصدير -->
                            <div class="dropdown">
                                <button class="btn btn-success btn-sm dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="icon-base ti tabler-download me-1"></i>
                                    {{ __('Export') }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('currencies.export.excel', ['search' => $search]) }}">
                                            <i class="icon-base ti tabler-file-type-xls me-2"></i>
                                            {{ __('Export to Excel') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('currencies.export.csv', ['search' => $search]) }}">
                                            <i class="icon-base ti tabler-file-type-csv me-2"></i>
                                            {{ __('Export to CSV') }}
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ route('currencies.export.pdf', ['search' => $search]) }}">
                                            <i class="icon-base ti tabler-file-type-pdf me-2"></i>
                                            {{ __('Export to PDF') }}
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            <!-- البحث -->
                            <form method="GET" action="{{ route('currencies.index') }}"
                                class="d-flex align-items-center">
                                <input type="hidden" name="perPage" value="{{ $perPage }}">
                                <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search currencies...') }}" value="{{ $search }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="icon-base ti tabler-search"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- زر إضافة عملة جديدة -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Create Currency') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="datatables-basic table" id="currenciesTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('Currency Name') }}</th>
                                    <th>{{ __('Currency Code') }}</th>
                                    <th>{{ __('Exchange Rate') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($currencies as $currency)
                                    <tr id="currency-row-{{ $currency->id }}">
                                        <td>{{ $currency->id }}</td>
                                        <td>
                                            <div class="d-flex justify-content-start align-items-center">
                                                <div class="avatar-wrapper">
                                                    <div class="avatar me-2">
                                                        <span class="avatar-initial rounded-circle bg-label-primary">
                                                            {{ strtoupper(substr($currency->code, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span
                                                        class="emp_name text-truncate text-heading fw-medium">{{ $currency->name }}</span>
                                                    <small class="emp_post text-truncate">Currency</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">{{ $currency->code }}</span>
                                        </td>
                                        <td>{{ number_format($currency->exchange_rate, 4) }}</td>
                                        <td>
                                            <span class="badge bg-label-success">{{ __('Active') }}</span>
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-currency"
                                                data-id="{{ $currency->id }}" title="Edit Currency">
                                                <i class="icon-base ti tabler-pencil"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-currency"
                                                data-id="{{ $currency->id }}" title="Delete Currency">
                                                <i class="icon-base ti tabler-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <div class="empty-state">
                                                <i class="icon-base ti tabler-search fs-1 text-muted mb-3"></i>
                                                <h5 class="text-muted">{{ __('No currencies found') }}</h5>
                                                <p class="text-muted">{{ __('Try adjusting your search criteria') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($currencies->hasPages())
                        <div>
                            {{ $currencies->appends(request()->query())->links() }}
                        </div>

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
                    <h5 class="modal-title">{{ __('Create New Currency') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="create-name">{{ __('Currency Name') }}</label>
                                <input type="text" id="create-name" name="name" class="form-control"
                                    placeholder="US Dollar" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-code">{{ __('Currency Code') }}</label>
                                <input type="text" id="create-code" name="code" class="form-control"
                                    placeholder="USD" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-rate">{{ __('Exchange Rate') }}</label>
                                <input type="number" step="0.0001" id="create-rate" name="exchange_rate"
                                    class="form-control" placeholder="1.0000" value="1" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-plus me-1"></i>
                            {{ __('Create Currency') }}
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
                    <h5 class="modal-title">{{ __('Edit Currency') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-currency-id" name="currency_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="edit-name">{{ __('Currency Name') }}</label>
                                <input type="text" id="edit-name" name="name" class="form-control"
                                    placeholder="US Dollar" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-code">{{ __('Currency Code') }}</label>
                                <input type="text" id="edit-code" name="code" class="form-control"
                                    placeholder="USD" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-rate">{{ __('Exchange Rate') }}</label>
                                <input type="number" step="0.0001" id="edit-rate" name="exchange_rate"
                                    class="form-control" placeholder="1.0000" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-device-floppy me-1"></i>
                            {{ __('Update Currency') }}
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
        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createForm');
            const editForm = document.getElementById('editForm');
            const createModal = new bootstrap.Modal(document.getElementById('createModal'));
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));

            // Clear form validation errors
            function clearFormErrors(form) {
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            }

            // Show form validation errors
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

            // Create Currency
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Adding...') }}';

                fetch('{{ route('currencies.store') }}', {
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
                                title: '{{ __('success') }}',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
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
                            text: '{{ __('An error occurred while adding the currency') }}',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Edit Currency - Load data
            document.querySelectorAll('.edit-currency').forEach(button => {
                button.addEventListener('click', function() {
                    const currencyId = this.getAttribute('data-id');

                    fetch(`{{ url('currencies') }}/${currencyId}/edit`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('edit-currency-id').value = data
                                    .currency.id;
                                document.getElementById('edit-name').value = data.currency.name;
                                document.getElementById('edit-code').value = data.currency.code;
                                document.getElementById('edit-rate').value = data.currency
                                    .exchange_rate;

                                editModal.show();
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
                                text: '{{ __('An error occurred while loading the currency data') }}',
                                icon: 'error'
                            });
                        });
                });
            });

            // Update Currency
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const currencyId = document.getElementById('edit-currency-id').value;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}';

                fetch(`{{ url('currencies') }}/${currencyId}`, {
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
                                title: '{{ __('success') }}',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
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
                            text: '{{ __('An error occurred while updating the currency') }}',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Delete Currency
            document.querySelectorAll('.delete-currency').forEach(button => {
                button.addEventListener('click', function() {
                    const currencyId = this.getAttribute('data-id');

                    Swal.fire({
                        title: "{{ __('Are You Sure') }}",
                        text: "{{ __('Confirm Delete Message') }}",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "{{ __('Yes Delete') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('currencies') }}/${currencyId}`, {
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
                                            title: "{{ __('success') }}",
                                            text: data.message,
                                            icon: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            document.getElementById(
                                                `currency-row-${currencyId}`
                                            ).remove();
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
                                        text: "{{ __('An error occurred while deleting the currency') }}",
                                        icon: "error"
                                    });
                                });
                        }
                    });
                });
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
        });
    </script>
@endsection
