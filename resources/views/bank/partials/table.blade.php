<tbody>
    @forelse ($banks as $bank)
        <tr id="bank-row-{{ $bank->id }}">
            <td>{{ $bank->name }}</td>
            <td>{{ $bank->account_number }}</td>
            <td>{{ optional($bank->currency)->name }}</td>
            <td>{{ $bank->balance }}</td>
            <td>
                <span class="badge bg-label-{{ $bank->status ? 'success' : 'danger' }}">
                    {{ $bank->status ? __('Active') : __('Inactive') }}
                </span>
            </td>
            <td>
                <button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-bank"
                    data-id="{{ $bank->id }}" title="Edit Bank">
                    <i class="icon-base ti tabler-pencil"></i>
                </button>
                <button type="button" class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-bank"
                    data-id="{{ $bank->id }}" title="Delete Bank">
                    <i class="icon-base ti tabler-trash"></i>
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="7" class="text-center py-4">
                <div class="empty-state">
                    <i class="icon-base ti tabler-search fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No banks found') }}</h5>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
