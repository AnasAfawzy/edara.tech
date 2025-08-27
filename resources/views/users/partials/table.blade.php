<tbody id="users-table-body">
    @forelse ($users as $user)
        <tr id="user-row-{{ $user->id }}">
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>
                {{ $user->roles->pluck('name')->implode(', ') }}
            </td>
            <td>
                @can('edit users')
                    <button type="button" class="btn btn-icon btn-text-secondary rounded-pill waves-effect"
                        title="{{ __('Edit') }}" onclick="editUser({{ $user->id }})">
                        <i class="icon-base ti tabler-pencil"></i>
                    </button>
                @endcan
                @can('delete users')
                    <button type="button" class="btn btn-icon btn-text-danger rounded-pill waves-effect"
                        title="{{ __('Delete') }}" onclick="deleteUser({{ $user->id }})">
                        <i class="icon-base ti tabler-trash"></i>
                    </button>
                @endcan
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="text-center py-4">
                <div class="empty-state">
                    <i class="icon-base ti tabler-search fs-1 text-muted mb-3"></i>
                    <h5 class="text-muted">{{ __('No users found') }}</h5>
                </div>
            </td>
        </tr>
    @endforelse
</tbody>
