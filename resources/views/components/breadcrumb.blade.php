@push('css')
    <style>
        .breadcrumb {
            background: transparent;
            border-radius: 6px;
            padding: 0.75rem 1rem;
        }

        .breadcrumb .breadcrumb-item,
        .breadcrumb .breadcrumb-item a {
            color: var(--bs-primary) !important;
            font-weight: 500;
            text-shadow: none;
        }

        .breadcrumb .breadcrumb-item.active {
            color: var(--bs-primary-dark, var(--bs-primary)) !important;
            font-weight: bold;
        }
    </style>
@endpush
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        @foreach ($items as $i => $item)
            @if (isset($item['url']) && $i < count($items) - 1)
                <li class="breadcrumb-item">
                    <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $item['title'] }}
                </li>
            @endif
        @endforeach
    </ol>
</nav>
