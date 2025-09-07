{{-- filepath: d:\xampp\htdocs\edara.tech_old\resources\views\livewire\warehouse-table.blade.php --}}
<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                            <select wire:model.live="perPage" id="perPage" class="form-select form-select-sm"
                                style="width: auto;">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                            <span class="ms-2">{{ __('entries') }}</span>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- البحث -->
                            <label for="search" class="form-label me-2 mb-0">{{ __('Search') }}:</label>
                            <div class="input-group" style="width: 250px;">
                                <input type="text" wire:model.live.debounce.300ms="search" id="search"
                                    class="form-control form-control-sm" placeholder="{{ __('Search warehouses...') }}">
                                <button class="btn btn-outline-secondary btn-sm" type="button">
                                    <i class="icon-base ti tabler-search"></i>
                                </button>
                            </div>

                            <!-- زر إضافة مخزن جديد -->
                            <button type="button" class="btn btn-primary btn-sm" wire:click="openModal">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Warehouse') }}
                            </button>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table" id="warehousesTable">
                            <thead>
                                <tr>
                                    <th>{{ __('Warehouse Name') }}</th>
                                    <th>{{ __('Notes') }}</th>
                                    <th>{{ __('Created At') }}</th>
                                    <th>{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouses as $warehouse)
                                    <tr id="warehouse-row-{{ $warehouse->id }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-initial rounded bg-label-primary me-2">
                                                    <i class="icon-base ti tabler-building-warehouse"></i>
                                                </div>
                                                <span class="fw-medium">{{ $warehouse->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($warehouse->notes)
                                                <span class="text-truncate"
                                                    style="max-width: 200px; display: inline-block;"
                                                    title="{{ $warehouse->notes }}">
                                                    {{ Str::limit($warehouse->notes, 50) }}
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small
                                                class="text-muted">{{ $warehouse->created_at->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
                                                    data-bs-toggle="dropdown">
                                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                                </button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="javascript:void(0);"
                                                        wire:click="edit({{ $warehouse->id }})">
                                                        <i class="icon-base ti tabler-edit me-1"></i>
                                                        {{ __('Edit') }}
                                                    </a>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0);"
                                                        wire:click="confirmDelete({{ $warehouse->id }})">
                                                        <i class="icon-base ti tabler-trash me-1"></i>
                                                        {{ __('Delete') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4">
                                            <div class="d-flex flex-column align-items-center">
                                                <i class="icon-base ti tabler-building-warehouse mb-2 text-muted"
                                                    style="font-size: 3rem;"></i>
                                                <p class="text-muted mb-0">{{ __('No warehouses found') }}</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($warehouses->hasPages())
                        <div>
                            {{ $warehouses->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Modal -->
    @if ($showModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" wire:ignore.self>
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isEditing ? __('Edit Warehouse') : __('Add New Warehouse') }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal" aria-label="Close"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label" for="name">
                                        {{ __('Warehouse Name') }} <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="name" wire:model="name"
                                        class="form-control @error('name') is-invalid @enderror"
                                        placeholder="{{ __('Warehouse Name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label" for="notes">{{ __('Notes') }}</label>
                                    <textarea id="notes" wire:model="notes" class="form-control @error('notes') is-invalid @enderror" rows="3"
                                        placeholder="{{ __('Enter notes (optional)') }}"></textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="closeModal">
                                {{ __('Cancel') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <span wire:loading.remove wire:target="save">
                                    <i
                                        class="icon-base ti tabler-{{ $isEditing ? 'device-floppy' : 'plus' }} me-1"></i>
                                    {{ $isEditing ? __('Update Warehouse') : __('Add Warehouse') }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    {{ $isEditing ? __('Updating...') : __('Adding...') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Success Message -->
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show position-fixed"
            style="top: 20px; right: 20px; z-index: 9999;">
            {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>

@push('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('show-delete-confirmation', (warehouseId) => {
                Swal.fire({
                    title: @json(__('Are You Sure')),
                    text: @json(__('Are you sure you want to delete this warehouse?')),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: @json(__('Yes Delete')),
                    cancelButtonText: @json(__('Cancel'))
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.call('delete', warehouseId);
                        Swal.fire({
                            title: @json(__('Success')),
                            text: @json(__('Warehouse deleted successfully')),
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });
        });

        // Auto dismiss alerts
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
@endpush
