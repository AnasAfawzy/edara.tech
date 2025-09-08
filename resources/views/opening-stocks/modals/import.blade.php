<!-- filepath: resources/views/opening-stocks/modals/import.blade.php -->
<div class="modal fade" id="importModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Import Opening Stocks from Excel') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="importForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">{{ __('Import Instructions') }}</h6>
                        <p class="mb-2">{{ __('Please ensure your Excel file has the following columns:') }}</p>
                        <ul class="mb-0">
                            <li>{{ __('Product Code') }} ({{ __('Required') }})</li>
                            <li>{{ __('Quantity') }} ({{ __('Required') }})</li>
                            <li>{{ __('Unit Cost') }} ({{ __('Required') }})</li>
                            <li>{{ __('Opening Date') }} ({{ __('Optional, format: YYYY-MM-DD') }})</li>
                            <li>{{ __('Notes') }} ({{ __('Optional') }})</li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required">{{ __('Excel File') }}</label>
                        <input type="file" name="excel_file" class="form-control"
                               accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">
                            {{ __('Accepted formats: Excel (.xlsx, .xls) or CSV') }}
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Default Opening Date') }}</label>
                        <input type="date" name="default_opening_date" class="form-control">
                        <div class="form-text">
                            {{ __('Will be used if opening date is not specified in the file') }}
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="skip_existing" value="1" checked>
                            <label class="form-check-label">
                                {{ __('Skip existing opening stocks') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        {{ __('Cancel') }}
                    </button>
                    <a href="{{ asset('templates/opening-stocks-template.xlsx') }}"
                       class="btn btn-outline-info" download>
                        <i class="ti ti-download me-2"></i>{{ __('Download Template') }}
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti ti-upload me-2"></i>{{ __('Import') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
