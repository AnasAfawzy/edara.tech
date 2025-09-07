@extends('layouts.app')

@section('title', __('Products'))

@section('content')
    {!! breadcrumb([['title' => __('Inventory')], ['title' => __('Products')]]) !!}

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
                                    <!-- الفلاتر -->
                                    <div class="d-flex align-items-center gap-2">
                                        <select id="categoryFilter" class="form-select form-select-sm" style="width: auto;">
                                            <option value="">{{ __('All Categories') }}</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>

                                        <select id="brandFilter" class="form-select form-select-sm" style="width: auto;">
                                            <option value="">{{ __('All Brands') }}</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </select>

                                        <select id="unitFilter" class="form-select form-select-sm" style="width: auto;">
                                            <option value="">{{ __('All Units') }}</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                            @endforeach
                                        </select>

                                        <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                                            <option value="">{{ __('All Status') }}</option>
                                            <option value="1">{{ __('Active') }}</option>
                                            <option value="0">{{ __('Inactive') }}</option>
                                        </select>
                                    </div>

                                    <!-- البحث -->
                                    <div class="input-group" style="width: 250px;">
                                        <input type="text" id="search" class="form-control form-control-sm"
                                            placeholder="{{ __('Search products...') }}">
                                        <button class="btn btn-outline-secondary btn-sm" type="button"
                                            onclick="clearSearch()">
                                            <i class="icon-base ti tabler-x"></i>
                                        </button>
                                    </div>

                                    <!-- زر إضافة منتج جديد -->
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#createModal">
                                        <i class="icon-base ti tabler-plus me-1"></i>
                                        {{ __('Add Product') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="productsTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Brand') }}</th>
                                    <th>{{ __('Unit') }}</th>
                                    <th>{{ __('Purchase Price') }}</th>
                                    <th>{{ __('Sale Price') }}</th>
                                    <th>{{ __('Stock') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            @include('products.partials.table', ['products' => $products])
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div id="pagination-container">
                        @if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator && $products->hasPages())
                            <div>
                                {{ $products->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Add New Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="createProductForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- الصف الأول -->
                            <div class="col-md-6">
                                <label class="form-label" for="create-name">{{ __('Product Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="create-name" name="name" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="create-name-en">{{ __('English Name') }}</label>
                                <input type="text" id="create-name-en" name="name_en" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الثاني -->
                            <div class="col-md-6">
                                <label class="form-label" for="create-code">{{ __('Product Code') }} <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" id="create-code" name="code" class="form-control" required>
                                    <button type="button" class="btn btn-outline-secondary" onclick="generateCode()">
                                        <i class="icon-base ti tabler-refresh"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="create-barcode">{{ __('Barcode') }}</label>
                                <input type="text" id="create-barcode" name="barcode" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الثالث -->
                            <div class="col-md-4">
                                <label class="form-label" for="create-category">{{ __('Category') }} <span
                                        class="text-danger">*</span></label>
                                <select id="create-category" name="category_id" class="form-select" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="create-brand">{{ __('Brand') }}</label>
                                <select id="create-brand" name="brand_id" class="form-select">
                                    <option value="">{{ __('Select Brand') }}</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="create-unit">{{ __('Unit') }} <span
                                        class="text-danger">*</span></label>
                                <select id="create-unit" name="unit_id" class="form-select" required>
                                    <option value="">{{ __('Select Unit') }}</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الرابع - الأسعار -->
                            <div class="col-md-6">
                                <label class="form-label" for="create-purchase-price">{{ __('Purchase Price') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="create-purchase-price" name="purchase_price"
                                    class="form-control" step="0.01" min="0" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="create-sale-price">{{ __('Sale Price') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="create-sale-price" name="sale_price" class="form-control"
                                    step="0.01" min="0" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الخامس - المخزون -->
                            <div class="col-md-3">
                                <label class="form-label" for="create-current-stock">{{ __('Current Stock') }}</label>
                                <input type="number" id="create-current-stock" name="current_stock"
                                    class="form-control" step="0.001" min="0" value="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="create-min-stock">{{ __('Min Stock') }}</label>
                                <input type="number" id="create-min-stock" name="min_stock" class="form-control"
                                    step="0.001" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="create-max-stock">{{ __('Max Stock') }}</label>
                                <input type="number" id="create-max-stock" name="max_stock" class="form-control"
                                    step="0.001" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="create-reorder-level">{{ __('Reorder Level') }}</label>
                                <input type="number" id="create-reorder-level" name="reorder_level"
                                    class="form-control" step="0.001" min="0">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف السادس -->
                            <div class="col-md-6">
                                <label class="form-label" for="create-image">{{ __('Product Image') }}</label>
                                <input type="file" id="create-image" name="image" class="form-control"
                                    accept="image/*">
                                <div class="invalid-feedback"></div>
                                <div class="form-text">{{ __('Allowed formats: JPG, PNG, GIF. Max size: 2MB') }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 mt-4">
                                    <div class="form-check">
                                        <input type="checkbox" id="create-has-expiry" name="has_expiry"
                                            class="form-check-input" value="1">
                                        <label class="form-check-label"
                                            for="create-has-expiry">{{ __('Has Expiry') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="create-is-active" name="is_active"
                                            class="form-check-input" value="1" checked>
                                        <label class="form-check-label"
                                            for="create-is-active">{{ __('Active') }}</label>
                                    </div>
                                </div>
                            </div>

                            <!-- الوصف والملاحظات -->
                            <div class="col-12">
                                <label class="form-label" for="create-description">{{ __('Description') }}</label>
                                <textarea id="create-description" name="description" class="form-control" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="create-notes">{{ __('Notes') }}</label>
                                <textarea id="create-notes" name="notes" class="form-control" rows="2"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-plus me-1"></i>
                            {{ __('Add Product') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Edit Product') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editProductForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="edit-product-id" name="product_id">
                    <div class="modal-body">
                        <div class="row g-3">
                            <!-- الصف الأول -->
                            <div class="col-md-6">
                                <label class="form-label" for="edit-name">{{ __('Product Name') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="edit-name" name="name" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="edit-name-en">{{ __('English Name') }}</label>
                                <input type="text" id="edit-name-en" name="name_en" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الثاني -->
                            <div class="col-md-6">
                                <label class="form-label" for="edit-code">{{ __('Product Code') }} <span
                                        class="text-danger">*</span></label>
                                <input type="text" id="edit-code" name="code" class="form-control" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="edit-barcode">{{ __('Barcode') }}</label>
                                <input type="text" id="edit-barcode" name="barcode" class="form-control">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الثالث -->
                            <div class="col-md-4">
                                <label class="form-label" for="edit-category">{{ __('Category') }} <span
                                        class="text-danger">*</span></label>
                                <select id="edit-category" name="category_id" class="form-select" required>
                                    <option value="">{{ __('Select Category') }}</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="edit-brand">{{ __('Brand') }}</label>
                                <select id="edit-brand" name="brand_id" class="form-select">
                                    <option value="">{{ __('Select Brand') }}</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="edit-unit">{{ __('Unit') }} <span
                                        class="text-danger">*</span></label>
                                <select id="edit-unit" name="unit_id" class="form-select" required>
                                    <option value="">{{ __('Select Unit') }}</option>
                                    @foreach ($units as $unit)
                                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الرابع - الأسعار -->
                            <div class="col-md-6">
                                <label class="form-label" for="edit-purchase-price">{{ __('Purchase Price') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="edit-purchase-price" name="purchase_price"
                                    class="form-control" step="0.01" min="0" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="edit-sale-price">{{ __('Sale Price') }} <span
                                        class="text-danger">*</span></label>
                                <input type="number" id="edit-sale-price" name="sale_price" class="form-control"
                                    step="0.01" min="0" required>
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف الخامس - المخزون -->
                            <div class="col-md-3">
                                <label class="form-label" for="edit-current-stock">{{ __('Current Stock') }}</label>
                                <input type="number" id="edit-current-stock" name="current_stock" class="form-control"
                                    step="0.001" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="edit-min-stock">{{ __('Min Stock') }}</label>
                                <input type="number" id="edit-min-stock" name="min_stock" class="form-control"
                                    step="0.001" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="edit-max-stock">{{ __('Max Stock') }}</label>
                                <input type="number" id="edit-max-stock" name="max_stock" class="form-control"
                                    step="0.001" min="0">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="edit-reorder-level">{{ __('Reorder Level') }}</label>
                                <input type="number" id="edit-reorder-level" name="reorder_level" class="form-control"
                                    step="0.001" min="0">
                                <div class="invalid-feedback"></div>
                            </div>

                            <!-- الصف السادس -->
                            <div class="col-md-6">
                                <label class="form-label" for="edit-image">{{ __('Product Image') }}</label>
                                <input type="file" id="edit-image" name="image" class="form-control"
                                    accept="image/*">
                                <div class="invalid-feedback"></div>
                                <div class="form-text">{{ __('Leave empty to keep current image') }}</div>
                                <div id="current-image-preview" class="mt-2"></div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-3 mt-4">
                                    <div class="form-check">
                                        <input type="checkbox" id="edit-has-expiry" name="has_expiry"
                                            class="form-check-input" value="1">
                                        <label class="form-check-label"
                                            for="edit-has-expiry">{{ __('Has Expiry') }}</label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" id="edit-is-active" name="is_active"
                                            class="form-check-input" value="1">
                                        <label class="form-check-label" for="edit-is-active">{{ __('Active') }}</label>
                                    </div>
                                </div>
                            </div>

                            <!-- الوصف والملاحظات -->
                            <div class="col-12">
                                <label class="form-label" for="edit-description">{{ __('Description') }}</label>
                                <textarea id="edit-description" name="description" class="form-control" rows="3"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="edit-notes">{{ __('Notes') }}</label>
                                <textarea id="edit-notes" name="notes" class="form-control" rows="2"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="icon-base ti tabler-device-floppy me-1"></i>
                            {{ __('Update Product') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Product Details') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="product-details">
                    <!-- سيتم ملء التفاصيل هنا -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        let createModal, editModal, viewModal;
        let currentPage = 1;

        function clearSearch() {
            document.getElementById('search').value = '';
            document.getElementById('categoryFilter').value = '';
            document.getElementById('brandFilter').value = '';
            document.getElementById('unitFilter').value = '';
            document.getElementById('statusFilter').value = '';
            reloadProductsTable();
        }

        function generateCode() {
            fetch('{{ route('products.generate-code') }}', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('create-code').value = data.code;
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        function reloadProductsTable(page = 1) {
            let search = document.getElementById('search').value.trim();
            let perPage = document.getElementById('perPage').value;
            let categoryId = document.getElementById('categoryFilter').value;
            let brandId = document.getElementById('brandFilter').value;
            let unitId = document.getElementById('unitFilter').value;
            let status = document.getElementById('statusFilter').value;

            let url = `{{ route('products.search') }}`;
            const params = new URLSearchParams();

            if (search && search !== '') {
                params.append('search', search);
            }
            if (categoryId) {
                params.append('category_id', categoryId);
            }
            if (brandId) {
                params.append('brand_id', brandId);
            }
            if (unitId) {
                params.append('unit_id', unitId);
            }
            if (status !== '') {
                params.append('is_active', status);
            }

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
                        document.querySelector('#productsTable tbody').outerHTML = data.html;

                        if (data.pagination) {
                            document.getElementById('pagination-container').innerHTML = data.pagination;
                            attachPaginationListeners();
                        }

                        attachEventListeners();
                        window.history.replaceState({}, '', '{{ route('products.index') }}');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function attachPaginationListeners() {
            document.querySelectorAll('#pagination-container .pagination a').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = new URL(this.href);
                    const page = url.searchParams.get('page') || 1;
                    reloadProductsTable(page);
                });
            });
        }

        function attachEventListeners() {
            // View buttons
            document.querySelectorAll('.view-product').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-id');
                    if (productId) {
                        viewProduct(productId);
                    }
                });
            });

            // Edit buttons
            document.querySelectorAll('.edit-product').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-id');
                    if (productId) {
                        loadProductForEdit(productId);
                    }
                });
            });

            // Delete buttons
            document.querySelectorAll('.delete-product').forEach(button => {
                button.onclick = null;
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-id');
                    if (productId) {
                        deleteProduct(productId);
                    }
                });
            });

            // Status toggle switches
            document.querySelectorAll('.toggle-status').forEach(toggle => {
                toggle.onchange = null;
                toggle.addEventListener('change', function(e) {
                    const productId = this.getAttribute('data-id');
                    if (productId) {
                        toggleProductStatus(productId, this);
                    }
                });
            });
        }

        function viewProduct(productId) {
            fetch(`{{ url('products') }}/${productId}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;
                        const detailsHtml = `
                        <div class="row">
                            <div class="col-md-4">
                                <img src="${product.image_url}" class="img-fluid rounded" alt="${product.name}">
                            </div>
                            <div class="col-md-8">
                                <table class="table table-borderless">
                                    <tr><td><strong>${'{{ __('Product Name') }}'}</strong></td><td>${product.name}</td></tr>
                                    <tr><td><strong>${'{{ __('English Name') }}'}</strong></td><td>${product.name_en || '{{ __('Not specified') }}'}</td></tr>
                                    <tr><td><strong>${'{{ __('Code') }}'}</strong></td><td>${product.code}</td></tr>
                                    <tr><td><strong>${'{{ __('Barcode') }}'}</strong></td><td>${product.barcode || '{{ __('Not specified') }}'}</td></tr>
                                    <tr><td><strong>${'{{ __('Category') }}'}</strong></td><td>${product.category?.name || ''}</td></tr>
                                    <tr><td><strong>${'{{ __('Brand') }}'}</strong></td><td>${product.brand?.name || '{{ __('Not specified') }}'}</td></tr>
                                    <tr><td><strong>${'{{ __('Unit') }}'}</strong></td><td>${product.unit?.name || ''}</td></tr>
                                    <tr><td><strong>${'{{ __('Purchase Price') }}'}</strong></td><td>${product.purchase_price}</td></tr>
                                    <tr><td><strong>${'{{ __('Sale Price') }}'}</strong></td><td>${product.sale_price}</td></tr>
                                    <tr><td><strong>${'{{ __('Current Stock') }}'}</strong></td><td>${product.current_stock}</td></tr>
                                    <tr><td><strong>${'{{ __('Status') }}'}</strong></td><td><span class="badge ${product.status_badge_class}">${product.formatted_status}</span></td></tr>
                                </table>
                            </div>
                        </div>
                        ${product.description ? `<div class="mt-3"><strong>${'{{ __('Description') }}'}</strong><p>${product.description}</p></div>` : ''}
                        ${product.notes ? `<div class="mt-3"><strong>${'{{ __('Notes') }}'}</strong><p>${product.notes}</p></div>` : ''}
                    `;

                        document.getElementById('product-details').innerHTML = detailsHtml;
                        if (viewModal) {
                            viewModal.show();
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function loadProductForEdit(productId) {
            fetch(`{{ url('products') }}/${productId}/edit`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const product = data.product;

                        document.getElementById('edit-product-id').value = product.id;
                        document.getElementById('edit-name').value = product.name;
                        document.getElementById('edit-name-en').value = product.name_en || '';
                        document.getElementById('edit-code').value = product.code;
                        document.getElementById('edit-barcode').value = product.barcode || '';
                        document.getElementById('edit-category').value = product.category_id;
                        document.getElementById('edit-brand').value = product.brand_id || '';
                        document.getElementById('edit-unit').value = product.unit_id;
                        document.getElementById('edit-purchase-price').value = product.purchase_price;
                        document.getElementById('edit-sale-price').value = product.sale_price;
                        document.getElementById('edit-current-stock').value = product.current_stock;
                        document.getElementById('edit-min-stock').value = product.min_stock || '';
                        document.getElementById('edit-max-stock').value = product.max_stock || '';
                        document.getElementById('edit-reorder-level').value = product.reorder_level || '';
                        document.getElementById('edit-description').value = product.description || '';
                        document.getElementById('edit-notes').value = product.notes || '';
                        document.getElementById('edit-has-expiry').checked = product.has_expiry;
                        document.getElementById('edit-is-active').checked = product.is_active;

                        // عرض الصورة الحالية
                        if (product.image_url) {
                            document.getElementById('current-image-preview').innerHTML =
                                `<img src="${product.image_url}" class="img-thumbnail" style="max-width: 100px;">`;
                        }

                        if (editModal) {
                            editModal.show();
                        }
                    } else {
                        Swal.fire({
                            title: '{{ __('Error') }}',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function deleteProduct(productId) {
            Swal.fire({
                title: "{{ __('Are You Sure') }}",
                text: "{{ __('This product will be permanently deleted') }}",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "{{ __('Yes, Delete') }}",
                cancelButtonText: "{{ __('Cancel') }}"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('products') }}/${productId}`, {
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
                                }).then(() => {
                                    reloadProductsTable();
                                });
                            } else {
                                Swal.fire({
                                    title: "{{ __('Error') }}",
                                    text: data.message,
                                    icon: "error"
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            });
        }

        function toggleProductStatus(productId, switchElement) {
            const originalState = switchElement.checked;
            switchElement.disabled = true;

            fetch(`{{ url('products') }}/${productId}/toggle-status`, {
                    method: 'POST',
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
                        }).then(() => {
                            reloadProductsTable();
                        });
                    } else {
                        throw new Error(data.message || 'Failed to update status');
                    }
                })
                .catch(error => {
                    console.error('Toggle status error:', error);
                    switchElement.checked = !originalState;
                    switchElement.disabled = false;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const createForm = document.getElementById('createProductForm');
            const editForm = document.getElementById('editProductForm');

            createModal = new bootstrap.Modal(document.getElementById('createModal'));
            editModal = new bootstrap.Modal(document.getElementById('editModal'));
            viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

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

            // Create Product
            if (createForm) {
                createForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors(this);

                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Adding...') }}';

                    fetch('{{ route('products.store') }}', {
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
                                createForm.reset();
                                clearFormErrors(createForm);
                                createModal.hide();

                                Swal.fire({
                                    title: '{{ __('Success') }}',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                reloadProductsTable();
                            } else {
                                if (data.errors) {
                                    showFormErrors(createForm, data.errors);
                                } else {
                                    Swal.fire({
                                        title: '{{ __('Error') }}',
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            // Update Product
            if (editForm) {
                editForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    clearFormErrors(this);

                    const productId = document.getElementById('edit-product-id').value;
                    const formData = new FormData(this);
                    formData.append('_method', 'PUT');

                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;

                    submitBtn.disabled = true;
                    submitBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>{{ __('Updating...') }}';

                    fetch(`{{ url('products') }}/${productId}`, {
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
                                editForm.reset();
                                clearFormErrors(editForm);
                                editModal.hide();

                                Swal.fire({
                                    title: '{{ __('Success') }}',
                                    text: data.message,
                                    icon: 'success',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                reloadProductsTable();
                            } else {
                                if (data.errors) {
                                    showFormErrors(editForm, data.errors);
                                } else {
                                    Swal.fire({
                                        title: '{{ __('Error') }}',
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        })
                        .finally(() => {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        });
                });
            }

            // Clear form when modal is hidden
            document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
                if (createForm) {
                    createForm.reset();
                    clearFormErrors(createForm);
                }
            });

            document.getElementById('editModal').addEventListener('hidden.bs.modal', function() {
                if (editForm) {
                    editForm.reset();
                    clearFormErrors(editForm);
                    document.getElementById('current-image-preview').innerHTML = '';
                }
            });

            // Event listeners للبحث والفلاتر
            let searchTimeout;
            document.getElementById('search').addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    reloadProductsTable();
                }, 300);
            });

            document.getElementById('perPage').addEventListener('change', function() {
                reloadProductsTable();
            });

            document.getElementById('categoryFilter').addEventListener('change', function() {
                reloadProductsTable();
            });

            document.getElementById('brandFilter').addEventListener('change', function() {
                reloadProductsTable();
            });

            document.getElementById('unitFilter').addEventListener('change', function() {
                reloadProductsTable();
            });

            document.getElementById('statusFilter').addEventListener('change', function() {
                reloadProductsTable();
            });

            // Generate code on page load
            generateCode();

            attachEventListeners();
            attachPaginationListeners();
        });
    </script>
@endsection
