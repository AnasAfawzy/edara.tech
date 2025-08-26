@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <h5 class="card-header">المستخدمون</h5>
            <div class="card-body">
                {{-- @can('create users') --}}
                <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">إضافة مستخدم</a>
                {{-- @endcan --}}
                <table class="table">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>البريد</th>
                            <th>الأدوار</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ implode(', ', $user->getRoleNames()->toArray()) }}</td>
                                <td>
                                    {{-- @can('edit users') --}}
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">تعديل</a>
                                    {{-- @endcan --}}
                                    {{-- @can('delete users') --}}
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger"
                                            onclick="return confirm('حذف المستخدم؟')">حذف</button>
                                    </form>
                                    {{-- @endcan --}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
