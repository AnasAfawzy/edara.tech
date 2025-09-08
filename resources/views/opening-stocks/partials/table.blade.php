<tbody>
    @forelse($openingStocks as $openingStock)
        <tr id="opening-stock-row-{{ $openingStock->id }}">
            <td>
                <div class="d-flex align-items-center">
                    <div>
                        <h6 class="mb-0">{{ $openingStock->product->name }}</h6>
                        @if ($openingStock->product->name_en)
                            <small class="text-muted">{{ $openingStock->product->name_en }}</small>
                        @endif
                    </div>
                </div>
            </td>
            <td>
                <span class="badge bg-label-dark">{{ $openingStock->product->code }}</span>
            </td>
            <td>
                <span class="badge bg-label-info">
                    {{ $openingStock->product->category->name ?? __('Not specified') }}
                </span>
            </td>
            <td>
                @if ($openingStock->product->brand)
                    <span class="badge bg-label-secondary">{{ $openingStock->product->brand->name }}</span>
                @else
                    <span class="text-muted">{{ __('Not specified') }}</span>
                @endif
            </td>
            <td>
                <span class="badge bg-label-primary">
                    {{ $openingStock->product->unit->name ?? __('Not specified') }}
                </span>
            </td>
            <td class="text-center">
                <span class="fw-bold text-primary">
                    {{ number_format($openingStock->quantity, 3) }}
                </span>
            </td>
            <td class="text-center">
                <span class="fw-bold text-success">
                    {{ number_format($openingStock->unit_cost, 2) }} {{ __('EGP') }}
                </span>
            </td>
            <td class="text-center">
                <span class="fw-bold text-info">
                    {{ number_format($openingStock->total_cost, 2) }} {{ __('EGP') }}
                </span>
            </td>
            <td class="text-center">
                <span class="badge bg-label-dark">
                    {{ $openingStock->opening_date->format('Y-m-d') }}
                </span>
                <br><small class="text-muted">
                    {{ $openingStock->opening_date->diffForHumans() }}
                </small>
            </td>
            <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                    <button type="button"
                        class="btn btn-icon btn-text-primary rounded-pill waves-effect edit-opening-stock"
                        data-id="{{ $openingStock->id }}" title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-edit"></i>
                    </button>
                    <button type="button"
                        class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-opening-stock"
                        data-id="{{ $openingStock->id }}" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="10" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                    <i class="icon-base ti tabler-package-off text-muted mb-2" style="font-size: 3rem;"></i>
                    <h6 class="mb-2">{{ __('No opening stocks found') }}</h6>
                    <p class="text-muted mb-0">{{ __('Start by adding opening stocks for your products') }}</p>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
