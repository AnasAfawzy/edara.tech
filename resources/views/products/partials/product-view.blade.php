<div class="product-full-view">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
        <div class="pe-2">
            {{-- <h5 class="mb-1">{{ $product->name }}</h5> --}}
            @if ($product->name_en)
                <div class="text-muted">{{ $product->name_en }}</div>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge {{ $product->status_badge_class }}">
                <i class="ti tabler-power me-1"></i>{{ $product->formatted_status }}
            </span>
            <span class="badge {{ $product->stock_status_badge_class }}">
                <i class="ti tabler-packages me-1"></i>{{ __('Stock') }}:
                {{ number_format($product->current_stock, 3) }}
            </span>
            @if ($product->unit)
                <span class="badge bg-label-primary"><i
                        class="ti tabler-ruler-3 me-1"></i>{{ $product->unit->name }}</span>
            @endif
        </div>
    </div>

    <div class="row g-3">
        {{-- Image + chips --}}
        <div class="col-md-4">
            <div class="border rounded-3 overflow-hidden" style="aspect-ratio:1/1;">
                @if ($product->image || !empty($product->image))
                    <img src="{{ $product->image }}" class="w-100 h-100" style="object-fit:cover;"
                        alt="{{ $product->name }}">
                @else
                    <img src="{{ asset('assets/img/products/product.png') }}" class="w-100 h-100"
                        style="object-fit:cover;" alt="{{ __('Default Product Image') }}">
                @endif
            </div>

            <div class="mt-2 d-flex flex-wrap gap-2">
                @if ($product->category)
                    <span class="badge bg-label-info"><i
                            class="ti tabler-category-2 me-1"></i>{{ $product->category->name }}</span>
                @endif
                @if ($product->brand)
                    <span class="badge bg-label-secondary"><i
                            class="ti tabler-tag me-1"></i>{{ $product->brand->name }}</span>
                @endif
                @if ($product->has_expiry)
                    <span class="badge bg-label-warning"><i
                            class="ti tabler-clock-hour-4 me-1"></i>{{ __('Has expiry') }}</span>
                @endif
            </div>
        </div>

        {{-- Stats --}}
        <div class="col-md-8">
            <div class="row g-2">
                <div class="col-sm-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="small text-muted"><i
                                class="ti tabler-shopping-bag me-1"></i>{{ __('Purchase Price') }}</div>
                        <div class="fs-4 fw-bold text-success">{{ number_format($product->purchase_price, 2) }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="small text-muted"><i class="ti tabler-credit-card me-1"></i>{{ __('Sale Price') }}
                        </div>
                        <div class="fs-4 fw-bold text-primary">{{ number_format($product->sale_price, 2) }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="small text-muted"><i class="ti tabler-coin me-1"></i>{{ __('Profit') }}</div>
                        <div class="fw-bold">
                            {{ number_format($product->profit_amount, 2) }}
                            <span class="text-muted">({{ number_format($product->profit_margin, 1) }}%)</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-3 rounded-3 bg-body-tertiary h-100">
                        <div class="small text-muted"><i
                                class="ti tabler-arrows-horizontal me-1"></i>{{ __('Stock Range') }}</div>
                        <div class="mb-1">
                            {{ __('Min') }}: {{ $product->min_stock ?? '-' }} |
                            {{ __('Max') }}: {{ $product->max_stock ?? '-' }}
                        </div>
                        @if ($product->max_stock)
                            @php
                                $max = max(1, (float) $product->max_stock);
                                $val = min(100, round(($product->current_stock / $max) * 100));
                            @endphp
                            <div class="progress" style="height:6px">
                                <div class="progress-bar" role="progressbar" style="width: {{ $val }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Meta list --}}
            <div class="card border-0 mt-3">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="ti tabler-hash me-1"></i>{{ __('Code') }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold text-break">{{ $product->code }}</span>
                            <button type="button" class="btn btn-xs btn-outline-secondary border-0"
                                data-copy="{{ $product->code }}" title="{{ __('Copy') }}">
                                <i class="ti tabler-copy"></i>
                            </button>
                        </div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span class="text-muted"><i class="ti tabler-barcode me-1"></i>{{ __('Barcode') }}</span>
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-semibold text-break">{{ $product->barcode ?: '-' }}</span>
                            @if ($product->barcode)
                                <button type="button" class="btn btn-xs btn-outline-secondary border-0"
                                    data-copy="{{ $product->barcode }}" title="{{ __('Copy') }}">
                                    <i class="ti tabler-copy"></i>
                                </button>
                            @endif
                        </div>
                    </li>
                    @if ($product->description)
                        <li class="list-group-item">
                            <div class="small text-muted mb-1"><i
                                    class="ti tabler-file-description me-1"></i>{{ __('Description') }}</div>
                            <div>{{ $product->description }}</div>
                        </li>
                    @endif
                    @if ($product->notes)
                        <li class="list-group-item">
                            <div class="small text-muted mb-1"><i class="ti tabler-note me-1"></i>{{ __('Notes') }}
                            </div>
                            <div class="text-muted">{{ $product->notes }}</div>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
