@extends('layouts.app')

@section('title', 'السنوات المالية')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-alt"></i>
                                إدارة السنوات المالية
                            </h3>
                            @can('create financial years')
                                <a href="{{ route('financial-years.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    إضافة سنة مالية جديدة
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="25%">اسم السنة المالية</th>
                                        <th width="15%">تاريخ البداية</th>
                                        <th width="15%">تاريخ النهاية</th>
                                        <th width="10%">المدة</th>
                                        <th width="15%">الحالة</th>
                                        <th width="15%">الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($financialYears as $index => $year)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <strong>{{ $year->name }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $year->start_date->format('Y-m-d') }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    {{ $year->end_date->format('Y-m-d') }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $year->start_date->diffInDays($year->end_date) + 1 }} يوم
                                            </td>
                                            <td>
                                                @if ($year->is_active)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> نشطة
                                                    </span>
                                                @elseif($year->is_closed)
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-lock"></i> مغلقة
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-pause"></i> غير نشطة
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    @can('view financial years')
                                                        <a href="{{ route('financial-years.show', $year->id) }}"
                                                            class="btn btn-sm btn-info" title="عرض">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endcan

                                                    @can('edit financial years')
                                                        @if (!$year->is_closed)
                                                            <a href="{{ route('financial-years.edit', $year->id) }}"
                                                                class="btn btn-sm btn-warning" title="تعديل">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        @endif
                                                    @endcan

                                                    @can('activate financial years')
                                                        @if (!$year->is_closed && !$year->is_active)
                                                            <form action="{{ route('financial-years.activate', $year->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-success"
                                                                    title="تفعيل"
                                                                    onclick="return confirm('هل أنت متأكد من تفعيل هذه السنة المالية؟')">
                                                                    <i class="fas fa-play"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan

                                                    @can('close financial years')
                                                        @if ($year->is_active)
                                                            <form action="{{ route('financial-years.close', $year->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    title="إغلاق"
                                                                    onclick="return confirm('هل أنت متأكد من إغلاق هذه السنة المالية؟ لن تتمكن من التراجع عن هذا الإجراء.')">
                                                                    <i class="fas fa-stop"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan

                                                    @can('delete financial years')
                                                        @if (!$year->is_active && !$year->is_closed)
                                                            <form action="{{ route('financial-years.destroy', $year->id) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger"
                                                                    title="حذف"
                                                                    onclick="return confirm('هل أنت متأكد من حذف هذه السنة المالية؟')">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">لا توجد سنوات مالية مسجلة</p>
                                                @can('create financial years')
                                                    <a href="{{ route('financial-years.create') }}" class="btn btn-primary">
                                                        <i class="fas fa-plus"></i>
                                                        إضافة أول سنة مالية
                                                    </a>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
