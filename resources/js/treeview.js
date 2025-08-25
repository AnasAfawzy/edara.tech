import $ from 'jquery';
import 'jstree';

window.$ = $;
window.jQuery = $;

window.initJsTree = function (accountsData) {
    if ($('#jstree-context-menu').length) {
        $('#jstree-context-menu').jstree({
            core: {
                themes: {
                    name: $('html').attr('data-bs-theme') === 'dark' ? 'default-dark' : 'default',
                    dots: true,
                    icons: true
                },
                check_callback: true,
                data: accountsData
            },
            plugins: ['types', 'contextmenu'],
            types: {
                default: {
                    icon: 'icon-base ti tabler-folder'
                },
                file: {
                    icon: 'icon-base ti tabler-file'
                }
            },
            contextmenu: {
                items: function (node) {
                    const items = {
                        edit: {
                            label: edit_label,
                            icon: "icon-base ti tabler-edit",
                            action: function () {
                                const accountId = node.id;

                                fetch(`/accounts/${accountId}/edit`, {
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (!data.success) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: error,
                                                text: data.message || cannot_fetch_account_data
                                            });
                                            return;
                                        }

                                        const modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
                                        modal.show();
                                        console.log(data);
                                        document.getElementById('editAccountId').value = data.account.id;
                                        document.getElementById('editAccountName').value = data.account.name;
                                        document.getElementById('editAccountType').value = data.type || data.account.type || 'account';

                                        if (data.account.ownerEl) {
                                            let parentOption = Array.from(document.querySelectorAll('#accountsList option'))
                                                .find(opt => opt.dataset.id == data.account.ownerEl);
                                            if (parentOption) {
                                                document.getElementById('editParentAccountInput').value = `${parentOption.dataset.name} — ${parentOption.dataset.code}`;
                                            } else {
                                                document.getElementById('editParentAccountInput').value = '';
                                            }
                                            document.getElementById('editParentAccountId').value = data.account.ownerEl;
                                        } else {
                                            document.getElementById('editParentAccountInput').value = '';
                                            document.getElementById('editParentAccountId').value = '';
                                        }

                                        if (data.parent_details_html) {
                                            document.getElementById('edit-parent-details').style.display = 'block';
                                            document.getElementById('edit-parent-details').innerHTML = data.parent_details_html;
                                        } else {
                                            document.getElementById('edit-parent-details').style.display = 'none';
                                            document.getElementById('edit-parent-details').innerHTML = '';
                                        }

                                        const typeSelect = document.getElementById('editAccountType');
                                        let typeMsg = '';
                                        let canChangeType = true;

                                        // بيانات الأب
                                        let parentOption = null;
                                        if (data.account.ownerEl) {
                                            parentOption = Array.from(document.querySelectorAll('#accountsList option'))
                                                .find(opt => opt.dataset.id == data.account.ownerEl);
                                        }
                                        const parentIsSub = parentOption && parentOption.dataset.slave === "1";

                                        if (parentIsSub) {
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
                                            typeMsg = '<div class="text-danger mt-2">' + the_account_type_can_only_be_changed_to_account_because_the_parent_account_is_a_sub_account + '</div>';
                                            canChangeType = false;

                                        } else if (data.is_sub_account && data.has_children) {
                                            typeMsg = '<div class="text-danger mt-2">' + the_account_type_cannot_be_changed_because_the_account_is_a_subsidiary_and_has_branches + '</div>';
                                            canChangeType = false;

                                        } else if (data.has_children && data.is_title) {
                                            typeMsg = '<div class="text-danger mt-2">' + the_account_type_cannot_be_changed_because_the_account_is_an_address_and_has_branches + '</div>';
                                            canChangeType = false;

                                        } else {
                                            Array.from(typeSelect.options).forEach(opt => {
                                                opt.disabled = false;
                                                opt.style.display = "";
                                            });
                                            typeMsg = '<div class="text-success mt-2">يمكنك تغيير نوع الحساب بحرية.</div>';
                                        }

                                        let typeInputDiv = typeSelect.parentElement;
                                        let oldTypeMsg = typeInputDiv.querySelector('.type-edit-msg');
                                        if (oldTypeMsg) oldTypeMsg.remove();
                                        let typeMsgDiv = document.createElement('div');
                                        typeMsgDiv.className = 'type-edit-msg';
                                        // typeMsgDiv.innerHTML = typeMsg;
                                        typeInputDiv.appendChild(typeMsgDiv);

                                        if (!canChangeType) {
                                            typeSelect.setAttribute('disabled', 'disabled');
                                        } else {
                                            typeSelect.removeAttribute('disabled');
                                        }

                                        let parentInput = document.getElementById('editParentAccountInput');
                                        let parentMsg = '';
                                        let canChangeParent = true;

                                        if (data.has_children) {
                                            parentMsg = '<div class="text-danger mt-2">' + cannot_edit_parent_account_because_it_has_children + '</div>';
                                            canChangeParent = false;

                                        } else if (data.has_journals) {
                                            parentMsg = '<div class="text-danger mt-2">' + Cannot_edit_parent_account_because_it_has_journal_entries + '</div>';
                                            canChangeParent = false;

                                        } else if (data.is_title) {
                                            parentMsg = '<div class="text-danger mt-2">' + Cannot_edit_parent_account_because_it_is_an_address + '</div>';
                                            canChangeParent = false;

                                        } else if (data.is_sub_account && !data.has_children) {
                                            parentMsg = '<div class="text-warning mt-2">' + you_can_modify_the_parent_account_because_the_account_is_a_subsidiary_and_does_not_have_branches + '</div>';
                                            canChangeParent = true;

                                        } else {
                                            parentMsg = '<div class="text-success mt-2">' + you_can_change_the_parent_account + '</div>';
                                            canChangeParent = true;
                                        }

                                        let parentInputDiv = parentInput.parentElement;
                                        let oldParentMsg = parentInputDiv.querySelector('.parent-edit-msg');
                                        if (oldParentMsg) oldParentMsg.remove();
                                        let parentMsgDiv = document.createElement('div');
                                        parentMsgDiv.className = 'parent-edit-msg';
                                        parentMsgDiv.innerHTML = parentMsg;
                                        parentInputDiv.appendChild(parentMsgDiv);

                                        if (!canChangeParent) {
                                            parentInput.setAttribute('disabled', 'disabled');
                                            document.getElementById('editParentAccountId').setAttribute('disabled', 'disabled');
                                        } else {
                                            parentInput.removeAttribute('disabled');
                                            document.getElementById('editParentAccountId').removeAttribute('disabled');
                                        }
                                    })

                                    .catch(() => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: error,
                                            text: cannot_fetch_account_data
                                        });
                                    });
                            }
                        },
                        delete: {
                            label: delete_label,
                            icon: "icon-base ti tabler-trash",
                            action: function () {
                                const accountId = node.id;
                                fetch(`/accounts/${accountId}/delete-info`, {
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        let errorMsg = '';
                                        if (data.has_children) {
                                            errorMsg = this_account_cannot_be_deleted_because_it_has_branches;
                                        } else if (data.creditor != 0 && data.creditor != null) {
                                            errorMsg = cannot_delete_account_because_it_has_a_creditor_balance;
                                        } else if (data.debtor != 0 && data.debtor != null) {
                                            errorMsg = cannot_delete_account_because_it_has_a_debtor_balance;
                                        }

                                        if (errorMsg) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: cannot_delete_account,
                                                text: errorMsg
                                            });
                                            return;
                                        }

                                        Swal.fire({
                                            title: confirm_delete,
                                            text: are_you_sure_you_want_to_delete + " " + node.text + "؟",
                                            icon: "warning",
                                            showCancelButton: true,
                                            confirmButtonText: yes_delete_it,
                                            cancelButtonText: no_cancel
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                fetch(`/accounts/${accountId}`, {
                                                        method: 'DELETE',
                                                        headers: {
                                                            'X-Requested-With': 'XMLHttpRequest',
                                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                                        }
                                                    })
                                                    .then(res => res.json())
                                                    .then(resp => {
                                                        if (resp.success) {
                                                            Swal.fire({
                                                                icon: 'success',
                                                                title: deleted,
                                                                text: account_deleted_successfully
                                                            });

                                                            // تحديث الشجرة بنفس طريقة index.blade.php
                                                            fetch(window.accountsTreeDataUrl, {
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
                                                        } else {
                                                            Swal.fire({
                                                                icon: 'error',
                                                                title: error,
                                                                text: resp.message || error_occurred_while_deleting
                                                            });
                                                        }
                                                    })
                                                    .catch(() => {
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: error,
                                                            text: error_occurred_while_deleting
                                                        });
                                                    });
                                            }
                                        });
                                    });
                            }
                        },
                        view: {
                            label: view_label,
                            icon: "jstree-file",
                            action: function () {
                                const accountId = node.id;
                                fetch(`/accounts/${accountId}/edit`, {
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (!data.success) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: error,
                                                text: data.message || cannot_fetch_account_data
                                            });
                                            return;
                                        }
                                        // فتح المودال
                                        const modal = new bootstrap.Modal(document.getElementById('editAccountModal'));
                                        modal.show();

                                        // تغيير عنوان المودال
                                        document.getElementById('editAccountModalLabel').textContent = view_account_data + ' - ' + data.account.name;

                                        // تعبئة الحقول
                                        document.getElementById('editAccountId').value = data.account.id;
                                        document.getElementById('editAccountName').value = data.account.name;
                                        document.getElementById('editAccountType').value = data.account.type || 'account';
                                        document.getElementById('editAccountName').setAttribute('readonly', 'readonly');
                                        document.getElementById('editAccountType').setAttribute('disabled', 'disabled');
                                        document.getElementById('editParentAccountInput').setAttribute('readonly', 'readonly');
                                        document.getElementById('editParentAccountId').setAttribute('disabled', 'disabled');

                                        // بيانات الأب
                                        if (data.account.ownerEl) {
                                            let parentOption = Array.from(document.querySelectorAll('#accountsList option'))
                                                .find(opt => opt.dataset.id == data.account.ownerEl);
                                            if (parentOption) {
                                                document.getElementById('editParentAccountInput').value = `${parentOption.dataset.name} — ${parentOption.dataset.code}`;
                                            } else {
                                                document.getElementById('editParentAccountInput').value = '';
                                            }
                                            document.getElementById('editParentAccountId').value = data.account.ownerEl;
                                        } else {
                                            document.getElementById('editParentAccountInput').value = '';
                                            document.getElementById('editParentAccountId').value = '';
                                        }

                                        // تفاصيل الأب
                                        if (data.parent_details_html) {
                                            document.getElementById('edit-parent-details').style.display = 'block';
                                            document.getElementById('edit-parent-details').innerHTML = data.parent_details_html;
                                        } else {
                                            document.getElementById('edit-parent-details').style.display = 'none';
                                            document.getElementById('edit-parent-details').innerHTML = '';
                                        }

                                        // إخفاء زر التحديث وإظهار زر إغلاق فقط
                                        document.querySelector('#editAccountForm button[type="submit"]').style.display = 'none';
                                        document.querySelector('#editAccountForm .btn-secondary').textContent = close_it;
                                    });
                            }
                        },
                    };

                    if (node.original.type === 'title' || node.original.type === 'sub_account') {
                        items.create = {
                            label: add_label,
                            icon: "icon-base ti tabler-plus",
                            action: function () {
                                const modal = new bootstrap.Modal(document.getElementById('addAccountModal'));
                                modal.show();

                                fillParentInModal(node);
                            }
                        };
                    }

                    return items;
                }
            }
        });


        document.getElementById('editAccountModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('editAccountName').removeAttribute('readonly');
            document.getElementById('editAccountType').removeAttribute('disabled');
            document.getElementById('editParentAccountInput').removeAttribute('readonly');
            document.getElementById('editParentAccountId').removeAttribute('disabled');
            document.querySelector('#editAccountForm button[type="submit"]').style.display = '';
            document.querySelector('#editAccountForm .btn-secondary').textContent = cancel_it;
        });

    }
};
