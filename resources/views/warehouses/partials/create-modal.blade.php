<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true"
    data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalLabel">
                    <i class="icon-base ti tabler-plus me-2"></i>
                    {{ __('Add New Warehouse') }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="createWarehouseForm" novalidate>
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 mb-3">
                            <label for="warehouse-name" class="form-label">
                                {{ __('Warehouse Name') }} <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="warehouse-name" name="name"
                                placeholder="{{ __('Enter warehouse name') }}" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">{{ __('Notes') }}</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"
                                placeholder="{{ __('Enter additional notes') }}"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="status" class="form-label">
                                {{ __('Status') }} <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="">{{ __('Choose status') }}</option>
                                <option value="1" selected>{{ __('Active') }}</option>
                                <option value="0">{{ __('Inactive') }}</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="icon-base ti tabler-x me-1"></i>
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="icon-base ti tabler-plus me-1"></i>
                        {{ __('Add Warehouse') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
