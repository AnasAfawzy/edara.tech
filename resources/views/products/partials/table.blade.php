<tbody>
    @forelse($products as $product)
        <tr id="product-row-{{ $product->id }}">
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center">
                    <div class="flex-grow-1 ms-3 text-center">
                        <h6 class="mb-0">{{ $product->name }}</h6>
                        @if ($product->name_en)
                            <small class="text-muted">{{ $product->name_en }}</small>
                        @endif
                    </div>
                </div>
            </td>
            <td class="text-center">
                <span class="fw-bold">{{ $product->code }}</span>
                @if ($product->barcode)
                    <br><small class="text-muted">{{ $product->barcode }}</small>
                @endif
            </td>
            <td class="text-center">
                <span class="badge bg-label-info">{{ $product->category->name ?? __('Not specified') }}</span>
            </td>
            <td class="text-center">
                @if ($product->brand)
                    <span class="badge bg-label-secondary">{{ $product->brand->name }}</span>
                @else
                    <span class="text-muted">{{ __('Not specified') }}</span>
                @endif
            </td>
            <td class="text-center">
                <span class="badge bg-label-primary">{{ $product->unit->name ?? __('Not specified') }}</span>
            </td>
            <td class="text-center">
                <span class="fw-bold text-success">{{ number_format($product->purchase_price, 2) }}</span>
            </td>
            <td class="text-center">
                <span class="fw-bold text-primary">{{ number_format($product->sale_price, 2) }}</span>
            </td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center">
                    <span class="badge {{ $product->stock_status_badge_class }} me-2">
                        {{ number_format($product->current_stock, 3) }}
                    </span>
                    @if ($product->is_low_stock)
                        <i class="icon-base ti tabler-alert-triangle text-danger" title="{{ __('Low Stock') }}"></i>
                    @endif
                </div>
            </td>
            <td class="text-center">
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input toggle-status" type="checkbox" id="status-{{ $product->id }}"
                        data-id="{{ $product->id }}" {{ $product->is_active ? 'checked' : '' }}>
                </div>
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button type="button" class="btn btn-icon btn-text-info rounded-pill waves-effect view-product"
                        data-id="{{ $product->id }}" title="{{ __('View') }}">
                        <i class="icon-base ti tabler-eye"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-text-primary rounded-pill waves-effect edit-product"
                        data-id="{{ $product->id }}" title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-edit"></i>
                    </button>
                    <button type="button" class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-product"
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
