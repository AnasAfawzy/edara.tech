<!-- filepath: resources/views/opening-stocks/modals/view.blade.php -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Opening Stock Details') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div id="opening-stock-details">
                    <!-- Content will be loaded here via JavaScript -->
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    {{ __('Close') }}
                </button>
                <button type="button" class="btn btn-primary" id="print-details">
                    <i class="ti ti-printer me-2"></i>{{ __('Print') }}
                </button>
            </div>
        </div>
    </div>
</div>
