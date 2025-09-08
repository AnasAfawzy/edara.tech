<div class="modal fade" id="bulkCreateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Bulk Add Opening Stocks') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <!-- Opening Date -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label required">{{ __('Opening Date') }}</label>
                        <input type="date" id="bulk-opening-date" class="form-control" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Actions') }}</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="select-all-btn"
                                onclick="selectAllProducts()">
                                <i class="ti ti-square-check me-1"></i>{{ __('Select All') }}
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">{{ __('Summary') }}</label>
                        <div class="card bg-light">
                            <div class="card-body p-2">
                                <small>
                                    {{ __('Selected') }}: <span id="bulk-selected-count" class="fw-bold">0</span><br>
                                    {{ __('Total Quantity') }}: <span id="bulk-total-quantity"
                                        class="fw-bold">0</span><br>
                                    {{ __('Total Cost') }}: <span id="bulk-total-cost" class="fw-bold">0.00</span>
                                    {{ __('EGP') }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="table-responsive" style="max-height: 500px;">
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
                            <!-- Products will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    {{ __('Cancel') }}
                </button>
                <button type="button" class="btn btn-primary" id="bulk-save-btn" onclick="saveBulkOpeningStocks()">
                    <i class="ti ti-device-floppy me-2"></i>{{ __('Save Opening Stocks') }}
                </button>
            </div>
        </div>
    </div>
</div>
