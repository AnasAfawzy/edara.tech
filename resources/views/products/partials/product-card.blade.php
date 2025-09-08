<div class="product-card printable-card">
    <div class="d-flex justify-content-between align-items-start mb-2">
        <div>
            <h5 class="mb-0">{{ $product->name }}</h5>
            @if ($product->name_en)
                <small class="text-muted">{{ $product->name_en }}</small>
            @endif
        </div>
        <div class="d-flex flex-wrap gap-2">
            <span class="badge {{ $product->status_badge_class }}">{{ $product->formatted_status }}</span>
            <span class="badge {{ $product->stock_status_badge_class }}">{{ __('Stock') }}:
                {{ number_format($product->current_stock, 3) }}</span>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="ratio ratio-1x1 border rounded-2 overflow-hidden">
                <img src="{{ $product->image_url }}" class="w-100 h-100 object-fit-cover" alt="{{ $product->name }}">
            </div>
            <div class="mt-2 d-flex flex-wrap gap-2">
                @if ($product->category)
                    <span class="badge bg-label-info">{{ $product->category->name }}</span>
                @endif
                @if ($product->brand)
                    <span class="badge bg-label-secondary">{{ $product->brand->name }}</span>
                @endif
                @if ($product->unit)
                    <span class="badge bg-label-primary">{{ $product->unit->name }}</span>
                @endif
            </div>
        </div>

        <div class="col-md-8">
            <div class="row g-2">
                <div class="col-sm-6">
                    <div class="bg-light rounded-2 p-2">
                        <div class="small text-muted">{{ __('Purchase Price') }}</div>
                        <div class="fs-5 text-success fw-bold">{{ number_format($product->purchase_price, 2) }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="bg-light rounded-2 p-2">
                        <div class="small text-muted">{{ __('Sale Price') }}</div>
                        <div class="fs-5 text-primary fw-bold">{{ number_format($product->sale_price, 2) }}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="bg-light rounded-2 p-2">
                        <div class="small text-muted">{{ __('Profit') }}</div>
                        <div class="fw-bold">
                            {{ number_format($product->profit_amount, 2) }}
                            <span class="text-muted">({{ number_format($product->profit_margin, 1) }}%)</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="bg-light rounded-2 p-2">
                        <div class="small text-muted">{{ __('Stock') }}</div>
                        <div class="d-flex align-items-center gap-2">
                            <span
                                class="badge {{ $product->stock_status_badge_class }}">{{ number_format($product->current_stock, 3) }}</span>
                            <small class="text-muted">
                                {{ __('Min') }}: {{ $product->min_stock ?? '-' }} |
                                {{ __('Max') }}: {{ $product->max_stock ?? '-' }}
                            </small>
                        </div>
                        @if ($product->max_stock)
                            @php
                                $max = max(1, (float) $product->max_stock);
                                $val = min(100, round(($product->current_stock / $max) * 100));
                            @endphp
                            <div class="progress mt-2" style="height:6px">
                                <div class="progress-bar" role="progressbar" style="width: {{ $val }}%"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <hr class="my-2">
            <div class="row g-2">
                <div class="col-sm-6">
                    <div class="small text-muted">{{ __('Code') }}</div>
                    <div class="fw-semibold">{{ $product->code }}</div>
                </div>
                <div class="col-sm-6">
                    <div class="small text-muted">{{ __('Barcode') }}</div>
                    <div class="fw-semibold">{{ $product->barcode ?: '-' }}</div>
                </div>
                @if ($product->description)
                    <div class="col-12">
                        <div class="small text-muted">{{ __('Description') }}</div>
                        <div>{{ $product->description }}</div>
                    </div>
                @endif
                @if ($product->notes)
                    <div class="col-12">
                        <div class="small text-muted">{{ __('Notes') }}</div>
                        <div class="text-muted">{{ $product->notes }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
