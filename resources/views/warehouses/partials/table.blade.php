<tbody>
    @forelse($warehouses as $warehouse)
        <tr>
            <td>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $warehouse->name }}</h6>
                    </div>
                </div>
            </td>
            <td>
                @if ($warehouse->notes)
                    <span class="text-wrap">{{ Str::limit($warehouse->notes, 50) }}</span>
                    @if (strlen($warehouse->notes) > 50)
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="tooltip"
                            title="{{ $warehouse->notes }}">
                            <i class="icon-base ti tabler-info-circle"></i>
                        </button>
                    @endif
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
                <div class="d-flex gap-1">
                    <button type="button"
                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-warehouse"
                        data-id="{{ $warehouse->id }}" title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-pencil"></i>
                    </button>
                    <button type="button"
                        class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-warehouse"
                        data-id="{{ $warehouse->id }}" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <i class="icon-base ti tabler-search-off mb-3" style="font-size: 3rem; color: #ddd;"></i>
                    <h6 class="mb-2">{{ __('No warehouses found') }}</h6>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
