'use strict';

// تعريف الترجمات كمتغيرات JS
window.warehouseTranslations = window.warehouseTranslations || {
    error: 'خطأ',
    success: 'نجح',
    areYouSure: 'هل أنت متأكد؟',
    confirmDeleteMessage: 'سيتم حذف هذا المخزن نهائياً',
    yesDelete: 'نعم، احذف',
    cancel: 'إلغاء',
    adding: 'جاري الإضافة...',
    updating: 'جاري التحديث...',
    addWarehouse: 'إضافة مخزن',
    updateWarehouse: 'تحديث المخزن',
    anErrorOccurredWhileLoadingData: 'حدث خطأ أثناء تحميل البيانات',
    anErrorOccurredWhileAddingWarehouse: 'حدث خطأ أثناء إضافة المخزن',
    anErrorOccurredWhileUpdatingWarehouse: 'حدث خطأ أثناء تحديث المخزن',
    anErrorOccurredWhileDeletingWarehouse: 'حدث خطأ أثناء حذف المخزن'
};

// دالة لإغلاق المودال بشكل صحيح
function closeModal(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;

    try {
        // محاولة استخدام Bootstrap Modal instance
        const modalInstance = bootstrap.Modal.getInstance(modalElement);
        if (modalInstance) {
            modalInstance.hide();
        } else {
            // إنشاء instance جديد وإغلاقه
            const newModalInstance = new bootstrap.Modal(modalElement);
            newModalInstance.hide();
        }
    } catch (error) {
        console.error('Error closing modal with Bootstrap:', error);
        // Fallback manual close
        forceCloseModal(modalElement);
    }

    // التأكد من إزالة الـ backdrop والـ classes بعد فترة قصيرة
    setTimeout(() => {
        cleanupModalBackdrop();
    }, 300);
}

// دالة لإغلاق المودال يدوياً
function forceCloseModal(modalElement) {
    // إخفاء المودال
    modalElement.style.display = 'none';
    modalElement.setAttribute('aria-hidden', 'true');
    modalElement.removeAttribute('aria-modal');
    modalElement.removeAttribute('role');

    // إزالة classes من body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';

    // إزالة backdrop
    cleanupModalBackdrop();
}

// دالة لتنظيف backdrop
function cleanupModalBackdrop() {
    // إزالة جميع modal backdrops
    const backdrops = document.querySelectorAll('.modal-backdrop');
    backdrops.forEach(backdrop => {
        backdrop.remove();
    });

    // إزالة modal-open class من body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = '';
    document.body.style.paddingRight = '';

    // إزالة أي inline styles قد تكون مضافة
    const body = document.body;
    if (body.style.overflow === 'hidden') {
        body.style.overflow = '';
    }
}

