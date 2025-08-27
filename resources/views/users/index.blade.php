@extends('layouts.app')

@section('title', __('Users'))

@section('content')
    {!! breadcrumb([['title' => __('Settings')], ['title' => __('Users')]]) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر وأزرار الإضافة -->
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('users.index') }}" class="d-flex align-items-center">
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
                            <form method="GET" action="{{ route('users.index') }}" class="d-flex align-items-center"
                                id="searchForm">
                                <input type="hidden" name="perPage" value="{{ $perPage ?? 10 }}">
                                <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                                <div class="mb-3">
                                    {{-- <label for="live-search" class="form-label me-2 mb-0">{{ __('Search') }}:</label> --}}
                                    <div class="input-group" style="width: 250px;">
                                        <input type="text" id="live-search" class="form-control form-control-sm"
                                            placeholder="{{ __('Search for user...') }}">
                                        <button class="btn btn-outline-secondary btn-sm" type="button" disabled>
                                            <i class="icon-base ti tabler-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                            @can('create users')
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#userModal"
                                    onclick="openUserModal()">
                                    <i class="icon-base ti tabler-plus me-1"></i>
                                    {{ __('Add User') }}
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-datatable table-responsive pt-0">
                        <table class="table table-striped text-center align-middle">
                            <thead>
                                <tr>
                                    <th>{{ __('User Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Roles') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('users.partials.table', ['users' => $users])
                        </table>
                        <div class="mt-3" id="users-pagination">
                            {{ $users->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="userForm">
                @csrf
                <input type="hidden" name="user_id" id="user_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">{{ __('Add User') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('User Name') }}</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">{{ __('Email') }}</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3" id="passwordField">
                            <label for="password" class="form-label">{{ __('Password') }}</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="roles" class="form-label">{{ __('Roles') }}</label>
                            <select class="form-select" id="roles" name="roles" required>
                                <option value="" disabled selected>{{ __('Choose Role') }}</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary" id="saveUserBtn">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function reloadUsersTable(search = '', perPage = 10, page = 1) {
            $.get("{{ route('users.search') }}", {
                search: search,
                perPage: perPage,
                page: page
            }, function(data) {
                console.log(search);
                $('#users-table-body').replaceWith(data.html);
                $('#users-pagination').html(data.pagination);
            });
        }

        $('#live-search').on('input', function() {
            let search = $(this).val();
            let perPage = 10; // أو اجلبها من select إذا عندك
            reloadUsersTable(search, perPage, 1);
        });

        // باجيناشن AJAX
        $(document).on('click', '#users-pagination .pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            let search = $('#live-search').val();
            let perPage = 10; // أو اجلبها من select إذا عندك
            reloadUsersTable(search, perPage, page);
        });

        // عند الحذف
        function deleteUser(id) {
            Swal.fire({
                title: '{{ __('Confirm delete') }}',
                text: '{{ __('Are you sure you want to delete this user?') }}',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '{{ __('Yes, delete it') }}',
                cancelButtonText: '{{ __('Cancel') }}'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('users') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(res) {
                            Swal.fire('{{ __('Deleted') }}', res.message, 'success');
                            let search = $('#live-search').val();
                            let perPage = 10; // أو اجلبها من select إذا عندك
                            reloadUsersTable(search, perPage, 1);
                        },
                        error: function(xhr) {
                            Swal.fire('{{ __('Error') }}', xhr.responseJSON?.message || 'حدث خطأ',
                                'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
