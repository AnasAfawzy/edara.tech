<tbody>
    @forelse($warehouses as $warehouse)
        <tr id="warehouse-row-{{ $warehouse->id }}">
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">{{ $warehouse->name ?? '-' }}</h6>
                    </div>
                </div>
            </td>
            <td>
                @if ($warehouse->notes)
                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $warehouse->notes }}">
                        {{ Str::limit($warehouse->notes, 50) }}
                    </span>
                @else
                    <span class="text-muted">{{ __('No notes') }}</span>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input toggle-status" type="checkbox" id="status-{{ $warehouse->id }}"
                        data-id="{{ $warehouse->id }}" {{ $warehouse->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="status-{{ $warehouse->id }}">
                        <span class="badge {{ $warehouse->status ? 'bg-label-success' : 'bg-label-secondary' }}">
                            {{ $warehouse->status ? __('Active') : __('Inactive') }}
                        </span>
                    </label>
                </div>
            </td>
            <td>
                <div class="d-flex gap-2">
                    <!-- زر التعديل -->
                    <button type="button"
                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-warehouse"
                        data-id="{{ $warehouse->id }}" title="{{ __('Edit') }}" data-bs-toggle="tooltip">
                        <i class="icon-base ti tabler-pencil"></i>
                    </button>

                    <!-- زر الحذف -->
                    <button type="button"
                        class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-warehouse"
                        data-id="{{ $warehouse->id }}" title="{{ __('Delete') }}" data-bs-toggle="tooltip">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">
                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                    {{-- <div class="avatar avatar-xl mb-3">
                        <div class="avatar-initial rounded bg-label-secondary">
                            <i class="icon-base ti tabler-building-warehouse" style="font-size: 2rem;"></i>
                        </div>
                    </div> --}}
                    <h6 class="mb-1">{{ __('No warehouses found') }}</h6>
                    {{-- <p class="text-muted mb-3">{{ __('Start by adding your first warehouse') }}</p>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#createModal">
                        <i class="icon-base ti tabler-plus me-1"></i>
                        {{ __('Add Warehouse') }}
                    </button> --}}
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
