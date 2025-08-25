@extends('layouts.app')

@section('title', __('Cost Centers Tree'))
@push('css')
    <style>
        .jstree-red>a,
        .jstree-red>a>i {
            color: red !important;
        }

        .jstree-blue>a,
        .jstree-blue>a>i {
            color: #1976d2 !important;
        }

        .parent-costcenter-red {
            color: red;
        }

        .parent-costcenter-blue {
            color: #1976d2;
        }
    </style>
@endpush
@section('content')
    {!! breadcrumb([['title' => __('Main Data')], ['title' => __('Cost Centers')]]) !!}

    <div class="col-md-12 col-12">
        <div class="card">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                {{ __('Cost Centers Tree') }}
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCostCenterModal">
                    {{ __('Add Cost Center') }}
                </button>
            </h5>
            <div class="card-body">
                <div id="jstree-context-menu" class="overflow-auto"></div>
            </div>
        </div>
    </div>

    <!-- Modal لإضافة مركز تكلفة جديد -->
    <div class="modal fade" id="addCostCenterModal" tabindex="-1" aria-labelledby="addCostCenterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="addCostCenterForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addCostCenterModalLabel">{{ __('Add New Cost Center') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Input للبحث -->
                        <div class="mb-3">
                            <label for="parentCostCenterInput" class="form-label">{{ __('Parent Cost Center') }}</label>
                            <input class="form-control" list="costCentersList" id="parentCostCenterInput"
                                placeholder="{{ __('Search for cost center by name or code') }}">
                            <input type="hidden" name="parent_id" id="parentCostCenterId">
                        </div>

                        <!-- Datalist -->
                        <datalist id="costCentersList">
                            @foreach ($allCostCenters as $cc)
                                @if ($cc->slave == 0 || $cc->has_sub == 1)
                                    <option value="{{ $cc->name }} — {{ $cc->code }}" data-id="{{ $cc->id }}"
                                        data-slave="{{ (int) $cc->slave }}" data-code="{{ $cc->code }}"
                                        data-has_sub="{{ (int) $cc->has_sub }}" data-name="{{ $cc->name }}">
                                    </option>
                                @endif
                            @endforeach
                        </datalist>

                        <!-- تفاصيل مركز التكلفة -->
                        <div id="parent-details" class="mb-3"
                            style="display:none; background: #f8f9fa; border-radius: 6px; padding: 10px; font-size: 14px;">
                        </div>

                        <div class="mb-3">
                            <label for="costCenterType" class="form-label">{{ __('Cost Center Type') }}</label>
                            <select class="form-select" id="costCenterType" name="type" required>
                                <option value="account">{{ __('Account') }}</option>
                                <option value="sub_account">{{ __('Sub Account') }}</option>
                                <option value="title">{{ __('Title') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="costCenterName" class="form-label">{{ __('New Cost Center Name') }}</label>
                            <input type="text" class="form-control" id="costCenterName" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal لتعديل مركز التكلفة -->
    <div class="modal fade" id="editCostCenterModal" tabindex="-1" aria-labelledby="editCostCenterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form id="editCostCenterForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCostCenterModalLabel">{{ __('Edit Cost Center') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">

                        <!-- Hidden id -->
                        <input type="hidden" name="id" id="editCostCenterId">

                        <!-- مركز التكلفة الأب -->
                        <div class="mb-3">
                            <label for="editParentCostCenterInput"
                                class="form-label">{{ __('Parent Cost Center') }}</label>
                            <input class="form-control" list="costCentersList" id="editParentCostCenterInput"
                                placeholder="{{ __('Search for cost center by name or code') }}">
                            <input type="hidden" name="parent_id" id="editParentCostCenterId">
                        </div>

                        <!-- تفاصيل الأب -->
                        <div id="edit-parent-details" class="mb-3"
                            style="display:none; background: #f8f9fa; border-radius: 6px; padding: 10px; font-size: 14px;">
                        </div>

                        <!-- نوع مركز التكلفة (معطل) -->
                        <div class="mb-3">
                            <label for="editCostCenterType" class="form-label">{{ __('Cost Center Type') }}</label>
                            <select class="form-select" id="editCostCenterType" name="type">
                                <option value="account">{{ __('Account') }}</option>
                                <option value="sub_account">{{ __('Sub Account') }}</option>
                                <option value="title">{{ __('Title') }}</option>
                            </select>
                        </div>

                        <!-- الاسم (قابل للتعديل) -->
                        <div class="mb-3">
                            <label for="editCostCenterName" class="form-label">{{ __('New Cost Center Name') }}</label>
                            <input type="text" class="form-control" id="editCostCenterName" name="name" required>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Update') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        window.costCentersTreeDataUrl = "{{ route('cost-centers.tree.data') }}";
        const edit_label = {!! json_encode(__('Edit')) !!};
        const delete_label = {!! json_encode(__('Delete')) !!};
        const add_label = {!! json_encode(__('Add')) !!};
        const view_label = {!! json_encode(__('View')) !!};
        const deleted = {!! json_encode(__('Deleted')) !!};
        const error = {!! json_encode(__('Error')) !!};
        const cost_center_deleted_successfully = {!! json_encode(__('Cost Center deleted successfully')) !!};
        const cost_center_updated_successfully = {!! json_encode(__('Cost Center updated successfully')) !!};
        const cost_center_created_successfully = {!! json_encode(__('Cost Center created successfully')) !!};
        const cost_center_creation_failed = {!! json_encode(__('Cost Center creation failed')) !!};
        const error_occurred_while_deleting = {!! json_encode(__('Error occurred while deleting')) !!};
        const edit = {!! json_encode(__('Edit')) !!};
        const cannot_fetch_cost_center_data = {!! json_encode(__('Cannot fetch cost center data')) !!};
        const this_cost_center_cannot_be_deleted_because_it_has_branches = {!! json_encode(__('This cost center cannot be deleted because it has branches')) !!};
        const confirm_delete = {!! json_encode(__('Confirm delete')) !!};
        const are_you_sure_you_want_to_delete = {!! json_encode(__('Are you sure you want to delete')) !!};
        const yes_delete_it = {!! json_encode(__('Yes, delete it')) !!};
        const no_cancel = {!! json_encode(__('No, cancel')) !!};
        const view_cost_center_data = {!! json_encode(__('View Cost Center Data')) !!};
        const close_it = {!! json_encode(__('Close')) !!};
        const cancel_it = {!! json_encode(__('Cancel')) !!};
    </script>
    <script src="{{ asset('assets/js/trance.js') }}"></script>
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const costCentersData = {!! $costCenters !!};
            window.initJsTree(costCentersData);

            // متغيرات إضافة مركز تكلفة
            const createForm = document.getElementById('addCostCenterForm');
            const parentInput = document.getElementById('parentCostCenterInput');
            const parentIdInput = document.getElementById('parentCostCenterId');
            const parentDetailsDiv = document.getElementById('parent-details');
            const typeSelect = document.getElementById('costCenterType');

            // متغيرات تعديل مركز تكلفة
            const editForm = document.getElementById('editCostCenterForm');
            const editParentInput = document.getElementById('editParentCostCenterInput');
            const editParentIdInput = document.getElementById('editParentCostCenterId');
            const editParentDetailsDiv = document.getElementById('edit-parent-details');
            const editTypeSelect = document.getElementById('editCostCenterType');
            const editCostCenterIdInput = document.getElementById('editCostCenterId');
            const editCostCenterNameInput = document.getElementById('editCostCenterName');

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

            // تفريغ مودال إضافة مركز تكلفة
            document.getElementById('addCostCenterModal').addEventListener('show.bs.modal', function() {
                createForm.reset();
                parentIdInput.value = '';
                parentInput.value = '';
                parentDetailsDiv.style.display = 'none';
                parentDetailsDiv.innerHTML = '';
                Array.from(typeSelect.options).forEach(opt => {
                    opt.disabled = false;
                    opt.style.display = "";
                });
            });

            // تفريغ مودال تعديل مركز تكلفة
            document.getElementById('editCostCenterModal').addEventListener('show.bs.modal', function() {
                editForm.reset();
                editParentIdInput.value = '';
                editParentInput.value = '';
                editParentDetailsDiv.style.display = 'none';
                editParentDetailsDiv.innerHTML = '';
                Array.from(editTypeSelect.options).forEach(opt => {
                    opt.disabled = false;
                    opt.style.display = "";
                });
            });

            // اختيار مركز تكلفة أب في إضافة
            parentInput.addEventListener('input', function() {
                const val = this.value.trim();
                let matchedOption = Array.from(document.querySelectorAll('#costCentersList option'))
                    .find(opt => opt.value === val);

                if (matchedOption) {
                    parentIdInput.value = matchedOption.dataset.id;

                    // عرض التفاصيل
                    const slave = matchedOption.dataset.slave === "1" ? 1 : 0;
                    const hasSub = matchedOption.dataset.has_sub === "1" ? 1 : 0;
                    const name = matchedOption.dataset.name || '';
                    const code = matchedOption.dataset.code || '';

                    parentDetailsDiv.style.display = 'block';
                    parentDetailsDiv.innerHTML = `
                        <b>{{ __('Cost Center Name') }}:</b> ${name}<br>
                        <b>{{ __('Cost Center Type') }}:</b> ${slave === 1 ? "{{ __('Sub Account') }}" : (hasSub === 1 ? "{{ __('Title') }}" : "{{ __('Center') }}")}<br>
                        <b>{{ __('Cost Center Code') }}:</b> ${code}
                    `;

                    // التحكم في نوع مركز التكلفة
                    if (hasSub === 1) {
                        Array.from(typeSelect.options).forEach(opt => {
                            if (opt.value !== "account") {
                                opt.disabled = true;
                                opt.style.display = "none";
                            } else {
                                opt.disabled = false;
                                opt.style.display = "";
                            }
                        });
                        typeSelect.value = "account";
                    } else {
                        Array.from(typeSelect.options).forEach(opt => {
                            opt.disabled = false;
                            opt.style.display = "";
                        });
                    }
                } else {
                    parentIdInput.value = '';
                    parentDetailsDiv.style.display = 'none';
                    parentDetailsDiv.innerHTML = '';
                    Array.from(typeSelect.options).forEach(opt => {
                        opt.disabled = false;
                        opt.style.display = "";
                    });
                }
            });

            // اختيار مركز تكلفة أب في تعديل
            editParentInput.addEventListener('input', function() {
                const val = this.value.trim();
                let matchedOption = Array.from(document.querySelectorAll('#costCentersList option'))
                    .find(opt => opt.value === val);

                if (matchedOption) {
                    editParentIdInput.value = matchedOption.dataset.id;

                    // عرض التفاصيل
                    const slave = matchedOption.dataset.slave === "1" ? 1 : 0;
                    const hasSub = matchedOption.dataset.has_sub === "1" ? 1 : 0;
                    const name = matchedOption.dataset.name || '';
                    const code = matchedOption.dataset.code || '';

                    editParentDetailsDiv.style.display = 'block';
                    editParentDetailsDiv.innerHTML = `
                        <b>{{ __('Cost Center Name') }}:</b> ${name}<br>
                        <b>{{ __('Cost Center Type') }}:</b> ${slave === 1 ? "{{ __('Sub Account') }}" : (hasSub === 1 ? "{{ __('Title') }}" : "{{ __('Account') }}")}<br>
                        <b>{{ __('Cost Center Code') }}:</b> ${code}
                    `;

                    // التحكم في نوع مركز التكلفة
                    if (hasSub === 1) {
                        Array.from(editTypeSelect.options).forEach(opt => {
                            if (opt.value !== "account") {
                                opt.disabled = true;
                                opt.style.display = "none";
                            } else {
                                opt.disabled = false;
                                opt.style.display = "";
                            }
                        });
                        editTypeSelect.value = "account";
                    } else {
                        Array.from(editTypeSelect.options).forEach(opt => {
                            opt.disabled = false;
                            opt.style.display = "";
                        });
                    }
                } else {
                    editParentIdInput.value = '';
                    editParentDetailsDiv.style.display = 'none';
                    editParentDetailsDiv.innerHTML = '';
                    Array.from(editTypeSelect.options).forEach(opt => {
                        opt.disabled = false;
                        opt.style.display = "";
                    });
                }
            });

            // إرسال نموذج إضافة مركز تكلفة جديد
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (
                    window.trans?.adding || '{{ __('Adding...') }}');

                fetch('{{ route('cost-centers.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type') || '';
                        const data = contentType.includes('application/json') ? await response
                            .json() : {
                                success: false,
                                message: await response.text()
                            };
                        if (!response.ok) throw data;
                        return data;
                    })
                    .then(data => {
                        const modalEl = document.getElementById('addCostCenterModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap
                            .Modal(modalEl);
                        modalInstance.hide();
                        createForm.reset();
                        parentIdInput.value = '';
                        parentInput.value = '';
                        parentDetailsDiv.style.display = 'none';
                        parentDetailsDiv.innerHTML = '';
                        Array.from(typeSelect.options).forEach(opt => {
                            opt.disabled = false;
                            opt.style.display = "";
                        });

                        // جلب بيانات مراكز التكلفة الجديدة من السيرفر
                        fetch('{{ route('cost-centers.tree.data') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(newCostCentersData => {
                                const $tree = $('#jstree-context-menu');
                                if ($.fn && $tree.length && typeof $tree.jstree === 'function') {
                                    const treeInst = $tree.jstree(true);
                                    treeInst.settings.core.data = newCostCentersData;
                                    treeInst.refresh();
                                    treeInst.close_all();
                                }
                            });

                        Swal.fire({
                            title: '{{ __('Success') }}',
                            text: data.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    })
                    .catch(err => {
                        console.error('Save error:', err);
                        if (err && err.errors) {
                            showFormErrors(createForm, err.errors);
                        } else {
                            Swal.fire({
                                title: '{{ __('Error') }}',
                                text: err && err.message ? err.message :
                                    '{{ __('An error occurred while adding the cost center') }}',
                                icon: 'error'
                            });
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // إرسال نموذج تعديل مركز تكلفة
            editForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const formData = new FormData(this);
                formData.append('_method', 'PUT');
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (
                    window.trans?.saving || '{{ __('Saving...') }}');

                const costCenterId = editCostCenterIdInput.value;
                fetch(`/cost-centers/${costCenterId}`, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type') || '';
                        const data = contentType.includes('application/json') ? await response
                            .json() : {
                                success: false,
                                message: await response.text()
                            };
                        if (!response.ok) throw data;
                        return data;
                    })
                    .then(data => {
                        console.log(data);
                        const modalEl = document.getElementById('editCostCenterModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap
                            .Modal(modalEl);
                        modalInstance.hide();
                        editForm.reset();
                        editParentIdInput.value = '';
                        editParentInput.value = '';
                        editParentDetailsDiv.style.display = 'none';
                        editParentDetailsDiv.innerHTML = '';
                        Array.from(editTypeSelect.options).forEach(opt => {
                            opt.disabled = false;
                            opt.style.display = "";
                        });

                        // تحديث الشجرة
                        fetch('{{ route('cost-centers.tree.data') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(newCostCentersData => {
                                const $tree = $('#jstree-context-menu');
                                if ($.fn && $tree.length && typeof $tree.jstree === 'function') {
                                    const treeInst = $tree.jstree(true);
                                    treeInst.settings.core.data = newCostCentersData;
                                    treeInst.refresh();
                                    treeInst.close_all();
                                }
                            });

                        Swal.fire({
                            title: '{{ __('Success') }}',
                            text: data.message,
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    })
                    .catch(err => {
                        console.error('Update error:', err);
                        if (err && err.errors) {
                            showFormErrors(editForm, err.errors);
                        } else {
                            Swal.fire({
                                title: '{{ __('Error') }}',
                                text: err && err.message ? err.message :
                                    '{{ __('An error occurred while updating the cost center') }}',
                                icon: 'error'
                            });
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });
        });

        // دالة اختيار مركز التكلفة الأب من الشجرة (إضافة)
        function fillParentInModal(node) {
            const input = document.getElementById('parentCostCenterInput');
            const hiddenId = document.getElementById('parentCostCenterId');
            const detailsDiv = document.getElementById('parent-details');
            const typeSelect = document.getElementById('costCenterType');

            let matchedOption = Array.from(document.querySelectorAll('#costCentersList option'))
                .find(opt => opt.dataset.id == node.id);

            if (matchedOption) {
                input.value = `${matchedOption.dataset.name} — ${matchedOption.dataset.code}`;
                hiddenId.value = matchedOption.dataset.id;

                const slave = matchedOption.dataset.slave === "1" ? 1 : 0;
                const hasSub = matchedOption.dataset.has_sub === "1" ? 1 : 0;
                const name = matchedOption.dataset.name || '';
                const code = matchedOption.dataset.code || '';

                detailsDiv.style.display = 'block';
                detailsDiv.innerHTML = `
                    <b>{{ __('Cost Center Name') }}:</b> ${name}<br>
                    <b>{{ __('Cost Center Type') }}:</b> ${slave === 1 ? "{{ __('Sub Account') }}" : (hasSub === 1 ? "{{ __('Title') }}" : "{{ __('Account') }}")}<br>
                    <b>{{ __('Cost Center Code') }}:</b> ${code}
                `;

                if (hasSub === 1) {
                    Array.from(typeSelect.options).forEach(opt => {
                        if (opt.value !== "account") {
                            opt.disabled = true;
                            opt.style.display = "none";
                        } else {
                            opt.disabled = false;
                            opt.style.display = "";
                        }
                    });
                    typeSelect.value = "account";
                } else {
                    Array.from(typeSelect.options).forEach(opt => {
                        opt.disabled = false;
                        opt.style.display = "";
                    });
                }
            }
        }
    </script>

    @vite('resources/js/CostCenters.js')
@endsection
