@forelse ($roles as $role)
    <tr>
        <td>{{ $role->id }}</td>
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
                @can('edit roles')
                    <a href="{{ route('roles.edit', $role) }}" class="btn btn-icon bg-transparent shadow-none" title="تعديل">
                        <i class="icon-base ti tabler-pencil"></i>
                    </a>
                @endcan
                <a href="{{ route('roles.edit_permissions', $role) }}" class="btn btn-icon bg-transparent shadow-none"
                    title="الصلاحيات">
                    <i class="icon-base ti tabler-lock"></i>
                </a>
                @can('delete roles')
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" style="display:inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-icon bg-transparent shadow-none" onclick="return confirm('حذف الدور؟')"
                            title="حذف">
                            <i class="icon-base ti tabler-trash"></i>
                        </button>
                    </form>
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="text-center py-4">
            <div class="empty-state">
                <i class="icon-base ti tabler-search fs-1 text-muted mb-3"></i>
                <h5 class="text-muted">{{ __('لا توجد أدوار') }}</h5>
                <p class="text-muted">{{ __('يمكنك إضافة دور جديد من الزر بالأعلى') }}</p>
            </div>
        </td>
    </tr>
@endforelse
