<tbody>
    @forelse($products as $product)
        <tr id="product-row-{{ $product->id }}">
            <td>
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img src="{{ $product->image_url }}" class="rounded"
                            style="width: 40px; height: 40px; object-fit: cover;" alt="{{ $product->name }}">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-0">{{ $product->name }}</h6>
                        @if ($product->name_en)
                            <small class="text-muted">{{ $product->name_en }}</small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                <span class="fw-bold">{{ $product->code }}</span>
                @if ($product->barcode)
                    <br><small class="text-muted">{{ $product->barcode }}</small>
                @endif
            </td>
            <td>
                <span class="badge bg-label-info">{{ $product->category->name ?? __('Not specified') }}</span>
            </td>
            <td>
                @if ($product->brand)
                    <span class="badge bg-label-secondary">{{ $product->brand->name }}</span>
                @else
                    <span class="text-muted">{{ __('Not specified') }}</span>
                @endif
            </td>
            <td>
                <span class="badge bg-label-primary">{{ $product->unit->name ?? __('Not specified') }}</span>
            </td>
            <td>
                <span class="fw-bold text-success">{{ number_format($product->purchase_price, 2) }}</span>
            </td>
            <td>
                <span class="fw-bold text-primary">{{ number_format($product->sale_price, 2) }}</span>
                {{-- <br><small class="text-muted">{{ __('Profit') }}:
                    {{ number_format($product->profit_amount, 2) }}</small> --}}
            </td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="badge {{ $product->stock_status_badge_class }} me-2">
                        {{ number_format($product->current_stock, 3) }}
                    </span>
                    @if ($product->is_low_stock)
                        <i class="icon-base ti tabler-alert-triangle text-danger" title="{{ __('Low Stock') }}"></i>
                    @endif
                </div>
                @if ($product->min_stock)
                    <small class="text-muted">{{ __('Min') }}: {{ number_format($product->min_stock, 3) }}</small>
                @endif
            </td>
            <td>
                <div class="form-check form-switch">
                    <input class="form-check-input toggle-status" type="checkbox" id="status-{{ $product->id }}"
                        data-id="{{ $product->id }}" {{ $product->is_active ? 'checked' : '' }}>
                    <label class="form-check-label" for="status-{{ $product->id }}">
                        <span class="badge {{ $product->status_badge_class }}">
                            {{ $product->formatted_status }}
                        </span>
                    </label>
                </div>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-icon  btn-text-info rounded-pill waves-effect view-product"
                        data-id="{{ $product->id }}" title="{{ __('View') }}">
                        <i class="icon-base ti tabler-eye"></i>
                    </button>
                    <button type="button"
                        class="btn btn-icon  btn-text-primary rounded-pill waves-effect edit-product"
                        data-id="{{ $product->id }}" title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-edit"></i>
                    </button>
                    <button type="button"
                        class="btn btn-icon  btn-text-danger rounded-pill waves-effect delete-product"
                        data-id="{{ $product->id }}" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </div>
            </td>

        </tr>
    @empty
        <tr>
            <td colspan="10" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <h6 class="mb-2">{{ __('No products found') }}</h6>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
