@extends('layouts.app')

@section('title', __('Cash Vaults'))

@section('content')
    {!! breadcrumb([['title' => __('Main Data')], ['title' => __('Cash Vaults')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('cash-vaults.index') }}" class="d-flex align-items-center">
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
                            <form method="GET" action="{{ route('cash-vaults.index') }}"
                                class="d-flex align-items-center">
                                <input type="hidden" name="perPage" value="{{ $perPage ?? 10 }}">
                                <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search vaults...') }}" value="{{ $search ?? '' }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="icon-base ti tabler-search"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- زر إضافة خزنة جديدة -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Vault') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="vaultsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Vault Name') }}</th>
                                    <th>{{ __('Currency') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('CashVault.partials.table', ['cashVaults' => $cashVaults])
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($cashVaults instanceof \Illuminate\Pagination\LengthAwarePaginator && $cashVaults->hasPages())
                        <div>
                            {{ $cashVaults->appends(request()->query())->links() }}
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
                    <h5 class="modal-title">{{ __('Add New Vault') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createVaultForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="create-vault-name">{{ __('Vault Name') }}</label>
                                <input type="text" id="create-vault-name" name="name" class="form-control"
                                    placeholder="{{ __('Vault Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-currency-id">{{ __('Currency') }}</label>
                                <select id="create-currency-id" name="currency_id" class="form-select" required>
                                    <option value="">{{ __('Choose Currency') }}</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-balance">{{ __('Balance') }}</label>
                                <input type="number" step="0.01" id="create-balance" name="balance" class="form-control"
                                    placeholder="0.00" value="0" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-status">{{ __('Status') }}</label>
                                <select id="create-status" name="status" class="form-select" required>
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="inactive">{{ __('Inactive') }}</option>
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
                            {{ __('Add Vault') }}
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
                    <h5 class="modal-title">{{ __('Edit Vault') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editVaultForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-vault-id" name="vault_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="edit-vault-name">{{ __('Vault Name') }}</label>
                                <input type="text" id="edit-vault-name" name="name" class="form-control"
                                    placeholder="{{ __('Vault Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-currency-id">{{ __('Currency') }}</label>
                                <select id="edit-currency-id" name="currency_id" class="form-select" required>
                                    <option value="">{{ __('Choose Currency') }}</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-balance">{{ __('Balance') }}</label>
                                <input type="number" step="0.01" id="edit-balance" name="balance"
                                    class="form-control" placeholder="0.00" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-status">{{ __('Status') }}</label>
                                <select id="edit-status" name="status" class="form-select" required>
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="inactive">{{ __('Inactive') }}</option>
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
                            {{ __('Update Vault') }}
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
        function reloadVaultsTable() {
            let search = document.getElementById('search').value;
            let perPage = document.getElementById('perPage').value;

            fetch(`{{ route('cash-vaults.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.querySelector('#vaultsTable tbody').outerHTML = data.html;
                    // أعد تفعيل أزرار التعديل والحذف بعد تحديث الجدول إذا لزم الأمر
                });
        }
        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createVaultForm');
            const editForm = document.getElementById('editVaultForm');
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

            // Create Vault
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Adding...') }}';

                fetch('{{ route('cash-vaults.store') }}', {
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
                                title: '{{ __('Success') }}',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                reloadVaultsTable();
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
                            text: '{{ __('An error occurred while adding the vault') }}',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Edit Vault - Load data
            document.querySelectorAll('.edit-vault').forEach(button => {
                button.addEventListener('click', function() {
                    const vaultId = this.getAttribute('data-id');

                    fetch(`{{ url('cash-vaults') }}/${vaultId}/edit`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('edit-vault-id').value = data.vault.id;
                                document.getElementById('edit-vault-name').value = data.vault
                                    .name;
                                document.getElementById('edit-balance').value = data.vault
                                    .balance;
                                // تحويل status من نص إلى رقم
                                document.getElementById('edit-status').value = data.vault
                                    .status;
                                document.getElementById('edit-currency-id').value = data.vault
                                    .currency_id;

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
                                text: '{{ __('An error occurred while loading the vault data') }}',
                                icon: 'error'
                            });
                        });
                });
            });

            // Update Vault
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const vaultId = document.getElementById('edit-vault-id').value;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}';

                fetch(`{{ url('cash-vaults') }}/${vaultId}`, {
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
                                title: '{{ __('Success') }}',
                                text: data.message,
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                reloadVaultsTable();
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
                            text: '{{ __('An error occurred while updating the vault') }}',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Delete Vault
            document.querySelectorAll('.delete-vault').forEach(button => {
                button.addEventListener('click', function() {
                    const vaultId = this.getAttribute('data-id');

                    Swal.fire({
                        title: "{{ __('Are You Sure') }}",
                        text: "{{ __('Confirm Delete Message') }}",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "{{ __('Yes Delete') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('cash-vaults') }}/${vaultId}`, {
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
                                            document.getElementById(
                                                `vault-row-${vaultId}`
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
                                        text: "{{ __('An error occurred while deleting the vault') }}",
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

            // Live search
            document.getElementById('search').addEventListener('keyup', function() {
                let search = this.value;
                let perPage = document.getElementById('perPage').value;

                fetch(`{{ route('cash-vaults.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.querySelector('#vaultsTable tbody').outerHTML = data.html;
                        // أعد تفعيل أزرار التعديل والحذف بعد تحديث الجدول إذا لزم الأمر
                    });
            });
        });
    </script>
@endsection