function reloadWarehousesTable() {
    const searchInput = document.getElementById('wh-search');
    const perPageSelect = document.getElementById('wh-perPage');
    const tableBody = document.querySelector('#warehousesTable tbody');

    // التحقق من وجود العناصر المطلوبة
    if (!searchInput || !perPageSelect || !tableBody) {
        console.error('Required elements not found', {
            searchInput: !!searchInput,
            perPageSelect: !!perPageSelect,
            tableBody: !!tableBody
        });

        // عرض رسالة خطأ للمستخدم
        Swal.fire({
            title: window.warehouseTranslations.error,
            text: 'عناصر الصفحة غير متوفرة، يرجى إعادة تحميل الصفحة',
            icon: 'error',
            confirmButtonText: 'إعادة تحميل الصفحة'
        }).then(() => {
            window.location.reload();
        });
        return;
    }

    const search = searchInput.value.trim() || '';
    const perPage = perPageSelect.value || 10;


    // إضافة loading indicator مع animation محسن
    tableBody.innerHTML = `
        <tr>
            <td colspan="4" class="text-center">
                <div class="d-flex justify-content-center align-items-center py-5">
                    <div class="spinner-border spinner-border-sm text-primary me-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="text-muted">جاري البحث...</span>
                </div>
            </td>
        </tr>
    `;

    // بناء URL بشكل أكثر أماناً
    const searchUrl = new URL('/warehouses/search', window.location.origin);
    searchUrl.searchParams.set('search', search);
    searchUrl.searchParams.set('perPage', perPage);

    // إضافة timeout للطلب
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 ثوانٍ

    fetch(searchUrl.toString(), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ?.getAttribute('content') || ''
            },
            signal: controller.signal
        })
        .then(response => {
            clearTimeout(timeoutId);

            // التحقق من نوع المحتوى
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`نوع استجابة غير متوقع: ${contentType || 'غير محدد'}`);
            }

            if (!response.ok) {
                // معالجة أخطاء HTTP المختلفة
                switch (response.status) {
                    case 404:
                        throw new Error('صفحة البحث غير موجودة');
                    case 403:
                        throw new Error('ليس لديك صلاحية للوصول');
                    case 500:
                        throw new Error('خطأ في الخادم، يرجى المحاولة لاحقاً');
                    case 429:
                        throw new Error('تم تجاوز الحد المسموح من الطلبات');
                    default:
                        throw new Error(`خطأ HTTP ${response.status}: ${response.statusText}`);
                }
            }

            return response.json();
        })
        .then(data => {


            // التحقق من صحة البيانات المستلمة
            if (!data || typeof data !== 'object') {
                throw new Error('البيانات المستلمة غير صحيحة');
            }

            if (data.success === false) {
                throw new Error(data.message || 'فشل في البحث');
            }

            if (!data.html) {
                throw new Error('لم يتم استلام HTML للجدول');
            }

            // تحديث الجدول
            try {
                tableBody.outerHTML = data.html;
                console.log('Table updated successfully');

                // إعادة تفعيل Event Listeners
                attachEventListeners();

                // عرض معلومات النتائج (اختياري)
                if (search.trim() !== '') {
                    const resultMessage = data.count > 0 ?
                        `تم العثور على ${data.count} مخزن` :
                        'لم يتم العثور على نتائج';

                    // يمكن عرض رسالة مؤقتة هنا إذا أردت
                    console.log(resultMessage);
                }

            } catch (updateError) {
                console.error('Error updating table:', updateError);
                throw new Error('فشل في تحديث الجدول');
            }
        })
        .catch(error => {
            clearTimeout(timeoutId);
            console.error('Search error:', error);

            let errorMessage = 'حدث خطأ في البحث';
            let actionButton = '';

            // تحديد نوع الخطأ وتخصيص الرسالة
            if (error.name === 'AbortError') {
                errorMessage = 'انتهت مهلة البحث، يرجى المحاولة مرة أخرى';
            } else if (error.message.includes('Failed to fetch')) {
                errorMessage = 'مشكلة في الاتصال بالخادم';
                actionButton = `
                    <button class="btn btn-sm btn-outline-warning me-2" onclick="window.location.reload()">
                        <i class="icon-base ti tabler-refresh me-1"></i>
                        إعادة تحميل الصفحة
                    </button>
                `;
            } else {
                errorMessage = error.message || errorMessage;
            }

            // عرض رسالة الخطأ في الجدول
            tableBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center">
                        <div class="d-flex flex-column align-items-center py-5">
                            <div class="text-danger mb-3">
                                <i class="icon-base ti tabler-alert-circle" style="font-size: 3rem;"></i>
                            </div>
                            <h6 class="text-danger mb-2">خطأ في البحث</h6>
                            <p class="text-muted mb-3 text-center" style="max-width: 400px;">
                                ${errorMessage}
                            </p>
                            <div class="d-flex gap-2">
                                ${actionButton}
                                <button class="btn btn-sm btn-outline-primary" onclick="reloadWarehousesTable()">
                                    <i class="icon-base ti tabler-refresh me-1"></i>
                                    إعادة المحاولة
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('wh-search').value=''; reloadWarehousesTable();">
                                    <i class="icon-base ti tabler-x me-1"></i>
                                    مسح البحث
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
            `;

            // عرض تنبيه إضافي للأخطاء الخطيرة
            if (error.message.includes('صفحة البحث غير موجودة') ||
                error.message.includes('ليس لديك صلاحية')) {
                Swal.fire({
                    title: window.warehouseTranslations.error,
                    text: error.message,
                    icon: 'error',
                    confirmButtonText: 'موافق'
                });
            }
        });
}

function attachEventListeners() {
    // Edit buttons
    document.querySelectorAll('.edit-warehouse').forEach(button => {
        button.onclick = null;
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const warehouseId = this.getAttribute('data-id');
            if (warehouseId) {
                loadWarehouseForEdit(warehouseId);
            }
        });
    });

    // Delete buttons
    document.querySelectorAll('.delete-warehouse').forEach(button => {
        button.onclick = null;
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const warehouseId = this.getAttribute('data-id');
            if (warehouseId) {
                deleteWarehouse(warehouseId);
            }
        });
    });

    // Status toggle switches
    document.querySelectorAll('.toggle-status').forEach(toggle => {
        toggle.onchange = null;
        toggle.addEventListener('change', function (e) {
            const warehouseId = this.getAttribute('data-id');
            if (warehouseId) {
                toggleWarehouseStatus(warehouseId, this);
            }
        });
    });
}

function loadWarehouseForEdit(warehouseId) {
    fetch(`/warehouses/${warehouseId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.warehouse) {
                const editModal = document.getElementById('editModal');
                const editWarehouseId = document.getElementById('edit-warehouse-id');
                const editWarehouseName = document.getElementById('edit-warehouse-name');
                const editNotes = document.getElementById('edit-notes');
                const editStatus = document.getElementById('edit-status');

                if (editWarehouseId) editWarehouseId.value = data.warehouse.id;
                if (editWarehouseName) editWarehouseName.value = data.warehouse.name;
                if (editNotes) editNotes.value = data.warehouse.notes || '';
                // تحديث معالجة الحالة للتعامل مع boolean
                if (editStatus) {
                    // تحويل القيمة إلى string للمقارنة مع options
                    const statusValue = data.warehouse.status ? '1' : '0';
                    console.log('Setting status to:', statusValue); // للتشخيص
                    editStatus.value = statusValue;

                    // التأكد من أن القيمة تم تعيينها بشكل صحيح
                    if (editStatus.value !== statusValue) {
                        console.warn('Status value not set correctly. Available options:');
                        Array.from(editStatus.options).forEach(option => {
                            console.log('Option value:', option.value, 'text:', option.text);
                        });

                        // محاولة تعيين القيمة بطريقة أخرى
                        Array.from(editStatus.options).forEach(option => {
                            if (option.value === statusValue) {
                                option.selected = true;
                            } else {
                                option.selected = false;
                            }
                        });
                    }
                }

                if (editModal) {
                    const modalInstance = new bootstrap.Modal(editModal);
                    modalInstance.show();
                }
            } else {
                Swal.fire({
                    title: window.warehouseTranslations.error,
                    text: data.message || 'حدث خطأ في تحميل بيانات المخزن',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: window.warehouseTranslations.error,
                text: window.warehouseTranslations.anErrorOccurredWhileLoadingData,
                icon: 'error'
            });
        });
}

