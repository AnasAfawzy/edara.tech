@extends('layouts.app')

@section('title', 'إضافة سنة مالية جديدة')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-plus"></i>
                                إضافة سنة مالية جديدة
                            </h3>
                            <a href="{{ route('financial-years.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                العودة إلى القائمة
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('financial-years.store') }}" method="POST" id="financial-year-form">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            <i class="fas fa-tag"></i>
                                            اسم السنة المالية <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name') }}"
                                            placeholder="مثال: 2024-2025" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">
                                            أدخل اسم السنة المالية (مثال: 2024-2025)
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">
                                            <i class="fas fa-calendar-plus"></i>
                                            تاريخ البداية <span class="text-danger">*</span>
                                        </label>
                                        <input type="date" class="form-control @error('start_date') is-invalid @enderror"
                                            id="start_date" name="start_date" value="{{ old('start_date') }}" required>
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
                                            id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>ملاحظة:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>تاريخ النهاية يجب أن يكون بعد تاريخ البداية</li>
                                            <li>لا يمكن أن تتداخل السنة المالية مع سنة مالية أخرى موجودة</li>
                                            <li>يمكن أن تكون هناك سنة مالية نشطة واحدة فقط في أي وقت</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

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
                                    حفظ السنة المالية
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
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');
            const nameInput = document.getElementById('name');

            // Auto-generate name when dates change
            function updateName() {
                if (startDate.value && endDate.value) {
                    const start = new Date(startDate.value);
                    const end = new Date(endDate.value);
                    const startYear = start.getFullYear();
                    const endYear = end.getFullYear();

                    if (!nameInput.value || nameInput.value.includes('-')) {
                        nameInput.value = `${startYear}-${endYear}`;
                    }
                }
            }

            startDate.addEventListener('change', function() {
                if (endDate.value && this.value >= endDate.value) {
                    const nextYear = new Date(this.value);
                    nextYear.setFullYear(nextYear.getFullYear() + 1);
                    nextYear.setDate(nextYear.getDate() - 1);
                    endDate.value = nextYear.toISOString().split('T')[0];
                }
                updateName();
            });

            endDate.addEventListener('change', function() {
                if (startDate.value && this.value <= startDate.value) {
                    alert('تاريخ النهاية يجب أن يكون بعد تاريخ البداية');
                    this.value = '';
                    return;
                }
                updateName();
            });
        });
    </script>
@endpush
