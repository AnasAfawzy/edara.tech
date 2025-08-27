@forelse ($roles as $role)
    <tr>
        <td>
            <span class="fw-bold">{{ $role->name }}</span>
        </td>
        <td>
            <div class="d-flex flex-wrap gap-1 justify-content-center">
                @foreach ($role->permissions as $perm)
                    <span class="badge bg-primary">{{ $perm->name }}</span>
                @endforeach
            </div>
        </td>
        <td>
            <div class="d-flex gap-2 justify-content-center">
                @can('view roles')
                    <a href="{{ route('roles.show', $role) }}" class="btn btn-icon bg-transparent shadow-none"
                        title="{{ __('View') }}">
                        <i class="icon-base ti tabler-eye"></i>
                    </a>
                @endcan
                @can('edit roles')
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-icon bg-transparent shadow-none"
                        title="{{ __('Edit') }}">
                        <i class="icon-base ti tabler-pencil"></i>
                    </a>
                @endcan
                @can('delete roles')
                    <button type="button" class="btn btn-icon bg-transparent shadow-none btn-delete-role"
                        data-url="{{ route('roles.destroy', $role) }}" title="{{ __('Delete') }}">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center py-4">
            <div class="empty-state">
                <i class="icon-base ti tabler-search fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('There are no roles') }}</h5>
            </div>
        </td>
    </tr>
@endforelse
