@extends('layouts.app')

@section('title', __('Accounts Tree'))
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

        .parent-account-red {
            color: red;
        }

        .parent-account-blue {
            color: #1976d2;
        }
    </style>
@endpush
@section('content')
    {!! breadcrumb([['title' => __('Main Data')], ['title' => __('Accounts Tree')]]) !!}

    <div class="col-md-12 col-12">
        <div class="card">
            <h5 class="card-header d-flex justify-content-between align-items-center">
                {{ __('Accounts Tree') }}
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                    {{ __('Add Account') }}
                </button>
            </h5>
            <div class="card-body">
                <div id="jstree-context-menu" class="overflow-auto"></div>
            </div>
        </div>
    </div>

    <!-- Modal لإضافة حساب جديد -->
    <div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="addAccountForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addAccountModalLabel">{{ __('Add New Account') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Input للبحث -->
                        <div class="mb-3">
                            <label for="parentAccountInput" class="form-label">{{ __('Parent Account') }}</label>
                            <input class="form-control" list="accountsList" id="parentAccountInput"
                                placeholder="{{ __('Search for account by name or code') }}">
                            <input type="hidden" name="parent_id" id="parentAccountId"> <!-- هنخزن فيه الـ id -->
                        </div>

                        <!-- Datalist -->
                        <datalist id="accountsList">
                            @foreach ($allAccounts as $acc)
                                @if ($acc->slave == 0 || $acc->has_sub == 1)
                                    <option value="{{ $acc->name }} — {{ $acc->code }}" data-id="{{ $acc->id }}"
                                        data-slave="{{ (int) $acc->slave }}" data-code="{{ $acc->code }}"
                                        data-has_sub="{{ (int) $acc->has_sub }}" data-name="{{ $acc->name }}"
                                        data-type="{{ $acc->type ?? '' }}">
                                    </option>
                                @endif
                            @endforeach
                        </datalist>

                        <!-- تفاصيل الحساب -->
                        <div id="parent-details" class="mb-3"
                            style="display:none; background: #f8f9fa; border-radius: 6px; padding: 10px; font-size: 14px;">
                        </div>

                        <div class="mb-3">
                            <label for="accountType" class="form-label">{{ __('Account Type') }}</label>
                            <select class="form-select" id="accountType" name="type" required>
                                <option value="account">{{ __('Account') }}</option>
                                <option value="sub_account">{{ __('Sub Account') }}</option>
                                <option value="title">{{ __('Title') }}</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="accountName" class="form-label">{{ __('New Account Name') }}</label>
                            <input type="text" class="form-control" id="accountName" name="name" required>
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

    <!-- Modal لتعديل الحساب -->
    <div class="modal fade" id="editAccountModal" tabindex="-1" aria-labelledby="editAccountModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editAccountForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editAccountModalLabel">{{ __('Edit Account') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <div class="modal-body">

                        <!-- Hidden id -->
                        <input type="hidden" name="id" id="editAccountId">

                        <!-- الحساب الأب -->
                        <div class="mb-3">
                            <label for="editParentAccountInput" class="form-label">{{ __('Parent Account') }}</label>
                            <input class="form-control" list="accountsList" id="editParentAccountInput"
                                placeholder="{{ __('Search for account by name or code') }}">
                            <input type="hidden" name="parent_id" id="editParentAccountId">
                        </div>

                        <!-- تفاصيل الأب -->
                        <div id="edit-parent-details" class="mb-3"
                            style="display:none; background: #f8f9fa; border-radius: 6px; padding: 10px; font-size: 14px;">
                        </div>

                        <!-- نوع الحساب (معطل) -->
                        <div class="mb-3">
                            <label for="editAccountType" class="form-label">{{ __('Account Type') }}</label>
                            <select class="form-select" id="editAccountType" name="type">
                                <option value="account">{{ __('Account') }}</option>
                                <option value="sub_account">{{ __('Sub Account') }}</option>
                                <option value="title">{{ __('Title') }}</option>
                            </select>
                        </div>

                        <!-- الاسم (قابل للتعديل) -->
                        <div class="mb-3">
                            <label for="editAccountName" class="form-label">{{ __('New Account Name') }}</label>
                            <input type="text" class="form-control" id="editAccountName" name="name" required>
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
        window.accountsTreeDataUrl = "{{ route('accounts.tree.data') }}";
        const edit_label = {!! json_encode(__('Edit')) !!};
        const delete_label = {!! json_encode(__('Delete')) !!};
        const add_label = {!! json_encode(__('Add')) !!};
        const view_label = {!! json_encode(__('View')) !!};
        const deleted = {!! json_encode(__('Deleted')) !!};
        const error = {!! json_encode(__('Error')) !!};
        const account_deleted_successfully = {!! json_encode(__('Account deleted successfully')) !!};
        const account_updated_successfully = {!! json_encode(__('Account updated successfully')) !!};
        const account_created_successfully = {!! json_encode(__('Account created successfully')) !!};
        const account_creation_failed = {!! json_encode(__('Account creation failed')) !!};
        const error_occurred_while_deleting = {!! json_encode(__('Error occurred while deleting')) !!};
        const edit = {!! json_encode(__('Edit')) !!};
        const the_account_type_can_only_be_changed_to_account_because_the_parent_account_is_a_sub_account =
            {!! json_encode(
                __('The account type can only be changed to \"Account\" because the parent account is a sub-account.'),
            ) !!};
        const the_account_type_cannot_be_changed_because_the_account_is_a_subsidiary_and_has_branches =
            {!! json_encode(__('The account type cannot be changed because the account is a subsidiary and has branches.')) !!};
        const the_account_type_cannot_be_changed_because_the_account_is_an_address_and_has_branches =
            {!! json_encode(__('The account type cannot be changed because the account is an address and has branches')) !!};
        const cannot_edit_parent_account_because_it_has_children = {!! json_encode(__('Cannot edit parent account because it has children')) !!};
        const cannot_edit_parent_account_because_it_has_journal_entries = {!! json_encode(__('Cannot edit parent account because it has journal entries')) !!};
        const cannot_edit_parent_account_because_it_is_an_address = {!! json_encode(__('Cannot edit parent account because it is an address')) !!};
        const you_can_modify_the_parent_account_because_the_account_is_a_subsidiary_and_does_not_have_branches =
            {!! json_encode(
                __('You can modify the parent account because the account is a subsidiary and does not have branches'),
            ) !!};
        const cannot_fetch_account_data = {!! json_encode(__('Cannot fetch account data')) !!};
        const this_account_cannot_be_deleted_because_it_has_branches = {!! json_encode(__('This account cannot be deleted because it has branches')) !!};
        const cannot_delete_account_because_it_has_a_creditor_balance = {!! json_encode(__('Cannot delete account because it has a creditor balance')) !!};
        const cannot_delete_account_because_it_has_a_debtor_balance = {!! json_encode(__('Cannot delete account because it has a debtor balance')) !!};
        const cannot_delete_account = {!! json_encode(__('Cannot delete account')) !!};
        const confirm_delete = {!! json_encode(__('Confirm delete')) !!};
        const are_you_sure_you_want_to_delete = {!! json_encode(__('Are you sure you want to delete')) !!};
        const yes_delete_it = {!! json_encode(__('Yes, delete it')) !!};
        const no_cancel = {!! json_encode(__('No, cancel')) !!};
        const view_account_data = {!! json_encode(__('View Account Data')) !!};
        const close_it = {!! json_encode(__('Close')) !!};
        const cancel_it = {!! json_encode(__('Cancel')) !!};
    </script>
    <script src="{{ asset('assets/js/trance.js') }}"></script>

    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            const accountsData = {!! $accounts !!};
            window.initJsTree(accountsData);

            // متغيرات إضافة حساب
            const createForm = document.getElementById('addAccountForm');
            const parentInput = document.getElementById('parentAccountInput');
            const parentIdInput = document.getElementById('parentAccountId');
            const parentDetailsDiv = document.getElementById('parent-details');
            const typeSelect = document.getElementById('accountType');

            // متغيرات تعديل حساب
            const editForm = document.getElementById('editAccountForm');
            const editParentInput = document.getElementById('editParentAccountInput');
            const editParentIdInput = document.getElementById('editParentAccountId');
            const editParentDetailsDiv = document.getElementById('edit-parent-details');
            const editTypeSelect = document.getElementById('editAccountType');
            const editAccountIdInput = document.getElementById('editAccountId');
            const editAccountNameInput = document.getElementById('editAccountName');

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

            // تفريغ مودال إضافة حساب
            document.getElementById('addAccountModal').addEventListener('show.bs.modal', function() {
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

            // تفريغ مودال تعديل حساب
            document.getElementById('editAccountModal').addEventListener('show.bs.modal', function() {
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

            // اختيار حساب أب في إضافة
            parentInput.addEventListener('input', function() {
                const val = this.value.trim();
                let matchedOption = Array.from(document.querySelectorAll('#accountsList option'))
                    .find(opt => opt.value === val);

                if (matchedOption) {
                    parentIdInput.value = matchedOption.dataset.id;

                    // عرض التفاصيل
                    const slave = matchedOption.dataset.slave === "1" ? 1 : 0;
                    const hasSub = matchedOption.dataset.has_sub === "1" ? 1 : 0;
                    const name = matchedOption.dataset.name || '';
                    const type = matchedOption.dataset.type || '';
                    const code = matchedOption.dataset.code || '';

                    parentDetailsDiv.style.display = 'block';
                    parentDetailsDiv.innerHTML = `
                        <b>{{ __('Account Name') }}:</b> ${name}<br>
                        <b>{{ __('Account Type') }}:</b> ${slave === 1 ? "{{ __('Sub Account') }}" : (hasSub === 1 ? "{{ __('Title') }}" : "{{ __('Account') }}")}<br>
                        ${type ? `<b>{{ __('Title') }}:</b> ${type}<br>` : ""}
                        <b>{{ __('Account') }} {{ __('Code') }}:</b> ${code}
                    `;

                    // التحكم في نوع الحساب
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

            // اختيار حساب أب في تعديل
            editParentInput.addEventListener('input', function() {
                const val = this.value.trim();
                let matchedOption = Array.from(document.querySelectorAll('#accountsList option'))
                    .find(opt => opt.value === val);

                if (matchedOption) {
                    editParentIdInput.value = matchedOption.dataset.id;

                    // عرض التفاصيل
                    const slave = matchedOption.dataset.slave === "1" ? 1 : 0;
                    const hasSub = matchedOption.dataset.has_sub === "1" ? 1 : 0;
                    const name = matchedOption.dataset.name || '';
                    const type = matchedOption.dataset.type || '';
                    const code = matchedOption.dataset.code || '';

                    editParentDetailsDiv.style.display = 'block';
                    editParentDetailsDiv.innerHTML = `
                        <b>اسم الحساب:</b> ${name}<br>
                        <b>نوع الحساب:</b> ${slave === 1 ? "حساب فرعي" : (hasSub === 1 ? "عنوان" : "حساب")}<br>
                        ${type ? `<b>التصنيف:</b> ${type}<br>` : ""}
                        <b>كود الحساب:</b> ${code}
                    `;

                    // التحكم في نوع الحساب
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

            // إرسال نموذج إضافة حساب جديد
            createForm.addEventListener('submit', function(e) {
                e.preventDefault();
                clearFormErrors(this);

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (
                    window.trans?.adding || '{{ __('Adding...') }}');

                fetch('{{ route('accounts.store') }}', {
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
                        const modalEl = document.getElementById('addAccountModal');
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

                        // جلب بيانات الحسابات الجديدة من السيرفر
                        fetch('{{ route('accounts.tree.data') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(newAccountsData => {
                                const $tree = $('#jstree-context-menu');
                                if ($.fn && $tree.length && typeof $tree.jstree === 'function') {
                                    const treeInst = $tree.jstree(true);
                                    treeInst.settings.core.data = newAccountsData;
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
                                    '{{ __('An error occurred while adding the account') }}',
                                icon: 'error'
                            });
                        }
                    })
                    .finally(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    });
            });

            // إرسال نموذج تعديل حساب
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


                const accountId = editAccountIdInput.value;
                fetch(`/accounts/${accountId}`, {
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
                        const modalEl = document.getElementById('editAccountModal');
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
                        fetch('{{ route('accounts.tree.data') }}', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => response.json())
                            .then(newAccountsData => {
                                const $tree = $('#jstree-context-menu');
                                if ($.fn && $tree.length && typeof $tree.jstree === 'function') {
                                    const treeInst = $tree.jstree(true);
                                    treeInst.settings.core.data = newAccountsData;
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
                                    '{{ __('An error occurred while updating the account') }}',
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

        // دالة اختيار الحساب الأب من الشجرة (إضافة)
        function fillParentInModal(node) {
            const input = document.getElementById('parentAccountInput');
            const hiddenId = document.getElementById('parentAccountId');
            const detailsDiv = document.getElementById('parent-details');
            const typeSelect = document.getElementById('accountType');

            let matchedOption = Array.from(document.querySelectorAll('#accountsList option'))
                .find(opt => opt.dataset.id == node.id);

            if (matchedOption) {
                input.value = `${matchedOption.dataset.name} — ${matchedOption.dataset.code}`;
                hiddenId.value = matchedOption.dataset.id;

                const slave = matchedOption.dataset.slave === "1" ? 1 : 0;
                const hasSub = matchedOption.dataset.has_sub === "1" ? 1 : 0;
                const name = matchedOption.dataset.name || '';
                const type = matchedOption.dataset.type || '';
                const code = matchedOption.dataset.code || '';

                detailsDiv.style.display = 'block';
                detailsDiv.innerHTML = `
                    <b>اسم الحساب:</b> ${name}<br>
                    <b>نوع الحساب:</b> ${slave === 1 ? "حساب فرعي" : (hasSub === 1 ? "عنوان" : "حساب")}<br>
                    ${type ? `<b>التصنيف:</b> ${type}<br>` : ""}
                    <b>كود الحساب:</b> ${code}
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

    @vite('resources/js/treeview.js')
@endsection
