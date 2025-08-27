@extends('layouts.app')

@section('title', __('Edit Role'))

@section('content')
    {!! breadcrumb([['title' => __('Settings')], ['title' => __('Roles')], ['title' => __('Edit Role')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('Edit Role') }}</h5>
                    <div class="card-body">
                        <form action="{{ route('roles.update', $role) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label>{{ __('Role Name') }}</label>
                                <input type="text" name="name" class="form-control" value="{{ $role->name }}"
                                    required>
                            </div>
                            <div class="mb-3">
                                <table class="table text-center">
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
                                    <tbody>
                                        @foreach ($modules->where('parent_id', null) as $parent)
                                            <tr class="table-primary">
                                                <td><strong>{{ __($parent->label) }}</strong></td>
                                                <td>
                                                    <input type="checkbox" name="sidebar_modules[]"
                                                        value="{{ $parent->id }}"
                                                        {{ in_array($parent->id, $sidebarModules) ? 'checked' : '' }}>
                                                </td>
                                                <td colspan="5"></td>
                                            </tr>
                                            @foreach ($modules->where('parent_id', $parent->id) as $child)
                                                <tr>
                                                    <td style="padding-right:30px;">&#8627; {{ __($child->label) }}</td>
                                                    <td>
                                                        <input type="checkbox" name="sidebar_modules[]"
                                                            value="{{ $child->id }}"
                                                            {{ in_array($child->id, $sidebarModules) ? 'checked' : '' }}>
                                                    </td>
                                                    @foreach (['view', 'create', 'edit', 'delete'] as $action)
                                                        <td>
                                                            <input type="checkbox" name="permissions[]"
                                                                value="{{ strtolower($action . ' ' . str_replace(['_', '-'], ' ', $child->name)) }}"
                                                                class="perm-{{ $child->id }}"
                                                                {{ in_array(strtolower($action . ' ' . str_replace(['_', '-'], ' ', $child->name)), $rolePermissions) ? 'checked' : '' }}>
                                                        </td>
                                                    @endforeach
                                                    <td>
                                                        <input type="checkbox" class="check-all"
                                                            data-module="{{ $child->id }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <button class="btn btn-primary">
                                <i class="icon-base ti tabler-device-floppy me-1"></i>
                                {{ __('تحديث') }}
                            </button>
                            <a href="{{ route('roles.index') }}" class="btn btn-secondary ms-2">
                                <i class="icon-base ti tabler-arrow-left me-1"></i>
                                {{ __('Back') }}
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
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
                        let checkAll = document.querySelector('.check-all[data-module="' + moduleId + '"]');
                        if (checkAll) checkAll.checked = allChecked;
                        updateParentSidebar(moduleId);
                    }
                }
            });
        });

        // عند تحميل الصفحة: إذا كل صلاحيات الموديول متعلمة، علم على "الكل" تلقائياً
        document.addEventListener('DOMContentLoaded', function() {
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
        });

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
    </script>
@endpush
