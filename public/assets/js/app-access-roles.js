/**
 * App user list
 */

'use strict';
window.dt_User = null;
// Datatable (js)
document.addEventListener('DOMContentLoaded', function (e) {
  const dtUserTable = document.querySelector('.datatables-users'),
    statusObj = {
      1: { title: 'Pending', class: 'bg-label-warning' },
      2: { title: 'Active', class: 'bg-label-success' },
      3: { title: 'Inactive', class: 'bg-label-secondary' }
    };
  let dt_User,
    userView = 'app-user-view-account.html';

  // Users List datatable
  if (dtUserTable) {
    const userRole = document.createElement('div');
    userRole.classList.add('user_role');
    const userPlan = document.createElement('div');
    userPlan.classList.add('user_plan');
    window.dt_User = new DataTable(dtUserTable, {
                ajax: 'users_table/search',
                columns: [{
                        data: 'id'
                    },
                    {
                        data: 'id',
                        orderable: false,
                        render: DataTable.render.select()
                    },
                    {
                        data: 'full_name'
                    },
                    {
                        data: 'role'
                    },
                    {
                        data: 'id'
                    } // actions
                ],
                columnDefs: [
                    // ...ابقِ فقط التعريفات الخاصة بالأعمدة التي أبقيتها...
                    // احذف أو علّق أي تعريف targets: 4, 5, 6 (plan, billing, status)
                    // وعدّل أرقام targets حسب الترتيب الجديد
                    {
                        // For Responsive
                        className: 'control',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 5,
                        targets: 0,
                        render: function (data, type, full, meta) {
                            return '';
                        }
                    },
                    {
                        // For Checkboxes
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        responsivePriority: 3,
                        checkboxes: true,
                        render: function () {
                            return '<input type="checkbox" class="dt-checkboxes form-check-input">';
                        },
                        checkboxes: {
                            selectAllRender: '<input type="checkbox" class="form-check-input">'
                        }
                    },
                    {
                        targets: 2,
                        responsivePriority: 1,
                        render: function (data, type, full, meta) {
                            const name = full['full_name'];
                            const email = full['email'];
                            const image = full['avatar'];
                            let output;

                            if (image) {
                                output = `<img src="${assetsPath}img/avatars/${image}" alt="Avatar" class="rounded-circle">`;
                            } else {
                                const stateNum = Math.floor(Math.random() * 6) + 1;
                                const states = ['success', 'danger', 'warning', 'info', 'dark', 'primary', 'secondary'];
                                const state = states[stateNum];
                                const initials = (name.match(/\b\w/g) || []).slice(0, 2).join('').toUpperCase();
                                output = `<span class="avatar-initial rounded-circle bg-label-${state}">${initials}</span>`;
                            }

                            return `
          <div class="d-flex justify-content-left align-items-center role-name">
            <div class="avatar-wrapper">
              <div class="avatar avatar-sm me-3">
                ${output}
              </div>
            </div>
            <div class="d-flex flex-column">
              <a href="${userView}" class="text-heading text-truncate"><span class="fw-medium">${name}</span></a>
              <small>@${email}</small>
            </div>
          </div>
        `;
                        }
                    },
                    {
                        targets: 3,
                        render: function (data, type, full, meta) {
                            const role = full['role'];
                            const roleBadgeObj = {
                                Subscriber: '<span class="me-2"><i class="icon-base ti tabler-user icon-22px text-success"></i></span>',
                                Author: '<span class="me-2"><i class="icon-base ti tabler-device-desktop icon-22px text-danger"></i></span>',
                                Maintainer: '<span class="me-2"><i class="icon-base ti tabler-chart-pie icon-22px text-info"></i></span>',
                                Editor: '<span class="me-2"><i class="icon-base ti tabler-edit icon-22px text-warning"></i></span>',
                                Admin: '<span class="me-2"><i class="icon-base ti tabler-crown icon-22px text-primary"></i></span>'
                            };

                            return `<span class='text-truncate d-flex align-items-center'>${roleBadgeObj[role] || ''}${role}</span>`;
                        }
                    },
                    {
                        targets: 4,
                        title: 'Actions',
                        searchable: false,
                        orderable: false,
                        render: function (data, type, full, meta) {
                        return `
                        <div class="d-flex align-items-center">
                            <a href="javascript:;" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-record me-2" 
                            data-user-id="${data}" 
                            data-user-name="${full['full_name']}" 
                            data-user-email="${full['email']}" 
                            data-user-role-name="${full['role']}"
                            title="${window.translations?.edit || 'Edit'}">
                            <i class="icon-base ti tabler-edit icon-md"></i>
                            </a>
                            <a href="javascript:;" class="btn btn-icon btn-text-secondary rounded-pill waves-effect delete-record" 
                            data-user-id="${data}" 
                            title="${window.translations?.delete || 'Delete'}">
                            <i class="icon-base ti tabler-trash icon-md"></i>
                            </a>
                        </div>
                        `;
                        }
                    }
                ],
      select: {
        style: 'multi',
        selector: 'td:nth-child(2)'
      },
      order: [[2, 'desc']],
      layout: {
        topStart: {
          rowClass: 'row my-md-0 me-3 ms-0 justify-content-between',
          features: [
            {
              pageLength: {
                menu: [10, 25, 50, 100],
                text: '_MENU_'
              }
            }
          ]
        },
        topEnd: {
          features: [
            {
              search: {
                placeholder: window.translations?.Search_User || 'Search User',
                text: '_INPUT_'
              }
            },
            {
              buttons: [
                {
                  extend: 'collection',
                  className: 'btn btn-label-secondary dropdown-toggle me-4',
                  text: '<span class="d-flex align-items-center gap-1"><i class="icon-base ti tabler-upload icon-xs"></i> <span class="d-inline-block">' + (window.translations?.Export || 'Export') + '</span></span>',
                  buttons: [
                    {
                      extend: 'print',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-printer me-1"></i>Print</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Check if inner is HTML content
                            if (inner.indexOf('<') > -1) {
                              const parser = new DOMParser();
                              const doc = parser.parseFromString(inner, 'text/html');

                              // Get all text content
                              let text = '';

                              // Handle specific elements
                              const userNameElements = doc.querySelectorAll('.role-name');
                              if (userNameElements.length > 0) {
                                userNameElements.forEach(el => {
                                  // Get text from nested structure
                                  const nameText =
                                    el.querySelector('.fw-medium')?.textContent ||
                                    el.querySelector('.d-block')?.textContent ||
                                    el.textContent;
                                  text += nameText.trim() + ' ';
                                });
                              } else {
                                // Get regular text content
                                text = doc.body.textContent || doc.body.innerText;
                              }

                              return text.trim();
                            }

                            return inner;
                          }
                        }
                      },
                      customize: function (win) {
                        win.document.body.style.color = config.colors.headingColor;
                        win.document.body.style.borderColor = config.colors.borderColor;
                        win.document.body.style.backgroundColor = config.colors.bodyBg;
                        const table = win.document.body.querySelector('table');
                        table.classList.add('compact');
                        table.style.color = 'inherit';
                        table.style.borderColor = 'inherit';
                        table.style.backgroundColor = 'inherit';
                      }
                    },
                    {
                      extend: 'csv',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file me-1"></i>Csv</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle role-name elements specifically
                            const userNameElements = doc.querySelectorAll('.role-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'excel',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-export me-1"></i>Excel</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle role-name elements specifically
                            const userNameElements = doc.querySelectorAll('.role-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    {
                      extend: 'pdf',
                      text: `<span class="d-flex align-items-center"><i class="icon-base ti tabler-file-text me-1"></i>Pdf</span>`,
                      className: 'dropdown-item',
                      exportOptions: {
                        columns: [2, 3, 4],
                        format: {
                          body: function (inner, coldex, rowdex) {
                            if (inner.length <= 0) return inner;

                            // Parse HTML content
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(inner, 'text/html');

                            let text = '';

                            // Handle role-name elements specifically
                            const userNameElements = doc.querySelectorAll('.role-name');
                            if (userNameElements.length > 0) {
                              userNameElements.forEach(el => {
                                // Get text from nested structure - try different selectors
                                const nameText =
                                  el.querySelector('.fw-medium')?.textContent ||
                                  el.querySelector('.d-block')?.textContent ||
                                  el.textContent;
                                text += nameText.trim() + ' ';
                              });
                            } else {
                              // Handle other elements (status, role, etc)
                              text = doc.body.textContent || doc.body.innerText;
                            }

                            return text.trim();
                          }
                        }
                      }
                    },
                    // {
                    //   extend: 'copy',
                    //   text: `<i class="icon-base ti tabler-copy me-1"></i>Copy`,
                    //   className: 'dropdown-item',
                    //   exportOptions: {
                    //     columns: [2, 3, 4],
                    //     format: {
                    //       body: function (inner, coldex, rowdex) {
                    //         if (inner.length <= 0) return inner;

                    //         // Parse HTML content
                    //         const parser = new DOMParser();
                    //         const doc = parser.parseFromString(inner, 'text/html');

                    //         let text = '';

                    //         // Handle role-name elements specifically
                    //         const userNameElements = doc.querySelectorAll('.role-name');
                    //         if (userNameElements.length > 0) {
                    //           userNameElements.forEach(el => {
                    //             // Get text from nested structure - try different selectors
                    //             const nameText =
                    //               el.querySelector('.fw-medium')?.textContent ||
                    //               el.querySelector('.d-block')?.textContent ||
                    //               el.textContent;
                    //             text += nameText.trim() + ' ';
                    //           });
                    //         } else {
                    //           // Handle other elements (status, role, etc)
                    //           text = doc.body.textContent || doc.body.innerText;
                    //         }

                    //         return text.trim();
                    //       }
                    //     }
                    //   }
                    // }
                  ]
                },
                {
                  text: '<i class="icon-base ti tabler-plus me-0 me-sm-1 icon-16px"></i><span class="d-none d-sm-inline-block">' + (window.translations?.add_user || 'Add User') + '</span>',
                  className: 'add-new btn btn-primary rounded-2 waves-effect waves-light',
                  attr: {
                    'data-bs-toggle': 'modal',
                    'data-bs-target': '#addUserModal'
                  }
                }
              ]
            }
          ]
        },
        bottomStart: {
          rowClass: 'row mx-3 justify-content-between',
          features: ['info']
        },
        bottomEnd: 'paging'
      },
      language: {
        paginate: {
          next: '<i class="icon-base ti tabler-chevron-right scaleX-n1-rtl icon-18px"></i>',
          previous: '<i class="icon-base ti tabler-chevron-left scaleX-n1-rtl icon-18px"></i>',
          first: '<i class="icon-base ti tabler-chevrons-left scaleX-n1-rtl icon-18px"></i>',
          last: '<i class="icon-base ti tabler-chevrons-right scaleX-n1-rtl icon-18px"></i>'
        },
        info: window.translations?.pagination_info || 'Showing _START_ to _END_ of _TOTAL_ entries',
        infoEmpty: window.translations?.pagination_info_empty || 'No entries to show',
        lengthMenu: window.translations?.pagination_length_menu || 'Show _MENU_ entries',
        search: window.translations?.pagination_search || 'Search:',
        zeroRecords: window.translations?.pagination_zero_records || 'No matching records found',
        emptyTable: window.translations?.pagination_empty_table || 'No data available in table',
        infoFiltered: window.translations?.pagination_info_filtered || '(filtered from _MAX_ total entries)',
      },
      // For responsive popup
      responsive: {
        details: {
          display: DataTable.Responsive.display.modal({
            header: function (row) {
              const data = row.data();
              return 'Details of ' + data['full_name'];
            }
          }),
          type: 'column',
          renderer: function (api, rowIdx, columns) {
            const data = columns
              .map(function (col) {
                return col.title !== '' // Do not show row in modal popup if title is blank (for check box)
                  ? `<tr data-dt-row="${col.rowIndex}" data-dt-column="${col.columnIndex}">
                      <td>${col.title}:</td>
                      <td>${col.data}</td>
                    </tr>`
                  : '';
              })
              .join('');

            if (data) {
              const div = document.createElement('div');
              div.classList.add('table-responsive');
              const table = document.createElement('table');
              div.appendChild(table);
              table.classList.add('table');
              const tbody = document.createElement('tbody');
              tbody.innerHTML = data;
              table.appendChild(tbody);
              return div;
            }
            return false;
          }
        }
      }
    });


    // Initial event binding
    // bindDeleteEvent();

    // // Re-bind events when modal is shown or hidden
    // document.addEventListener('show.bs.modal', function (event) {
    //   if (event.target.classList.contains('dtr-bs-modal')) {
    //     bindDeleteEvent();
    //   }
    // });

    // document.addEventListener('hide.bs.modal', function (event) {
    //   if (event.target.classList.contains('dtr-bs-modal')) {
    //     bindDeleteEvent();
    //   }
    // });
  }

  // Filter form control to default size
  // ? setTimeout used for multilingual table initialization
  setTimeout(() => {
    const elementsToModify = [
      { selector: '.dt-buttons .btn', classToRemove: 'btn-secondary' },
      { selector: '.dt-buttons.btn-group .btn-group', classToRemove: 'btn-group' },
      { selector: '.dt-buttons.btn-group', classToRemove: 'btn-group', classToAdd: 'd-flex' },
      { selector: '.dt-search .form-control', classToRemove: 'form-control-sm' },
      { selector: '.dt-length .form-select', classToRemove: 'form-select-sm' },
      { selector: '.dt-length', classToAdd: 'mb-md-6 mb-0' },
      { selector: '.dt-layout-start', classToAdd: 'ps-3 mt-0' },
      {
        selector: '.dt-layout-end',
        classToRemove: 'justify-content-between',
        classToAdd: 'justify-content-md-between justify-content-center d-flex flex-wrap gap-4 mt-0 mb-md-0 mb-6'
      },
      { selector: '.dt-layout-table', classToRemove: 'row mt-2' },
      { selector: '.dt-layout-full', classToRemove: 'col-md col-12', classToAdd: 'table-responsive' }
    ];

    // Delete record
    elementsToModify.forEach(({ selector, classToRemove, classToAdd }) => {
      document.querySelectorAll(selector).forEach(element => {
        if (classToRemove) {
          classToRemove.split(' ').forEach(className => element.classList.remove(className));
        }
        if (classToAdd) {
          classToAdd.split(' ').forEach(className => element.classList.add(className));
        }
      });
    });
  }, 100);

  // On edit role click, update text
  var roleEditList = document.querySelectorAll('.role-edit-modal'),
    roleAdd = document.querySelector('.add-new-role'),
    roleTitle = document.querySelector('.role-title');

  roleAdd.onclick = function () {
    roleTitle.innerHTML = 'Add New Role'; // reset text
  };
  if (roleEditList) {
    roleEditList.forEach(function (roleEditEl) {
      roleEditEl.onclick = function () {
        roleTitle.innerHTML = 'Edit Role'; // reset text
      };
    });
  }
});
