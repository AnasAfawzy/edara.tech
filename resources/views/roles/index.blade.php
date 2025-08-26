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
    {{-- <script src="{{ asset('assets/js/sweetalert2.js') }}"></script> --}}
    <script>
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
                                title: 'تمت العملية بنجاح',
                                text: data.message,
                                icon: 'success',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = "{{ route('roles.index') }}";
                            });
                        } else {
                            Swal.fire({
                                title: 'خطأ!',
                                text: data.message || 'حدث خطأ أثناء الحفظ',
                                icon: 'error'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            title: 'خطأ!',
                            text: 'حدث خطأ أثناء الحفظ',
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
                    title: 'تأكيد الحذف',
                    text: 'هل أنت متأكد أنك تريد حذف هذا الدور؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'نعم، احذف!',
                    cancelButtonText: 'إلغاء'
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
                                        title: 'تم الحذف',
                                        text: data.message,
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    });
                                    fetchRoles();
                                } else {
                                    Swal.fire({
                                        title: 'خطأ!',
                                        text: data.message || 'حدث خطأ أثناء الحذف',
                                        icon: 'error'
                                    });
                                }
                            })
                            .catch(() => {
                                Swal.fire({
                                    title: 'خطأ!',
                                    text: 'حدث خطأ أثناء الحذف',
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
                title: type === 'success' ? 'تمت العملية بنجاح' : 'خطأ!',
                text: message,
                showConfirmButton: false,
                timer: 1800
            });
        }
    </script>
@endpush