function deleteWarehouse(warehouseId) {
    Swal.fire({
        title: window.warehouseTranslations.areYouSure,
        text: window.warehouseTranslations.confirmDeleteMessage,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: window.warehouseTranslations.yesDelete,
        cancelButtonText: window.warehouseTranslations.cancel,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/warehouses/${warehouseId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: window.warehouseTranslations.success,
                            text: data.message,
                            icon: "success",
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            reloadWarehousesTable();
                        });
                    } else {
                        throw new Error(data.message || 'فشل في حذف المخزن');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: window.warehouseTranslations.error,
                        text: 'حدث خطأ أثناء حذف المخزن: ' + error.message,
                        icon: "error"
                    });
                });
        }
    });
}

function toggleWarehouseStatus(warehouseId, switchElement) {
    const originalState = switchElement.checked;
    switchElement.disabled = true;

    fetch(`/warehouses/${warehouseId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: window.warehouseTranslations.success,
                    text: data.message,
                    icon: "success",
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    reloadWarehousesTable();
                });
            } else {
                throw new Error(data.message || 'فشل في تغيير الحالة');
            }
        })
        .catch(error => {
            console.error('Toggle status error:', error);

            // إعادة تعيين الـ switch لحالته السابقة
            switchElement.checked = originalState;
            switchElement.disabled = false;

            Swal.fire({
                title: window.warehouseTranslations.error,
                text: 'حدث خطأ أثناء تغيير حالة المخزن: ' + error.message,
                icon: "error"
            });
        });
}

function clearFormErrors(form) {
    if (!form) return;
    form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
}

function showFormErrors(form, errors) {
    if (!form || !errors) return;
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

document.addEventListener('DOMContentLoaded', function () {
    const createForm = document.getElementById('createWarehouseForm');
    const editForm = document.getElementById('editWarehouseForm');

    // إرفاق Event Listeners الأولية
    attachEventListeners();

    // Create Warehouse Form
    if (createForm) {
        createForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearFormErrors(this);

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn ?.innerHTML;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${window.warehouseTranslations.adding}`;
            }

            fetch('/warehouses', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // إغلاق المودال بشكل صحيح
                        closeModal('createModal');

                        // إعادة تعيين النموذج
                        createForm.reset();

                        // عرض رسالة النجاح
                        Swal.fire({
                            title: window.warehouseTranslations.success,
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // إعادة تحميل الجدول
                            reloadWarehousesTable();
                        });
                    } else {
                        if (data.errors) {
                            showFormErrors(createForm, data.errors);
                        } else {
                            Swal.fire({
                                title: window.warehouseTranslations.error,
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: window.warehouseTranslations.error,
                        text: window.warehouseTranslations.anErrorOccurredWhileAddingWarehouse,
                        icon: 'error'
                    });
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
        });
    }

    // Update Warehouse Form
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            clearFormErrors(this);

            const warehouseId = document.getElementById('edit-warehouse-id') ?.value;
            if (!warehouseId) {
                console.error('Warehouse ID not found');
                return;
            }

            const formData = new FormData(this);
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn ?.innerHTML;

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>${window.warehouseTranslations.updating}`;
            }

            fetch(`/warehouses/${warehouseId}`, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // إغلاق المودال بشكل صحيح
                        closeModal('editModal');

                        // عرض رسالة النجاح
                        Swal.fire({
                            title: window.warehouseTranslations.success,
                            text: data.message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // إعادة تحميل الجدول
                            reloadWarehousesTable();
                        });
                    } else {
                        if (data.errors) {
                            showFormErrors(editForm, data.errors);
                        } else {
                            Swal.fire({
                                title: window.warehouseTranslations.error,
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        title: window.warehouseTranslations.error,
                        text: window.warehouseTranslations.anErrorOccurredWhileUpdatingWarehouse,
                        icon: 'error'
                    });
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
        });
    }

    // Clear form when modal is hidden - تصحيح ID المودال
    const createModalEl = document.getElementById('createModal');
    if (createModalEl) {
        createModalEl.addEventListener('hidden.bs.modal', function () {
            if (createForm) {
                createForm.reset();
                clearFormErrors(createForm);
            }
            // تنظيف إضافي للـ backdrop
            cleanupModalBackdrop();
        });
    }

    const editModalEl = document.getElementById('editModal');
    if (editModalEl) {
        editModalEl.addEventListener('hidden.bs.modal', function () {
            if (editForm) {
                editForm.reset();
                clearFormErrors(editForm);
            }
            // تنظيف إضافي للـ backdrop
            cleanupModalBackdrop();
        });
    }

    // Live search مع debouncing
    const searchInput = document.getElementById('wh-search');
    if (searchInput) {
        let searchTimeout;

        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                console.log('Live search triggered with value:', this.value);
                reloadWarehousesTable();
            }, 300);
        });

        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                console.log('Enter key pressed for search');
                clearTimeout(searchTimeout);
                reloadWarehousesTable();
            }
        });
    } else {
        console.error('Search input #wh-search not found');
    }

    // تغيير عدد العناصر المعروضة
    const perPageSelect = document.getElementById('wh-perPage');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function () {
            console.log('Per page changed to:', this.value);
            reloadWarehousesTable();
        });
    } else {
        console.error('Per page select #wh-perPage not found');
    }


});
