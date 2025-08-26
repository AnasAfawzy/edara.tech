@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="card">
            <h5 class="card-header">إضافة مستخدم</h5>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label>الاسم</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>كلمة المرور</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>تأكيد كلمة المرور</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>الأدوار</label>
                        <select name="roles[]" class="form-control" multiple>
                            @foreach ($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary">حفظ</button>
                </form>
            </div>
        </div>
    </div>
@endsection
