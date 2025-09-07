@extends('layouts.app')

@section('title', __('Warehouses'))

@section('content')
    {!! breadcrumb([['title' => __('Inventory')], ['title' => __('Warehouses')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    {{-- شريط العرض/البحث/الإضافة بنفس ستايل الأدوار --}}
                    <div class="card-header d-flex justify-content-between align-items-center">
                        {{-- عرض عدد الصفوف --}}
                        <div class="d-flex align-items-center">
                            <label for="wh-perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                            <select id="wh-perPage" class="form-select form-select-sm" style="width:auto;">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span class="ms-2">{{ __('entries') }}</span>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            {{-- البحث --}}
                            <label for="wh-search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                            <div class="input-group" style="width:250px;">
                                <input type="text" id="wh-search" class="form-control form-control-sm"
                                    placeholder="{{ __('Search warehouses...') }}">
                            </div>

                            {{-- زر إضافة مخزن - تحديث data-bs-target --}}
                            <button id="btnOpenCreate" type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#createModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Warehouse') }}
                            </button>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="card-datatable table-responsive">
                        <table class="table table-hover" id="warehousesTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Warehouse') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            @include('warehouses.partials.table', ['warehouses' => $warehouses])
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($warehouses instanceof \Illuminate\Pagination\LengthAwarePaginator && $warehouses->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <small class="text-muted">
                                        {{ __('Showing') }} {{ $warehouses->firstItem() ?? 0 }} {{ __('to') }}
                                        {{ $warehouses->lastItem() ?? 0 }}
                                        {{ __('of') }} {{ $warehouses->total() }} {{ __('results') }}
                                    </small>
                                </div>
                                <div>
                                    {{ $warehouses->appends(request()->query())->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    @include('warehouses.partials.create-modal')

    <!-- Edit Modal -->
    @include('warehouses.partials.edit-modal')
@endsection

@section('scripts')
    <script>
        // تمرير الترجمات للـ JavaScript
        window.warehouseTranslations = {
            error: @json(__('Error')),
            success: @json(__('Success')),
            areYouSure: @json(__('Are you sure?')),
            confirmDeleteMessage: @json(__('This warehouse will be permanently deleted')),
            yesDelete: @json(__('Yes, Delete')),
            cancel: @json(__('Cancel')),
            adding: @json(__('Adding...')),
            updating: @json(__('Updating...')),
            addWarehouse: @json(__('Add Warehouse')),
            updateWarehouse: @json(__('Update Warehouse')),
            anErrorOccurredWhileLoadingData: @json(__('An error occurred while loading data')),
            anErrorOccurredWhileAddingWarehouse: @json(__('An error occurred while adding warehouse')),
            anErrorOccurredWhileUpdatingWarehouse: @json(__('An error occurred while updating warehouse')),
            anErrorOccurredWhileDeletingWarehouse: @json(__('An error occurred while deleting warehouse'))
        };

        // إضافة event listener يدوي للزر لتجنب مشاكل Bootstrap
        document.addEventListener('DOMContentLoaded', function() {
            const createBtn = document.getElementById('btnOpenCreate');
            const createModal = document.getElementById('createModal');

            if (createBtn && createModal) {
                createBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log('Opening create modal...');

                    try {
                        const modalInstance = new bootstrap.Modal(createModal, {
                            backdrop: 'static',
                            keyboard: false
                        });
                        modalInstance.show();
                    } catch (error) {
                        console.error('Error opening modal:', error);
                        // Fallback: استخدام jQuery إذا كان متوفراً
                        if (typeof $ !== 'undefined') {
                            $(createModal).modal('show');
                        }
                    }
                });
            } else {
                console.error('Create button or modal not found');
            }
        });
    </script>
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script src="{{ asset('assets/js/app-warehouses.js') }}?v={{ time() }}"></script>
@endsection
