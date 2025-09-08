@if ($products instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div>
        {{ $products->links() }}
    </div>
@else
    <div class="text-center text-muted py-3">{{ __('No results') }}</div>
@endif
