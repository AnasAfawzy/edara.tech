@extends('layouts.app')

@section('title', __('Banks'))

@section('content')
    {!! breadcrumb([['title' => __('Main Data')], ['title' => __('Banks')]]) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('banks.index') }}" class="d-flex align-items-center">
                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    @if (!empty($showVaultAccountAlert) && $showVaultAccountAlert) disabled @endif style="width: auto;"
                                    onchange="this.form.submit()">
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
                            <form method="GET" action="{{ route('banks.index') }}" class="d-flex align-items-center">
                                <input type="hidden" name="perPage" value="{{ $perPage ?? 10 }}">
                                <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search banks...') }}" value="{{ $search ?? '' }}"
                                        @if (!empty($showVaultAccountAlert) && $showVaultAccountAlert) disabled @endif>
                                    <button class="btn btn-outline-secondary btn-sm" type="submit" @if (!empty($showVaultAccountAlert) && $showVaultAccountAlert) disabled @endif>
                                        <i class="icon-base ti tabler-search"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- زر إضافة بنك جديد -->
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal" @if (!empty($showVaultAccountAlert) && $showVaultAccountAlert) disabled @endif>
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Bank') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="banksTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Bank Name') }}</th>
                                    <th>{{ __('Account Number') }}</th>
                                    <th>{{ __('Currency') }}</th>
                                    <th>{{ __('Balance') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('bank.partials.table', ['banks' => $banks])

                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($banks instanceof \Illuminate\Pagination\LengthAwarePaginator && $banks->hasPages())
                        <div>
                            {{ $banks->appends(request()->query())->links() }}
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
                    <h5 class="modal-title">{{ __('Add New Bank') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createBankForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="create-bank-name">{{ __('Bank Name') }}</label>
                                <input type="text" id="create-bank-name" name="name" class="form-control"
                                    placeholder="{{ __('Bank Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-account-number">{{ __('Account Number') }}</label>
                                <input type="text" id="create-account-number" name="account_number" class="form-control"
                                    placeholder="{{ __('Account Number') }}" required>
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
                                <input type="number" step="0.01" id="create-balance" name="balance"
                                    class="form-control" placeholder="0.00" value="0" required>
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
                            {{ __('Add Bank') }}
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
                    <h5 class="modal-title">{{ __('Edit Bank') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editBankForm">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit-bank-id" name="bank_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="edit-bank-name">{{ __('Bank Name') }}</label>
                                <input type="text" id="edit-bank-name" name="name" class="form-control"
                                    placeholder="{{ __('Bank Name') }}" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-account-number">{{ __('Account Number') }}</label>
                                <input type="text" id="edit-account-number" name="account_number"
                                    class="form-control" placeholder="{{ __('Account Number') }}" required>
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
                            {{ __('Update Bank') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    @if (!empty($showVaultAccountAlert) && $showVaultAccountAlert)
        <script>
            Swal.fire({
                icon: 'warning',
                title: '{{ __('Attention') }}',
                text: '{{ __('Please link the default bank account from accounts settings') }}',
                confirmButtonText: '{{ __('Go to Accounts Settings') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "{{ route('accounting-settings.index') }}";
                }
            });
        </script>
    @endif
    <script>
        function reloadBanksTable() {
            let search = document.getElementById('search').value;
            let perPage = document.getElementById('perPage').value;

            fetch(`{{ route('banks.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.querySelector('#banksTable tbody').outerHTML = data.html;
                    // أعد تفعيل أزرار التعديل والحذف بعد تحديث الجدول
                    attachBankRowEvents();
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createBankForm');
            const editForm = document.getElementById('editBankForm');
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

            // Create Bank
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Adding...') }}';

                fetch('{{ route('banks.store') }}', {
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
                                reloadBanksTable();
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
                            text: '{{ __('An error occurred while adding the bank') }}',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Edit Bank - Load data
            document.querySelectorAll('.edit-bank').forEach(button => {
                button.addEventListener('click', function() {
                    const bankId = this.getAttribute('data-id');

                    fetch(`{{ url('banks') }}/${bankId}/edit`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('edit-bank-id').value = data.bank.id;
                                document.getElementById('edit-bank-name').value = data.bank
                                    .name;
                                document.getElementById('edit-account-number').value = data.bank
                                    .account_number;
                                document.getElementById('edit-currency-id').value = data.bank
                                    .currency_id;
                                document.getElementById('edit-balance').value = data.bank
                                    .balance;
                                document.getElementById('edit-status').value = data.bank.status;

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
                                text: '{{ __('An error occurred while loading the bank data') }}',
                                icon: 'error'
                            });
                        });
                });
            });

            // Update Bank
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const bankId = document.getElementById('edit-bank-id').value;
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                submitBtn.disabled = true;
                submitBtn.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}';

                fetch(`{{ url('banks') }}/${bankId}`, {
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
                                reloadBanksTable();
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
                            text: '{{ __('An error occurred while updating the bank') }}',
                            icon: 'error'
                        });
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // Delete Bank
            document.querySelectorAll('.delete-bank').forEach(button => {
                button.addEventListener('click', function() {
                    const bankId = this.getAttribute('data-id');

                    Swal.fire({
                        title: "{{ __('Are You Sure') }}",
                        text: "{{ __('Confirm Delete Message') }}",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "{{ __('Yes Delete') }}",
                        cancelButtonText: "{{ __('Cancel') }}"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`{{ url('banks') }}/${bankId}`, {
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
                                                `bank-row-${bankId}`
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
                                        text: "{{ __('An error occurred while deleting the bank') }}",
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

            document.getElementById('search').addEventListener('keyup', function() {
                let search = this.value;
                let perPage = document.getElementById('perPage').value;

                fetch(`{{ route('banks.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.querySelector('#banksTable tbody').outerHTML = data.html;
                        // أعد تفعيل أزرار التعديل والحذف بعد تحديث الجدول
                        // (يمكنك نسخ نفس كود تفعيل الأحداث من الأعلى)
                    });
            });
        });
    </script>
@endsection
