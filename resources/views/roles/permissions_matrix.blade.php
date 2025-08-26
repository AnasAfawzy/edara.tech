@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <h5 class="card-header">الصلاحيات - {{ $role->name }}</h5>
            <div class="card-body">
                <form method="POST" action="{{ route('roles.update_permissions', $role) }}">
                    @csrf
                    @method('PUT')
                    <table class="table text-center">
                        <thead>
                            <tr>
                                <th>Module</th>
                                <th>View</th>
                                <th>Create</th>
                                <th>Edit</th>
                                <th>Delete</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($modules as $module)
                                <tr>
                                    <td>{{ $module['label'] }}</td>
                                    @foreach (['view', 'create', 'edit', 'delete'] as $action)
                                        <td>
                                            <input type="checkbox" name="permissions[]"
                                                value="{{ $action . ' ' . $module['name'] }}"
                                                {{ $role->hasPermissionTo($action . ' ' . $module['name']) ? 'checked' : '' }}>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button class="btn btn-primary">حفظ</button>
                </form>
            </div>
        </div>
    </div>
@endsection
