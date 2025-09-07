<tbody>
    @forelse($categories as $category)
        <tr id="category-row-{{ $category->id }}">
            <td>
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $category->name }}</h6>
                    </div>
                </div>
            </td>
            <td>
                @if ($category->name_en)
                    <span>{{ $category->name_en }}</span>
                @else
                    <span class="text-muted">{{ __('Not specified') }}</span>
                @endif
            </td>
            <td>
                @if ($category->notes)
                    <span class="text-wrap">{{ Str::limit($category->notes, 50) }}</span>
                    @if (strlen($category->notes) > 50)
                        <button type="button" class="btn btn-link btn-sm p-0 ms-1" data-bs-toggle="tooltip"
                            title="{{ $category->notes }}">
                            <i class="icon-base ti tabler-info-circle"></i>
                        </button>
                    @endif
                @else
                    <span class="text-muted">{{ __('No notes') }}</span>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input toggle-status" type="checkbox" id="status-{{ $category->id }}"
                        data-id="{{ $category->id }}" {{ $category->status ? 'checked' : '' }}>
                    <label class="form-check-label" for="status-{{ $category->id }}">
                        <span class="badge {{ $category->status_badge_class }}">
                            {{ $category->formatted_status }}
                        </span>
                    </label>
                </div>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button type="button"
                        class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-category"
                        data-id="{{ $category->id }}" title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-pencil"></i>
                    </button>
                    <button type="button"
                        class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-category"
                        data-id="{{ $category->id }}" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <h6 class="mb-2">{{ __('No categories found') }}</h6>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
