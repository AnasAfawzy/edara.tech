@extends('layouts.app')

@section('title', __('إضافة دور'))

@section('content')
    {!! breadcrumb([['title' => __('إدارة المستخدمين')], ['title' => __('إضافة دور')]]) !!}
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('إضافة دور') }}</h5>
                    <div class="card-body">
                        <form action="{{ route('roles.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label>اسم الدور</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <table class="table text-center">
                                    <thead>
                                        <tr>
                                            <th colspan="7" class="text-center bg-primary text-white">
                                                الصلاحيات حسب الموديولات
                                            </th>
                                        </tr>
                                        <tr>
                                            <th>الموديول</th>
                                            <th>الظهور في السايد بار</th>
                                            <th>عرض</th>
                                            <th>إضافة</th>
                                            <th>تعديل</th>
                                            <th>حذف</th>
                                            <th>الكل</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($modules->where('parent_id', null) as $parent)
                                            <tr class="table-primary">
                                                <td><strong>{{ $parent->label }}</strong></td>
                                                <td>
                                                    <input type="checkbox" name="sidebar_modules[]"
                                                        value="{{ $parent->id }}">
                                                </td>
                                                <td colspan="5"></td>
                                            </tr>
                                            @foreach ($modules->where('parent_id', $parent->id) as $child)
                                                <tr>
                                                    <td style="padding-right:30px;">&#8627; {{ $child->label }}</td>
                                                    <td>
                                                        <input type="checkbox" name="sidebar_modules[]"
                                                            value="{{ $child->id }}">
                                                    </td>
                                                    @foreach (['view', 'create', 'edit', 'delete'] as $action)
                                                        <td>
                                                            <input type="checkbox" name="permissions[]"
                                                                value="{{ strtolower($action . ' ' . str_replace(['_', '-'], ' ', $child->name)) }}"
                                                                class="perm-{{ $child->id }}">
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
                                {{ __('حفظ') }}
                            </button>
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
