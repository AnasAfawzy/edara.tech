<!-- filepath: d:\xampp\htdocs\edara.tech_old\resources\views\opening-stocks\index.blade.php -->
@extends('layouts.app')

@section('title', __('Opening Stocks'))

@section('content')
    {!! breadcrumb([['title' => __('Inventory')], ['title' => __('Opening Stocks')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث والفلاتر -->
                    <div class="card-header">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <div class="d-flex align-items-center">
                                    <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                                    <select id="perPage" class="form-select form-select-sm" style="width: auto;">
                                        <option value="5">5</option>
                                        <option value="10" selected>10</option>
                                        <option value="25">25</option>
                                        <option value="50">50</option>
                                    </select>
                                    <span class="ms-2">{{ __('entries') }}</span>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center justify-content-end gap-2">
                                    <!-- البحث -->
                                    <div class="input-group" style="width: 250px;">
                                        <input type="text" id="search" class="form-control form-control-sm"
                                            placeholder="{{ __('Search opening stocks...') }}">
                                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                            onclick="clearSearch()">
                                            <i class="icon-base ti tabler-x"></i>
                                        </button>
                                    </div>

                                    <!-- أزرار العمليات -->
                                    <button type="button" class="btn btn-success btn-sm" onclick="showBulkForm()">
                                        <i class="icon-base ti tabler-plus me-1"></i>
                                        {{ __('Add Opening Stocks') }}
                                    </button>

                                    <button type="button" class="btn btn-info btn-sm" onclick="exportData()">
                                        <i class="icon-base ti tabler-download me-1"></i>
                                        {{ __('Export') }}
                                    </button>

                                    <button type="button" class="btn btn-warning btn-sm" onclick="showImportForm()">
                                        <i class="icon-base ti tabler-upload me-1"></i>
                                        {{ __('Import') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- نموذج الإدخال الجماعي -->
                    <div id="bulk-form-container" class="card-body border-bottom" style="display: none;">
                        <h6 class="mb-3">{{ __('Add Opening Stocks') }}</h6>

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Opening Date') }} <span
                                        class="text-danger">*</span></label>
                                <input type="date" id="bulk-opening-date" class="form-control" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">{{ __('Actions') }}</label>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        onclick="selectAllProducts()">
                                        <i class="icon-base ti tabler-check-list"></i> {{ __('Select All') }}
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                        onclick="hideBulkForm()">
                                        <i class="icon-base ti tabler-x"></i> {{ __('Cancel') }}
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">{{ __('Summary') }}</label>
                                <div class="card bg-light">
                                    <div class="card-body p-2">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted">{{ __('Selected') }}</small>
                                                <div class="fw-bold" id="bulk-selected-count">0</div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">{{ __('Total Quantity') }}</small>
                                                <div class="fw-bold" id="bulk-total-quantity">0</div>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">{{ __('Total Cost') }}</small>
                                                <div class="fw-bold" id="bulk-total-cost">0.00</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th width="50">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="select-all-checkbox">
                                            </div>
                                        </th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Brand') }}</th>
                                        <th>{{ __('Unit') }}</th>
                                        <th width="120">{{ __('Quantity') }}</th>
                                        <th width="120">{{ __('Unit Cost') }}</th>
                                        <th width="100">{{ __('Total') }}</th>
                                        <th width="150">{{ __('Notes') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="bulk-products-table">
                                    <!-- Products will be loaded here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" onclick="saveBulkOpeningStocks()">
                                <i class="icon-base ti tabler-device-floppy me-1"></i>
                                {{ __('Save Opening Stocks') }}
                            </button>
                        </div>
                    </div>

                    <!-- نموذج الاستيراد -->
                    <div id="import-form-container" class="card-body border-bottom" style="display: none;">
                        <h6 class="mb-3">{{ __('Import Opening Stocks from Excel') }}</h6>

                        <form id="importForm" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Excel File') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="file" name="excel_file" class="form-control"
                                        accept=".xlsx,.xls,.csv" required>
                                    <div class="form-text">{{ __('Accepted formats: Excel (.xlsx, .xls) or CSV') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Default Opening Date') }}</label>
                                    <input type="date" name="default_opening_date" class="form-control">
                                    <div class="form-text">{{ __('Used if date not in file') }}</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">{{ __('Actions') }}</label>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="icon-base ti tabler-upload me-1"></i>
                                            {{ __('Import') }}
                                        </button>
                                        <a href="{{ route('opening-stocks.download-template') }}"
                                            class="btn btn-outline-info btn-sm">
                                            <i class="icon-base ti tabler-download me-1"></i>
                                            {{ __('Download Template') }}
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="hideImportForm()">
                                            <i class="icon-base ti tabler-x me-1"></i>
                                            {{ __('Cancel') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">{{ __('File Format Requirements') }}</h6>
                                    <ul class="mb-0">
                                        <li>{{ __('Product Code') }} ({{ __('Required') }})</li>
                                        <li>{{ __('Quantity') }} ({{ __('Required') }})</li>
                                        <li>{{ __('Unit Cost') }} ({{ __('Required') }})</li>
                                        <li>{{ __('Opening Date') }} ({{ __('Optional, format: YYYY-MM-DD') }})</li>
                                        <li>{{ __('Notes') }} ({{ __('Optional') }})</li>
                                    </ul>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table align-middle text-center" id="openingStocksTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Brand') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Quantity') }}</th>
                                    <th>{{ __('Unit Cost') }}</th>
                                    <th>{{ __('Total Cost') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('opening-stocks.partials.table', ['openingStocks' => $openingStocks])
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container">
                        @if ($openingStocks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div>
                                {{ $openingStocks->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let currentPage = 1;
        let selectedProducts = [];

        function clearSearch() {
            document.getElementById('search').value = '';
            reloadOpeningStocksTable(1);
        }

        function showBulkForm() {
            document.getElementById('bulk-form-container').style.display = 'block';
            document.getElementById('import-form-container').style.display = 'none';
            loadProductsForBulk();
            document.getElementById('bulk-opening-date').value = new Date().toISOString().split('T')[0];
        }

        function hideBulkForm() {
            document.getElementById('bulk-form-container').style.display = 'none';
            resetBulkForm();
        }

        function showImportForm() {
            document.getElementById('import-form-container').style.display = 'block';
            document.getElementById('bulk-form-container').style.display = 'none';
            document.querySelector('input[name="default_opening_date"]').value = new Date().toISOString().split('T')[0];
        }

        function hideImportForm() {
            document.getElementById('import-form-container').style.display = 'none';
            document.getElementById('importForm').reset();
        }

        function exportData() {
            window.open('{{ route('opening-stocks.export') }}', '_blank');
        }

        function reloadOpeningStocksTable(page = 1) {
            currentPage = page;
            let search = document.getElementById('search')?.value?.trim() ?? '';
            let perPage = document.getElementById('perPage')?.value ?? 10;

            let url = `{{ route('opening-stocks.search') }}`;
            const params = new URLSearchParams();
            if (search) params.append('search', search);
            params.append('perPage', perPage);
            params.append('page', page);

            url += '?' + params.toString();

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('#openingStocksTable tbody').outerHTML = data.html;
                        document.getElementById('pagination-container').innerHTML = data.pagination || '';
                        attachPaginationListeners();
                        attachEventListeners();
                    } else {
                        Swal.fire({
                            title: '{{ __('Error') }}',
                            text: data.message || 'حدث خطأ في تحميل البيانات',
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: '{{ __('Error') }}',
                        text: 'خطأ في الاتصال بالسيرفر',
                        icon: 'error'
                    });
                });
        }

        function attachPaginationListeners() {
            document.querySelectorAll('#pagination-container .pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    reloadOpeningStocksTable(page);
                });
            });
        }

        function attachEventListeners() {
            document.querySelectorAll('.edit-opening-stock').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    if (id) loadOpeningStockForEdit(id);
                });
            });

            document.querySelectorAll('.delete-opening-stock').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    if (id) deleteOpeningStock(id);
                });
            });
        }

        function loadOpeningStockForEdit(id) {
            fetch(`{{ url('opening-stocks') }}/${id}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const row = document.querySelector(`#opening-stock-row-${id}`);
                        if (row) {
                            showEditRowForm(row, data.openingStock);
                        }
                    }
                });
        }

        function showEditRowForm(row, openingStock) {
            const originalHtml = row.innerHTML;

            row.innerHTML = `
            <td colspan="10">
                <form class="edit-form p-3 bg-light rounded" data-id="${openingStock.id}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Quantity') }}</label>
                            <input type="number" name="quantity" class="form-control"
                                   value="${openingStock.quantity}" step="0.001" min="0.001" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Unit Cost') }}</label>
                            <input type="number" name="unit_cost" class="form-control"
                                   value="${openingStock.unit_cost}" step="0.01" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Opening Date') }}</label>
                            <input type="date" name="opening_date" class="form-control"
                                   value="${openingStock.opening_date}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <input type="text" name="notes" class="form-control"
                                   value="${openingStock.notes || ''}" placeholder="{{ __('Notes') }}">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="icon-base ti tabler-device-floppy me-1"></i>
                                {{ __('Update') }}
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm ms-2" onclick="cancelEdit(this, '${originalHtml}')">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </div>
                </form>
            </td>
        `;

            // Handle form submission
            const form = row.querySelector('.edit-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const id = this.getAttribute('data-id');

                fetch(`{{ url('opening-stocks') }}/${id}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify(Object.fromEntries(formData))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: '{{ __('Success') }}',
                                text: data.message,
                                icon: 'success',
                                timer: 1500,
                                showConfirmButton: false
                            });
                            reloadOpeningStocksTable(currentPage);
                        } else {
                            Swal.fire({
                                title: '{{ __('Error') }}',
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    });
            });
        }

        function cancelEdit(button, originalHtml) {
            const row = button.closest('tr');
            row.innerHTML = originalHtml;
            attachEventListeners();
        }

        function deleteOpeningStock(id) {
            Swal.fire({
                title: "{{ __('Are You Sure') }}",
                text: "{{ __('This opening stock will be permanently deleted') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, Delete') }}",
                cancelButtonText: "{{ __('Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('opening-stocks') }}/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: "{{ __('Success') }}",
                                    text: data.message,
                                    icon: "success",
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                reloadOpeningStocksTable(currentPage);
                            } else {
                                Swal.fire({
                                    title: "{{ __('Error') }}",
                                    text: data.message,
                                    icon: "error"
                                });
                            }
                        });
                }
            });
        }

        function loadProductsForBulk() {
            fetch('{{ route('opening-stocks.bulk-products') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('bulk-products-table');
                        tbody.innerHTML = '';

                        data.products.forEach(product => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                        <td>
                            <div class="form-check">
                                <input class="form-check-input product-checkbox" type="checkbox"
                                       value="${product.id}" onchange="toggleProductRow(this)">
                            </div>
                        </td>
                        <td>
                            <div>
                                <strong>${product.name}</strong>
                                <br><small class="text-muted">${product.code}</small>
                            </div>
                        </td>
                        <td>${product.category?.name || 'N/A'}</td>
                        <td>${product.brand?.name || 'N/A'}</td>
                        <td>${product.unit?.name || 'N/A'}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm quantity-input"
                                   step="0.001" min="0.001" disabled
                                   data-product-id="${product.id}">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm cost-input"
                                   step="0.01" min="0" disabled
                                   data-product-id="${product.id}">
                        </td>
                        <td>
                            <span class="total-cost fw-bold text-success" data-product-id="${product.id}">0.00</span>
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm notes-input"
                                   disabled placeholder="{{ __('Notes') }}"
                                   data-product-id="${product.id}">
                        </td>
                    `;
                            tbody.appendChild(row);
                        });

                        attachBulkEventListeners();
                    }
                });
        }

        function toggleProductRow(checkbox) {
            const row = checkbox.closest('tr');
            const inputs = row.querySelectorAll('input:not(.product-checkbox)');
            const productId = checkbox.value;

            inputs.forEach(input => {
                input.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    input.value = '';
                }
            });

            if (checkbox.checked) {
                selectedProducts.push(productId);
            } else {
                selectedProducts = selectedProducts.filter(id => id !== productId);
            }

            updateBulkTotals();
        }

        function attachBulkEventListeners() {
            document.querySelectorAll('.quantity-input, .cost-input').forEach(input => {
                input.addEventListener('input', function() {
                    const productId = this.dataset.productId;
                    updateProductTotal(productId);
                    updateBulkTotals();
                });
            });
        }

        function updateProductTotal(productId) {
            const quantityInput = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
            const costInput = document.querySelector(`.cost-input[data-product-id="${productId}"]`);
            const totalSpan = document.querySelector(`.total-cost[data-product-id="${productId}"]`);

            const quantity = parseFloat(quantityInput.value) || 0;
            const cost = parseFloat(costInput.value) || 0;
            const total = quantity * cost;

            totalSpan.textContent = total.toLocaleString('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function updateBulkTotals() {
            let totalQuantity = 0;
            let totalCost = 0;
            let selectedCount = 0;

            selectedProducts.forEach(productId => {
                const quantityInput = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                const costInput = document.querySelector(`.cost-input[data-product-id="${productId}"]`);

                if (quantityInput && costInput) {
                    const quantity = parseFloat(quantityInput.value) || 0;
                    const cost = parseFloat(costInput.value) || 0;

                    if (quantity > 0 && cost >= 0) {
                        selectedCount++;
                        totalQuantity += quantity;
                        totalCost += (quantity * cost);
                    }
                }
            });

            document.getElementById('bulk-selected-count').textContent = selectedCount;
            document.getElementById('bulk-total-quantity').textContent = totalQuantity.toLocaleString();
            document.getElementById('bulk-total-cost').textContent = totalCost.toLocaleString('ar-EG', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function selectAllProducts() {
            const checkboxes = document.querySelectorAll('.product-checkbox');
            const selectAllBtn = document.querySelector('button[onclick="selectAllProducts()"]');
            const isSelectAll = selectAllBtn.textContent.includes('{{ __('Select All') }}');

            checkboxes.forEach(checkbox => {
                checkbox.checked = isSelectAll;
                toggleProductRow(checkbox);
            });

            selectAllBtn.innerHTML = isSelectAll ?
                '<i class="icon-base ti tabler-square-minus"></i> {{ __('Deselect All') }}' :
                '<i class="icon-base ti tabler-check-list"></i> {{ __('Select All') }}';
        }

        function saveBulkOpeningStocks() {
            const openingDate = document.getElementById('bulk-opening-date').value;
            if (!openingDate) {
                Swal.fire({
                    title: '{{ __('Error') }}',
                    text: '{{ __('Please select opening date') }}',
                    icon: 'error'
                });
                return;
            }

            const products = [];
            selectedProducts.forEach(productId => {
                const quantityInput = document.querySelector(`.quantity-input[data-product-id="${productId}"]`);
                const costInput = document.querySelector(`.cost-input[data-product-id="${productId}"]`);
                const notesInput = document.querySelector(`.notes-input[data-product-id="${productId}"]`);

                const quantity = parseFloat(quantityInput.value);
                const cost = parseFloat(costInput.value);

                if (quantity > 0 && cost >= 0) {
                    products.push({
                        product_id: productId,
                        quantity: quantity,
                        unit_cost: cost,
                        notes: notesInput.value || null
                    });
                }
            });

            if (products.length === 0) {
                Swal.fire({
                    title: '{{ __('Error') }}',
                    text: '{{ __('Please select at least one product with valid quantity and cost') }}',
                    icon: 'error'
                });
                return;
            }

            fetch('{{ route('opening-stocks.bulk-store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        opening_date: openingDate,
                        products: products
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        hideBulkForm();

                        let message = `{{ __('Created successfully') }}: ${data.created_count} {{ __('records') }}`;
                        if (data.errors && data.errors.length > 0) {
                            message += `\n{{ __('Errors') }}: ${data.errors.length}`;
                        }

                        Swal.fire({
                            title: '{{ __('Success') }}',
                            text: message,
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        reloadOpeningStocksTable(currentPage);
                    } else {
                        Swal.fire({
                            title: '{{ __('Error') }}',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                });
        }

        function resetBulkForm() {
            document.getElementById('bulk-opening-date').value = '';
            document.querySelectorAll('.product-checkbox').forEach(checkbox => {
                checkbox.checked = false;
                toggleProductRow(checkbox);
            });
            selectedProducts = [];
            updateBulkTotals();
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Import form submission
            const importForm = document.getElementById('importForm');
            if (importForm) {
                importForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Importing...') }}';

                    fetch('{{ route('opening-stocks.import') }}', {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                hideImportForm();

                                let message =
                                    `{{ __('Imported successfully') }}: ${data.imported_count} {{ __('records') }}`;
                                if (data.errors && data.errors.length > 0) {
                                    message += `\n{{ __('Errors') }}: ${data.errors.length}`;
                                }

                                Swal.fire({
                                    title: '{{ __('Success') }}',
                                    text: message,
                                    icon: 'success',
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                reloadOpeningStocksTable(currentPage);
                            } else {
                                Swal.fire({
                                    title: '{{ __('Error') }}',
                                    text: data.message,
                                    icon: 'error'
                                });
                            }
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            // Filters
            document.getElementById('perPage')?.addEventListener('change', function() {
                reloadOpeningStocksTable(1);
            });

            let searchTimeout;
            document.getElementById('search')?.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    reloadOpeningStocksTable(1);
                }, 300);
            });

            attachEventListeners();
            attachPaginationListeners();
        });
    </script>
@endsection
