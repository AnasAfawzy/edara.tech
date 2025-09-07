<tbody>
    @forelse($units as $unit)
        <tr id="unit-row-{{ $unit->id }}">
            <td>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $unit->name }}</h6>
                    </div>
                </div>
            </td>
            <td>
                @if ($unit->name_en)
                    <span>{{ $unit->name_en }}</span>
                @else
                    <span class="text-muted">{{ __('Not specified') }}</span>
                @endif
            </td>
            <td>
                <span class="badge bg-label-info">{{ $unit->symbol }}</span>
            </td>
            <td>
                @if ($unit->notes)
                    <span class="text-wrap">{{ Str::limit($unit->notes, 50) }}</span>
                    @if (strlen($unit->notes) > 50)
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="tooltip"
                            title="{{ $unit->notes }}">
                            <i class="icon-base ti tabler-info-circle"></i>
                        </button>
                    @endif
                @else
                    <span class="text-muted">{{ __('No notes') }}</span>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input toggle-status" type="checkbox" id="status-{{ $unit->id }}"
                        data-id="{{ $unit->id }}" {{ $unit->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="status-{{ $unit->id }}">
                        <span class="badge {{ $unit->status_badge_class }}">
                            {{ $unit->formatted_status }}
                        </span>
                    </label>
                </div>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-unit"
                        data-id="{{ $unit->id }}" title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-edit"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-unit"
                        data-id="{{ $unit->id }}" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <h6 class="mb-2">{{ __('No units found') }}</h6>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
