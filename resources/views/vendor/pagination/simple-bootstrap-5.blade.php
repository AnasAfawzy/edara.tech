{{-- Previous --}}
@if ($paginator->onFirstPage())
    <li class="page-item disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
        <span class="page-link" aria-hidden="true">{{ __('pagination.previous') }}</span>
    </li>
@else
    <li class="page-item">
        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"
            aria-label="{{ __('pagination.previous') }}">
            {{ __('pagination.previous') }}
        </a>
    </li>
@endif

{{-- Next --}}
@if ($paginator->hasMorePages())
    <li class="page-item">
        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"
            aria-label="{{ __('pagination.next') }}">
            {{ __('pagination.next') }}
        </a>
    </li>
@else
    <li class="page-item disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
        <span class="page-link" aria-hidden="true">{{ __('pagination.next') }}</span>
    </li>
@endif
