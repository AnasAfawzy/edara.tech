<!-- filepath: resources/views/opening-stocks/modals/edit.blade.php -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Edit Opening Stock') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="editOpeningStockForm">
                <div class="modal-body">
                    <input type="hidden" id="edit-id" name="id">

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label required">{{ __('Product') }}</label>
                            <select id="edit-product-id" name="product_id" class="form-select" required>
                                <option value="">{{ __('Select Product') }}</option>
                                @foreach ($productsForBulk ?? [] as $product)
                                    <option value="{{ $product->id }}">
                                        {{ $product->name }} ({{ $product->code }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ __('Quantity') }}</label>
                            <input type="number" id="edit-quantity" name="quantity" class="form-control" step="0.001"
                                min="0.001" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label required">{{ __('Unit Cost') }}</label>
                            <input type="number" id="edit-unit-cost" name="unit_cost" class="form-control"
                                step="0.01" min="0" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label required">{{ __('Opening Date') }}</label>
                            <input type="date" id="edit-opening-date" name="opening_date" class="form-control"
                                required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12 mb-3">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea id="edit-notes" name="notes" class="form-control" rows="3" placeholder="{{ __('Enter notes') }}"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="edit-is-active" name="is_active"
                                    value="1">
                                <label class="form-check-label" for="edit-is-active">
                                    {{ __('Active') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-device-floppy me-2"></i>{{ __('Update') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
