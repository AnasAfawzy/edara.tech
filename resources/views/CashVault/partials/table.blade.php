<tbody>
    @forelse ($cashVaults as $vault)
        <tr id="vault-row-{{ $vault->id }}">
            <td>{{ $vault->name }}</td>
            <td>{{ $vault->currency->name }}</td>
            <td>{{ $vault->balance }}</td>
            <td>
                <span class="badge bg-label-{{ $vault->status ? 'success' : 'danger' }}">
                    {{ $vault->status ? __('Active') : __('Inactive') }}
                </span>
            </td>
            <td>
                <button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect edit-vault"
                    data-id="{{ $vault->id }}" title="Edit Vault">
                    <i class="icon-base ti tabler-pencil"></i>
                </button>
                <button type="button" class="btn btn-icon btn-text-danger rounded-pill waves-effect delete-vault"
                    data-id="{{ $vault->id }}" title="Delete Vault">
                    <i class="icon-base ti tabler-trash"></i>
                </button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center py-4">
                <div class="empty-state">
                    <i class="icon-base ti tabler-search fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No vaults found') }}</h5>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
