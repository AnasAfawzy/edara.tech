@extends('layouts.app')

@section('title', __('الأدوار'))

@section('content')
    {!! breadcrumb([['title' => __('إدارة المستخدمين')], ['title' => __('الأدوار')]]) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر وأزرار الإضافة -->
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('roles.index') }}" class="d-flex align-items-center">
                                <input type="hidden" name="search" value="{{ $search }}">
                                <label for="perPage" class="form-label me-2 mb-0">{{ __('عرض') }}:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    style="width: auto;" onchange="this.form.submit()">
                                    <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                                </select>
                                <span class="ms-2">{{ __('عنصر') }}</span>
                            </form>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <!-- البحث -->
                            <form method="GET" action="{{ route('roles.index') }}" class="d-flex align-items-center"
                                id="searchForm">
                                <input type="hidden" name="perPage" value="{{ $perPage }}">
                                <label for="search" class="form-label me-2 mb-0">{{ __('بحث') }}:</label>
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('بحث عن دور...') }}" value="{{ $search }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="icon-base ti tabler-search"></i>
                                    </button>
                                </div>
                            </form>
                            @can('create roles')
                            <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('إضافة دور') }}
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-datatable table-responsive pt-0">
                        <table class="table table-striped text-center align-middle">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>الصلاحيات</th>
                                    <th>إجراءات</th>
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
        // البحث بمجرد الكتابة
        document.getElementById('search').addEventListener('input', function() {
            clearTimeout(window.roleSearchTimeout);
            window.roleSearchTimeout = setTimeout(function() {
                document.getElementById('searchForm').submit();
            }, 500); // يمكنك تقليل أو زيادة التأخير حسب الحاجة
        });
    </script>
    <script>
        let searchInput = document.getElementById('search');
        let perPageInput = document.getElementById('perPage');

        function fetchRoles() {
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
                });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(window.roleSearchTimeout);
            window.roleSearchTimeout = setTimeout(fetchRoles, 400);
        });

        perPageInput.addEventListener('change', fetchRoles);
    </script>
@endpush
