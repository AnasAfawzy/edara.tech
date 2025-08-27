@extends('layouts.app')

@section('title', 'تعديل السنة المالية')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-edit"></i>
                                تعديل السنة المالية: {{ $financialYear->name }}
                            </h3>
                            <a href="{{ route('financial-years.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                العودة إلى القائمة
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('financial-years.update', $financialYear->id) }}" method="POST"
                            id="financial-year-form">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-tag"></i>
                                            اسم السنة المالية <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $financialYear->name) }}"
                                            placeholder="مثال: 2024-2025" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">
                                            <i class="fas fa-calendar-plus"></i>
                                            تاريخ البداية <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                            id="start_date" name="start_date"
                                            value="{{ old('start_date', $financialYear->start_date->format('Y-m-d')) }}"
                                            required @if ($financialYear->is_closed) disabled @endif>
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="end_date" class="form-label">
                                            <i class="fas fa-calendar-minus"></i>
                                            تاريخ النهاية <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control @error('end_date') is-invalid @enderror"
                                            id="end_date" name="end_date"
                                            value="{{ old('end_date', $financialYear->end_date->format('Y-m-d')) }}"
                                            required @if ($financialYear->is_closed) disabled @endif>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title">
                                                <i class="fas fa-info-circle"></i>
                                                معلومات السنة المالية
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <strong>الحالة:</strong>
                                                    @if ($financialYear->is_active)
                                                        <span class="badge bg-success">نشطة</span>
                                                    @elseif($financialYear->is_closed)
                                                        <span class="badge bg-secondary">مغلقة</span>
                                                    @else
                                                        <span class="badge bg-warning">غير نشطة</span>
                                                    @endif
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>المدة:</strong>
                                                    {{ $financialYear->start_date->diffInDays($financialYear->end_date) + 1 }}
                                                    يوم
                                                </div>
                                                <div class="col-md-4">
                                                    <strong>تاريخ الإنشاء:</strong>
                                                    {{ $financialYear->created_at->format('Y-m-d') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($financialYear->is_closed)
                                <div class="alert alert-warning">
                                    <i class="fas fa-lock"></i>
                                    <strong>تنبيه:</strong>
                                    هذه السنة المالية مغلقة. لا يمكن تعديل التواريخ للسنوات المالية المغلقة.
                                </div>
                            @endif

                            @if ($errors->has('error'))
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    {{ $errors->first('error') }}
                                </div>
                            @endif

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('financial-years.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i>
                                    إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    حفظ التغييرات
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const endDate = document.getElementById('end_date');
            const startDate = document.getElementById('start_date');

            endDate.addEventListener('change', function() {
                if (startDate.value && this.value <= startDate.value) {
                    alert('تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
                    this.value = '{{ $financialYear->end_date->format('Y-m-d') }}';
                    return;
                }
            });

            startDate.addEventListener('change', function() {
                if (endDate.value && this.value >= endDate.value) {
                    alert('تاريخ البداية يجب أن يكون قبل تاريخ النهاية');
                    this.value = '{{ $financialYear->start_date->format('Y-m-d') }}';
                    return;
                }
            });
        });
    </script>
@endpush
