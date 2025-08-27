@extends('layouts.app')

@section('title', __('Roles'))

@section('content')

    {!! breadcrumb([['title' => __('Settings')], ['title' => __('Roles')]]) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر وأزرار الإضافة -->
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('roles.index') }}" class="d-flex align-items-center">
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
                            <!-- البحث -->
                            <form method="GET" action="{{ route('roles.index') }}" class="d-flex align-items-center"
                                id="searchForm">
                                <input type="hidden" name="perPage" value="{{ $perPage }}">
                                <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search for role...') }}" value="{{ $search }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="icon-base ti tabler-search"></i>
                                    </button>
                                </div>
                            </form>
                            @can('create roles')
                                <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                                    <i class="icon-base ti tabler-plus me-1"></i>
                                    {{ __('Add Role') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-datatable table-responsive pt-0">
                        <table class="table table-striped text-center align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('Role') }}</th>
                                    <th>{{ __('Permissions') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody id="rolesTableBody">
                                @include('roles.partials.table', ['roles' => $roles])
                            </tbody>
                        </table>
                        <div class="mt-3">
                            {{ $roles->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const The_operation_was_completed_successfully = {!! json_encode(__('The operation was completed successfully')) !!};
        const An_error_occurred_while_saving = {!! json_encode(__('An error occurred while saving')) !!};
        const Error = {!! json_encode(__('Error')) !!};
        const Confirm_delete = {!! json_encode(__('Confirm delete')) !!};
        const Are_you_sure_you_want_to_delete_this_role = {!! json_encode(__('Are you sure you want to delete this role?')) !!};
        const yes_delete_it = {!! json_encode(__('Yes, delete it')) !!};
        const Cancel = {!! json_encode(__('Cancel')) !!};
        const Delete = {!! json_encode(__('Deleted')) !!};
        const An_error_occurred_while_deleting = {!! json_encode(__('An error occurred while deleting')) !!};
        const Success = {!! json_encode(__('Success')) !!};
        const This_role_cannot_be_deleted_because_it_is_assigned_to_users = {!! json_encode(__('This role cannot be deleted because it is assigned to users')) !!};



        let searchInput = document.getElementById('search');
        let perPageInput = document.getElementById('perPage');

        function fetchRoles(showSwalMsg = null, swalType = 'success') {
            let search = searchInput.value;
            let perPage = perPageInput.value;

            fetch(`{{ route('roles.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('rolesTableBody').innerHTML = data.html;
                    if (showSwalMsg) {
                        showSwal(showSwalMsg, swalType);
                    }
                });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(window.roleSearchTimeout);
            window.roleSearchTimeout = setTimeout(fetchRoles, 400);
        });

        document.addEventListener('submit', function(e) {
            if (e.target.matches('#roleCreateForm, #roleEditForm')) {
                e.preventDefault();
                let form = e.target;
                let url = form.action;
                let formData = new FormData(form);

                fetch(url, {
                        method: form.method,
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status) {
                            Swal.fire({
                                title: The_operation_was_completed_successfully,
                                text: data.message,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "{{ route('roles.index') }}";
                            });
                        } else {
                            Swal.fire({
                                title: Error,
                                text: data.message || An_error_occurred_while_saving,
                                icon: 'error'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            title: Error,
                            text: An_error_occurred_while_saving,
                            icon: 'error'
                        });
                    });
            }
        });

        perPageInput.addEventListener('change', fetchRoles);

        // SweetAlert لحذف دور
        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-delete-role')) {
                let btn = e.target.closest('.btn-delete-role');
                let url = btn.getAttribute('data-url');
                Swal.fire({
                    title: Confirm_delete,
                    text: Are_you_sure_you_want_to_delete_this_role,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: yes_delete_it,
                    cancelButtonText: Cancel
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(url, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(async response => {
                                const contentType = response.headers.get('content-type') || '';
                                const data = contentType.includes('application/json') ?
                                    await response.json() : {
                                        status: false,
                                        message: await response.text()
                                    };
                                if (!response.ok) throw data;
                                return data;
                            })
                            .then(data => {
                                if (data.status) {
                                    Swal.fire({
                                        title: Delete,
                                        text: data.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    fetchRoles();
                                } else {
                                    Swal.fire({
                                        title: Error,
                                        text: data.message || An_error_occurred_while_deleting,
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch((err) => {
                                const msg = err && err.message ? err.message :
                                    (typeof err === 'string' ? err : An_error_occurred_while_deleting);
                                Swal.fire({
                                    title: Error,
                                    text: msg,
                                    icon: 'error'
                                });
                            });
                    }
                });
            }
        });

        function showSwal(message, type = 'success') {
            Swal.fire({
                icon: type,
                title: type === 'success' ? Success : Error,
                text: message,
                showConfirmButton: false,
                timer: 1800
            });
        }
    </script>
@endpush
