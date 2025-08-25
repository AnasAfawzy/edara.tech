import $ from 'jquery';
import 'jstree';

window.$ = $;
window.jQuery = $;

window.initJsTree = function (costCentersData) {
    if ($('#jstree-context-menu').length) {
        $('#jstree-context-menu').jstree({
            core: {
                themes: {
                    name: $('html').attr('data-bs-theme') === 'dark' ? 'default-dark' : 'default',
                    dots: true,
                    icons: true
                },
                check_callback: true,
                data: costCentersData
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
                                const costCenterId = node.id;

                                fetch(`/cost-centers/${costCenterId}/edit`, {
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
                                                text: data.message || cannot_fetch_cost_center_data
                                            });
                                            return;
                                        }

                                        const modal = new bootstrap.Modal(document.getElementById('editCostCenterModal'));
                                        modal.show();

                                        document.getElementById('editCostCenterId').value = data.cost_center.id;
                                        document.getElementById('editCostCenterName').value = data.cost_center.name;
                                        document.getElementById('editCostCenterType').value = data.type || data.cost_center.type || 'center';

                                        if (data.cost_center.ownerEl) {
                                            let parentOption = Array.from(document.querySelectorAll('#costCentersList option'))
                                                .find(opt => opt.dataset.id == data.cost_center.ownerEl);
                                            if (parentOption) {
                                                document.getElementById('editParentCostCenterInput').value = `${parentOption.dataset.name} — ${parentOption.dataset.code}`;
                                            } else {
                                                document.getElementById('editParentCostCenterInput').value = '';
                                            }
                                            document.getElementById('editParentCostCenterId').value = data.cost_center.ownerEl;
                                        } else {
                                            document.getElementById('editParentCostCenterInput').value = '';
                                            document.getElementById('editParentCostCenterId').value = '';
                                        }

                                        if (data.parent_details_html) {
                                            document.getElementById('edit-parent-details').style.display = 'block';
                                            document.getElementById('edit-parent-details').innerHTML = data.parent_details_html;
                                        } else {
                                            document.getElementById('edit-parent-details').style.display = 'none';
                                            document.getElementById('edit-parent-details').innerHTML = '';
                                        }

                                        const typeSelect = document.getElementById('editCostCenterType');
                                        let canChangeType = true;

                                        let parentOption = null;
                                        if (data.cost_center.ownerEl) {
                                            parentOption = Array.from(document.querySelectorAll('#costCentersList option'))
                                                .find(opt => opt.dataset.id == data.cost_center.ownerEl);
                                        }
                                        const parentHasSub = parentOption && parentOption.dataset.has_sub === "1";

                                        if (parentHasSub) {
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
                                            canChangeType = false;
                                        } else if (data.is_sub_account && data.has_children) {
                                            canChangeType = false;
                                        } else if (data.has_children && data.is_title) {
                                            canChangeType = false;
                                        } else {
                                            Array.from(typeSelect.options).forEach(opt => {
                                                opt.disabled = false;
                                                opt.style.display = "";
                                            });
                                        }

                                        if (!canChangeType) {
                                            typeSelect.setAttribute('disabled', 'disabled');
                                        } else {
                                            typeSelect.removeAttribute('disabled');
                                        }

                                        let parentInput = document.getElementById('editParentCostCenterInput');
                                        let canChangeParent = true;

                                        if (data.has_children) {
                                            canChangeParent = false;
                                        } else if (data.is_title) {
                                            canChangeParent = false;
                                        } else if (data.is_sub_account && !data.has_children) {
                                            canChangeParent = true;
                                        } else {
                                            canChangeParent = true;
                                        }

                                        if (!canChangeParent) {
                                            parentInput.setAttribute('disabled', 'disabled');
                                            document.getElementById('editParentCostCenterId').setAttribute('disabled', 'disabled');
                                        } else {
                                            parentInput.removeAttribute('disabled');
                                            document.getElementById('editParentCostCenterId').removeAttribute('disabled');
                                        }
                                    })
                                    .catch(() => {
                                        Swal.fire({
                                            icon: 'error',
                                            title: error,
                                            text: cannot_fetch_cost_center_data
                                        });
                                    });
                            }
                        },
                        delete: {
                            label: delete_label,
                            icon: "icon-base ti tabler-trash",
                            action: function () {
                                const costCenterId = node.id;
                                fetch(`/cost-centers/${costCenterId}/delete-info`, {
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        let errorMsg = '';
                                        if (data.has_children) {
                                            errorMsg = this_cost_center_cannot_be_deleted_because_it_has_branches;
                                        }

                                        if (errorMsg) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: error,
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
                                                fetch(`/cost-centers/${costCenterId}`, {
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
                                                                text: cost_center_deleted_successfully
                                                            });

                                                            fetch(window.costCentersTreeDataUrl, {
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
                                const costCenterId = node.id;
                                fetch(`/cost-centers/${costCenterId}/edit`, {
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
                                                text: data.message || cannot_fetch_cost_center_data
                                            });
                                            return;
                                        }
                                        const modal = new bootstrap.Modal(document.getElementById('editCostCenterModal'));
                                        modal.show();

                                        document.getElementById('editCostCenterModalLabel').textContent = view_cost_center_data + ' - ' + data.cost_center.name;

                                        document.getElementById('editCostCenterId').value = data.cost_center.id;
                                        document.getElementById('editCostCenterName').value = data.cost_center.name;
                                        document.getElementById('editCostCenterName').setAttribute('readonly', 'readonly');
                                        document.getElementById('editCostCenterType').value = data.type || data.cost_center.type || 'center';
                                        document.getElementById('editCostCenterType').setAttribute('disabled', 'disabled');
                                        document.getElementById('editParentCostCenterInput').setAttribute('readonly', 'readonly');
                                        document.getElementById('editParentCostCenterId').setAttribute('disabled', 'disabled');

                                        if (data.cost_center.ownerEl) {
                                            let parentOption = Array.from(document.querySelectorAll('#costCentersList option'))
                                                .find(opt => opt.dataset.id == data.cost_center.ownerEl);
                                            if (parentOption) {
                                                document.getElementById('editParentCostCenterInput').value = `${parentOption.dataset.name} — ${parentOption.dataset.code}`;
                                            } else {
                                                document.getElementById('editParentCostCenterInput').value = '';
                                            }
                                            document.getElementById('editParentCostCenterId').value = data.cost_center.ownerEl;
                                        } else {
                                            document.getElementById('editParentCostCenterInput').value = '';
                                            document.getElementById('editParentCostCenterId').value = '';
                                        }

                                        if (data.parent_details_html) {
                                            document.getElementById('edit-parent-details').style.display = 'block';
                                            document.getElementById('edit-parent-details').innerHTML = data.parent_details_html;
                                        } else {
                                            document.getElementById('edit-parent-details').style.display = 'none';
                                            document.getElementById('edit-parent-details').innerHTML = '';
                                        }

                                        document.querySelector('#editCostCenterForm button[type="submit"]').style.display = 'none';
                                        document.querySelector('#editCostCenterForm .btn-secondary').textContent = close_it;
                                    });
                            }
                        },
                    };

                    if (node.original.type === 'title' || node.original.type === 'sub_account') {
                        items.create = {
                            label: add_label,
                            icon: "icon-base ti tabler-plus",
                            action: function () {
                                const modal = new bootstrap.Modal(document.getElementById('addCostCenterModal'));
                                modal.show();

                                fillParentInModal(node);
                            }
                        };
                    }

                    return items;
                }
            }
        });

        document.getElementById('editCostCenterModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('editCostCenterName').removeAttribute('readonly');
            document.getElementById('editCostCenterType').removeAttribute('disabled');
            document.getElementById('editParentCostCenterInput').removeAttribute('readonly');
            document.getElementById('editParentCostCenterId').removeAttribute('disabled');
            document.querySelector('#editCostCenterForm button[type="submit"]').style.display = '';
            document.querySelector('#editCostCenterForm .btn-secondary').textContent = cancel_it;
        });

    }
};
