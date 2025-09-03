@extends('layouts.app')

@section('title', __('Roles'))

@section('content')
    {!! breadcrumb([['title' => __('Settings')], ['title' => __('Roles')]]) !!}
    <h4 class="mb-1">{{ __('Roles List') }}</h4>

    <!-- Role cards -->
    <div class="row g-6">
        @foreach ($roles as $role)
            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card role-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="fw-normal mb-0 text-body">
                                {{ __('Total :count users', ['count' => $role->users_count]) }}
                            </h6>
                            <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                @foreach ($role->users->take(4) as $user)
                                    <li data-bs-toggle="tooltip" title="{{ $user->name }}" class="avatar pull-up">
                                        <img class="rounded-circle"
                                            src="{{ $user->avatar_url ?? asset('assets/img/avatars/default.png') }}"
                                            alt="Avatar" />
                                    </li>
                                @endforeach
                                @if ($role->users_count > 4)
                                    <li class="avatar">
                                        <span class="avatar-initial rounded-circle pull-up" data-bs-toggle="tooltip"
                                            title="{{ __(':count more', ['count' => $role->users_count - 4]) }}">
                                            +{{ $role->users_count - 4 }}
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div class="role-heading">
                                <h5 class="mb-1">{{ $role->name }}</h5>
                                @can('edit roles')
                                    <button type="button" class="btn btn-icon bg-transparent shadow-none role-edit-modal"
                                        data-role-id="{{ $role->id }}" title="{{ __('Edit') }}">
                                        <i class="icon-base ti tabler-pencil"></i>
                                    </button>
                                @endcan
                                @can('delete roles')
                                    <a href="javascript:void(0);" class="role-delete-modal ms-2"
                                        data-role-id="{{ $role->id }}" title="{{ __('Delete') }}">
                                        <i class="icon-base ti tabler-trash"></i>
                                    </a>
                                @endcan
                            </div>
                            {{-- <a href="javascript:void(0);" onclick="duplicateRole({{ $role->id }})">
                                <i class="icon-base ti tabler-copy icon-md text-heading"></i>
                            </a> --}}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
        <div class="col-xl-4 col-lg-6 col-md-6">
            <div class="card h-100">
                <div class="row h-100">
                    <div class="col-sm-5">
                        <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-4">
                            <img src="{{ asset('assets/img/illustrations/add-new-roles.png') }}" class="img-fluid"
                                alt="Image" width="83" />
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="card-body text-sm-end text-center ps-sm-0">
                            <button data-bs-target="#addRoleModal" data-bs-toggle="modal"
                                class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role">{{ __('Add Role') }}</button>
                            <p class="mb-0">
                                {{ __('Add new role,if it doesn\'t exist') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <h4 class="mt-6 mb-1">{{ __('Total users with their roles') }}</h4>
        </div>
        <div class="col-12">
            <!-- Role Table -->
            <div class="card">
                <div class="card-datatable">
                    <table class="datatables-users table border-top">
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                                <th>{{ __('User') }}</th>
                                <th>{{ __('Role') }}</th>
                                <th>{{ _('Action') }}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
            <!--/ Role Table -->
        </div>
    </div>

    <!-- Add Role Modal -->
    @include('roles.partials.modal-add-role')
    <!-- / Add Role Modal -->

    @include('users.partials.modal-add-user')
@endsection

@push('scripts')
    <script>
        window.translations = {
            success: {!! json_encode(__('The operation was completed successfully')) !!},
            error: {!! json_encode(__('An error occurred')) !!},
            confirm_delete: {!! json_encode(__('Confirm delete')) !!},
            confirm_delete_text: {!! json_encode(__('Are you sure you want to delete this role?')) !!},
            yes_delete: {!! json_encode(__('Yes, delete it')) !!},
            cancel: {!! json_encode(__('Cancel')) !!},
            deleted: {!! json_encode(__('Deleted')) !!},
            cannot_delete: {!! json_encode(__('This role cannot be deleted because it is assigned to users')) !!},
            add_user: @json(__('Add User')),
            edit_user: @json(__('Edit User')), // أضف هذا
            Export: @json(__('Export')),
            Search_User: @json(__('Search User')),

            // إضافة ترجمات للمستخدمين
            delete_success: @json(__('User deleted successfully')),
            delete_error: @json(__('Error occurred while deleting')),
            something_wrong: @json(__('Something went wrong!')),
            edit: @json(__('Edit')),
            delete: @json(__('Delete')),

            // ترجمات Pagination
            pagination_info: {!! json_encode(
                __('عرض :start إلى :end من أصل :total مدخل', [
                    'start' => '_START_',
                    'end' => '_END_',
                    'total' => '_TOTAL_',
                ]),
            ) !!},
            pagination_info_empty: @json(__('لا يوجد بيانات للعرض')),
            pagination_length_menu: @json(__('عرض _MENU_ مدخلات')),
            pagination_search: @json(__('بحث:')),
            pagination_zero_records: @json(__('لا يوجد نتائج مطابقة')),
            pagination_empty_table: @json(__('لا توجد بيانات في الجدول')),
            pagination_info_filtered: {!! json_encode(__('(تم التصفية من _MAX_ مدخلات)')) !!},

        };
    </script>


    <script src="{{ asset('assets/js/app-access-roles.js') }}"></script>

    <script>
        // متغيرات الترجمة


        // عناصر DOM
        const searchInput = document.getElementById('search');
        const perPageSelect = document.getElementById('perPage');
        const rolesContainer = document.getElementById('rolesContainer');
        const loadingSkeleton = document.getElementById('loadingSkeleton');

        let searchTimeout;

        // البحث مع AJAX
        function fetchRoles(showLoading = true) {
            const search = searchInput.value;
            const perPage = perPageSelect.value;

            if (showLoading) {
                showLoadingSkeleton();
            }

            fetch(`{{ route('roles.search') }}?search=${encodeURIComponent(search)}&perPage=${perPage}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    rolesContainer.innerHTML = data.html;
                    document.querySelector('.mt-4').innerHTML = data.pagination; // تحديث الباجيناشن
                    hideLoadingSkeleton();
                    initializeTooltips();
                })
                .catch(error => {
                    console.error('Search error:', error);
                    hideLoadingSkeleton();
                    showAlert(translations.error, 'error');
                });
        }

        // إظهار/إخفاء Loading Skeleton
        function showLoadingSkeleton() {
            rolesContainer.style.display = 'none';
            loadingSkeleton.style.display = 'block';
        }

        function hideLoadingSkeleton() {
            loadingSkeleton.style.display = 'none';
            rolesContainer.style.display = 'block';
        }

        // تهيئة Tooltips
        function initializeTooltips() {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // البحث الفوري
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => fetchRoles(), 500);
            });
        }

        // تغيير عدد العناصر
        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                fetchRoles();
            });
        }

        // حذف الدور
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.role-delete-modal');
            if (btn) {
                e.preventDefault();
                const roleId = btn.getAttribute('data-role-id');
                Swal.fire({
                    title: translations.confirm_delete,
                    text: translations.confirm_delete_text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: translations.yes_delete,
                    cancelButtonText: translations.cancel,
                    customClass: {
                        popup: 'swal-popup-custom'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteRole(`/roles/${roleId}`);
                    }
                });
            }
        });

        // دالة حذف الدور
        function deleteRole(url) {
            fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        showAlert(data.message, 'success');
                        fetchRoles(false);
                    } else {
                        showAlert(data.message || translations.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Delete error:', error);
                    showAlert(translations.error, 'error');
                });
        }

        // نسخ الدور
        function duplicateRole(roleId) {
            fetch(`{{ url('roles') }}/${roleId}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        showAlert(data.message, 'success');
                        fetchRoles(false);
                    } else {
                        showAlert(data.message || translations.error, 'error');
                    }
                })
                .catch(error => {
                    console.error('Duplicate error:', error);
                    showAlert(translations.error, 'error');
                });
        }

        // عرض التنبيهات
        function showAlert(message, type = 'success') {
            const icon = type === 'success' ? 'success' : 'error';
            const title = type === 'success' ? translations.success : translations.error;

            Swal.fire({
                icon: icon,
                title: title,
                text: message,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end'
            });
        }

        // تهيئة أولية
        document.addEventListener('DOMContentLoaded', function() {
            initializeTooltips();

            // إضافة تأثيرات hover للكاردات
            document.querySelectorAll('.role-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });

                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });

        // تحسين الأداء
        window.addEventListener('load', function() {
            // Lazy loading للصور
            const images = document.querySelectorAll('img[data-src]');
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        });

        // دعم الباجيناشن بالـ AJAX
        document.addEventListener('click', function(e) {
            if (e.target.closest('.pagination a')) {
                e.preventDefault();
                const url = e.target.closest('a').href;
                fetchPagination(url);
            }
        });

        function fetchPagination(url) {
            showLoadingSkeleton();
            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    rolesContainer.innerHTML = data.html;
                    document.querySelector('.mt-4').innerHTML = data.pagination;
                    hideLoadingSkeleton();
                    initializeTooltips();
                })
                .catch(error => {
                    hideLoadingSkeleton();
                    showAlert(translations.error, 'error');
                });
        }

        // فتح المودال للإضافة
        document.querySelector('.add-new-role').addEventListener('click', function() {
            fetch(`/roles/create`)
                .then(res => res.json())
                .then(data => openRoleModal(data.role, data.modules));
        });
        // فتح المودال للتعديل
        document.addEventListener('click', function(e) {
            if (e.target.closest('.role-edit-modal')) {
                const roleId = e.target.closest('.role-edit-modal').dataset.roleId;
                fetch(`/roles/${roleId}/edit`)
                    .then(res => res.json())
                    .then(data => openRoleModal(data.role, data.modules));
            }
        });

        function openRoleModal(role = null, modules = [], data) {
            document.getElementById('roleModalTitle').textContent = role ? '{{ __('Edit Role') }}' :
                '{{ __('Add New Role') }}';
            document.getElementById('roleIdInput').value = role ? role.id : '';
            document.getElementById('modalRoleName').value = role ? role.name : '';
            loadPermissionsTable(role, modules); // يجب أن تملأ الصلاحيات هنا
            $('#addRoleModal').modal('show');
        }

        function loadPermissionsTable(role = null, modules = []) {
            const container = document.getElementById('permissionsTableContainer');
            container.innerHTML = '';

            if (!modules.length) {
                container.innerHTML = '<p class="text-muted">{{ __('No permissions loaded') }}</p>';
                return;
            }

            let html = `<table class="table text-center">
<thead>
    <tr>
        <th>{{ __('Module') }}</th>
        <th>{{ __('Show in Sidebar') }}</th>
        <th>{{ __('View') }}</th>
        <th>{{ __('Add') }}</th>
        <th>{{ __('Edit') }}</th>
        <th>{{ __('Delete') }}</th>
        <th>{{ __('All') }}</th>
    </tr>
</thead>
<tbody>`;

            modules.forEach(parent => {
                html += `<tr class="table-primary">
    <td><strong>${parent.name}</strong></td>
    <td>
        <input type="checkbox" name="sidebar_modules[]" value="${parent.id}"
            ${role.sidebar_modules && role.sidebar_modules.includes(parent.id) ? 'checked' : ''}>
    </td>
    <td colspan="5"></td>
</tr>`;

                parent.children.forEach(child => {
                    html += `<tr>
        <td style="padding-right:30px;">&#8627; ${child.label}</td>
        <td>
            <input type="checkbox" name="sidebar_modules[]" value="${child.id}"
                ${role.sidebar_modules && role.sidebar_modules.includes(child.id) ? 'checked' : ''}>
        </td>`;

                    // عرض كل صلاحية (view, create, edit, delete)
                    ['view', 'create', 'edit', 'delete'].forEach(action => {
                        // ابحث عن الصلاحية بالاسم الصحيح
                        const perm = child.permissions.find(p => p.name.toLowerCase().startsWith(
                            action));
                        const permName = perm ? perm.name : `${action} ${child.label}`
                            .toLowerCase();
                        html += `<td>
            <input type="checkbox" name="permissions[]" value="${permName}"
                class="perm-${child.id}"
                ${role.permissions && role.permissions.includes(permName) ? 'checked' : ''}>
        </td>`;
                    });

                    html += `<td>
        <input type="checkbox" class="check-all" data-module="${child.id}">
    </td>
    </tr>`;
                });
            });

            html += `</tbody></table>`;
            container.innerHTML = html;
            initPermissionsTableEvents();
        }
        document.getElementById('roleModalForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const roleId = formData.get('role_id');
            const url = roleId ? `/roles/${roleId}` : `/roles`;
            const method = roleId ? 'PUT' : 'POST';

            fetch(url, {
                    method: method,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        $('#addRoleModal').modal('hide');
                        fetchRoles(false);
                        showAlert(data.message, 'success');
                    } else {
                        showAlert(data.message || '{{ __('An error occurred') }}', 'error');
                    }
                });
        });

        function initPermissionsTableEvents() {
            // عند الضغط على "الكل" يتم تحديد كل صلاحيات الموديول + السايدبار
            document.querySelectorAll('.check-all').forEach(function(checkAll) {
                checkAll.addEventListener('change', function() {
                    let moduleId = this.getAttribute('data-module');
                    document.querySelectorAll('.perm-' + moduleId).forEach(function(perm) {
                        perm.checked = checkAll.checked;
                    });
                    let sidebarCheckbox = document.querySelector('input[name="sidebar_modules[]"][value="' +
                        moduleId + '"]');
                    if (sidebarCheckbox) {
                        sidebarCheckbox.checked = checkAll.checked;
                    }
                    updateParentSidebar(moduleId);
                });
            });

            // إذا ألغيت أي صلاحية أو السايدبار، يتم إلغاء "الكل" وتحديث الأب
            document.querySelectorAll('input[type="checkbox"]').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (!this.classList.contains('check-all')) {
                        let moduleId = null;
                        if (this.className.startsWith('perm-')) {
                            moduleId = this.className.replace('perm-', '');
                        }
                        if (this.name === 'sidebar_modules[]') {
                            moduleId = this.value;
                        }
                        if (moduleId) {
                            let allChecked = true;
                            document.querySelectorAll('.perm-' + moduleId).forEach(function(perm) {
                                if (!perm.checked) allChecked = false;
                            });
                            let sidebarCheckbox = document.querySelector(
                                'input[name="sidebar_modules[]"][value="' + moduleId + '"]');
                            if (sidebarCheckbox && !sidebarCheckbox.checked) allChecked = false;
                            let checkAll = document.querySelector('.check-all[data-module="' + moduleId +
                                '"]');
                            if (checkAll) checkAll.checked = allChecked;
                            updateParentSidebar(moduleId);
                        }
                    }
                });
            });

            // عند تحميل الصفحة: إذا كل صلاحيات الموديول متعلمة، علم على "الكل" تلقائياً
            document.querySelectorAll('.check-all').forEach(function(checkAll) {
                let moduleId = checkAll.getAttribute('data-module');
                let allChecked = true;
                document.querySelectorAll('.perm-' + moduleId).forEach(function(perm) {
                    if (!perm.checked) allChecked = false;
                });
                let sidebarCheckbox = document.querySelector('input[name="sidebar_modules[]"][value="' +
                    moduleId + '"]');
                if (sidebarCheckbox && !sidebarCheckbox.checked) allChecked = false;
                checkAll.checked = allChecked;
            });
        }

        // تحديث ظهور الأب في السايدبار حسب صلاحيات أبنائه
        function updateParentSidebar(childModuleId) {
            let childRow = document.querySelector('input[name="sidebar_modules[]"][value="' + childModuleId + '"]');
            if (!childRow) return;
            let parentTr = childRow.closest('tr');
            while (parentTr && !parentTr.classList.contains('table-primary')) {
                parentTr = parentTr.previousElementSibling;
            }
            if (!parentTr) return;
            let parentSidebarCheckbox = parentTr.querySelector('input[name="sidebar_modules[]"]');
            if (!parentSidebarCheckbox) return;
            let parentId = parentSidebarCheckbox.value;
            let hasChecked = false;
            document.querySelectorAll('input[name="sidebar_modules[]"]').forEach(function(childSidebar) {
                let childTr = childSidebar.closest('tr');
                if (childTr && !childTr.classList.contains('table-primary')) {
                    let parentRow = childTr.previousElementSibling;
                    while (parentRow && !parentRow.classList.contains('table-primary')) {
                        parentRow = parentRow.previousElementSibling;
                    }
                    if (parentRow && parentRow.querySelector('input[name="sidebar_modules[]"]').value ===
                        parentId) {
                        let childId = childSidebar.value;
                        let checked = false;
                        if (childSidebar.checked) checked = true;
                        document.querySelectorAll('.perm-' + childId).forEach(function(perm) {
                            if (perm.checked) checked = true;
                        });
                        if (checked) hasChecked = true;
                    }
                }
            });
            parentSidebarCheckbox.checked = hasChecked;
        }

        function reloadUsersDataTable() {
            if (typeof window.dt_User !== 'undefined' && window.dt_User !== null) {
                window.dt_User.ajax.reload(null, false);
                return true;
            }
            return false;
        }
        $('#userForm').on('submit', function(e) {
            e.preventDefault();
            let userId = $('#user_id').val();
            let url = userId ? `/users/${userId}` : `/users`;
            let method = userId ? 'PUT' : 'POST';

            let formData = $(this).serialize();

            $.ajax({
                url: url,
                type: method,
                data: formData,
                success: function(res) {
                    $('#addUserModal').modal('hide');
                    Swal.fire('{{ __('Success') }}', res.message, 'success');

                    // إعادة تحميل الجدول
                    if (!reloadUsersDataTable()) {
                        console.log('DataTable not available, reloading page...');
                        setTimeout(() => location.reload(), 1000); // انتظر ثانية ثم أعد تحميل الصفحة
                    }

                    $('#userForm')[0].reset();
                    $('#user_id').val('');
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || '{{ __('An error occurred') }}';
                    Swal.fire('{{ __('Error') }}', msg, 'error');
                }
            });
        });

        // التعامل مع أحداث التعديل والحذف
        document.addEventListener('click', function(e) {
            // تعديل المستخدم
            if (e.target.closest('.edit-record')) {
                e.preventDefault();
                const editBtn = e.target.closest('.edit-record');
                const userId = editBtn.getAttribute('data-user-id');
                const userName = editBtn.getAttribute('data-user-name');
                const userEmail = editBtn.getAttribute('data-user-email');
                const userRole = editBtn.getAttribute('data-user-role-name');
                // ملء نموذج التعديل
                $('#user_id').val(userId);
                $('#name').val(userName);
                $('#email').val(userEmail);
                $('#roles').val(userRole);

                // تغيير عنوان المودال
                $('#addUserModalLabel').text(window.translations?.edit_user || 'Edit User');

                // فتح المودال
                $('#addUserModal').modal('show');
            }
        });
        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete-record')) {
                e.preventDefault();
                const userId = e.target.closest('.delete-record').getAttribute('data-user-id');

                Swal.fire({
                    title: '{{ __('Confirm delete') }}',
                    text: '{{ __('Are you sure you want to delete this user?') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ __('Yes, delete it') }}',
                    cancelButtonText: '{{ __('Cancel') }}'
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteUser(userId);
                    }
                });
            }
        });
        // دالة حذف المستخدم
        function deleteUser(userId) {
            fetch(`/users/${userId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            window.translations?.deleted || 'تم الحذف!',
                            data.message || window.translations?.delete_success || 'تم حذف المستخدم بنجاح',
                            'success'
                        );
                        // إعادة تحميل الجدول
                        if (window.dt_User) {
                            window.dt_User.ajax.reload(null, false);
                        }
                    } else {
                        Swal.fire(
                            window.translations?.error || 'خطأ!',
                            data.message || window.translations?.delete_error || 'حدث خطأ أثناء الحذف',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        window.translations?.error || 'خطأ!',
                        window.translations?.something_wrong || 'حدث خطأ ما!',
                        'error'
                    );
                });
        }

        // إعادة تعيين المودال عند إغلاقه
        $('#addUserModal').on('hidden.bs.modal', function() {
            $('#userForm')[0].reset();
            $('#user_id').val('');
            $('#addUserModalLabel').text(window.translations?.add_user || 'Add User');
        });
    </script>
@endpush
