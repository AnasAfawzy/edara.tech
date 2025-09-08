<!doctype html>
<html dir="rtl" lang="ar">

<head>
    <meta charset="utf-8">
    <style>
        * {
            font-family: DejaVu Sans, Tahoma, Arial, sans-serif
        }

        body {
            margin: 10px
        }

        .wrap {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px
        }

        .title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 4px
        }

        .sub {
            color: #666;
            font-size: 12px;
            margin-bottom: 8px
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px
        }

        .box {
            background: #f6f7f9;
            border-radius: 6px;
            padding: 8px
        }

        .label {
            color: #777;
            font-size: 11px
        }

        .val {
            font-size: 14px;
            font-weight: 700
        }

        img {
            object-fit: cover;
            width: 100%;
            height: auto;
            border-radius: 6px
        }
    </style>
</head>

<body>
    <div class="wrap">
        <div class="title">{{ $product->name }}</div>
        @if ($product->name_en)
            <div class="sub">{{ $product->name_en }}</div>
        @endif

        <div class="grid" style="margin-bottom:8px">
            <div>
                <img src="{{ $product->image_url }}" alt="">
                <div style="margin-top:6px; display:flex; gap:6px; flex-wrap:wrap; font-size:11px">
                    @if ($product->category)
                        <span>🔖 {{ $product->category->name }}</span>
                    @endif
                    @if ($product->brand)
                        <span>🏷️ {{ $product->brand->name }}</span>
                    @endif
                    @if ($product->unit)
                        <span>📏 {{ $product->unit->name }}</span>
                    @endif
                </div>
            </div>

            <div class="grid">
                <div class="box">
                    <div class="label">سعر الشراء</div>
                    <div class="val">{{ number_format($product->purchase_price, 2) }}</div>
                </div>
                <div class="box">
                    <div class="label">سعر البيع</div>
                    <div class="val">{{ number_format($product->sale_price, 2) }}</div>
                </div>
                <div class="box">
                    <div class="label">الربح</div>
                    <div class="val">
                        {{ number_format($product->profit_amount, 2) }}
                        ({{ number_format($product->profit_margin, 1) }}%)
                    </div>
                </div>
                <div class="box">
                    <div class="label">المخزون</div>
                    <div class="val">{{ number_format($product->current_stock, 3) }}</div>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="box">
                <div class="label">الكود</div>
                <div class="val">{{ $product->code }}</div>
            </div>
            <div class="box">
                <div class="label">الباركود</div>
                <div class="val">{{ $product->barcode ?: '-' }}</div>
            </div>
        </div>

        @if ($product->description)
            <div class="box" style="margin-top:8px">
                <div class="label">الوصف</div>
                <div>{{ $product->description }}</div>
            </div>
        @endif
    </div>
</body>

</html>
