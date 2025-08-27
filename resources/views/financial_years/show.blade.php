@extends('layouts.app')

@section('title', 'تفاصيل السنة المالية')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">
                                <i class="fas fa-eye"></i>
                                تفاصيل السنة المالية: {{ $financialYear->name }}
                            </h3>
                            <div>
                                @can('edit financial years')
                                    @if (!$financialYear->is_closed)
                                        <a href="{{ route('financial-years.edit', $financialYear->id) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i>
                                            تعديل
                                        </a>
                                    @endif
                                @endcan
                                <a href="{{ route('financial-years.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    العودة إلى القائمة
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <!-- معلومات أساسية -->
                            <div class="col-md-6">
                                <div class="card bg-primary text-white mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-info-circle"></i>
                                            المعلومات الأساسية
                                        </h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>الاسم:</strong><br>
                                                {{ $financialYear->name }}
                                            </div>
                                            <div class="col-6">
                                                <strong>الحالة:</strong><br>
                                                @if ($financialYear->is_active)
                                                    <span class="badge bg-success">نشطة</span>
                                                @elseif($financialYear->is_closed)
                                                    <span class="badge bg-secondary">مغلقة</span>
                                                @else
                                                    <span class="badge bg-warning">غير نشطة</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- التواريخ -->
                            <div class="col-md-6">
                                <div class="card bg-info text-white mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <i class="fas fa-calendar"></i>
                                            التواريخ والمدة
                                        </h5>
                                        <div class="row">
                                            <div class="col-4">
                                                <strong>البداية:</strong><br>
                                                {{ $financialYear->start_date->format('Y-m-d') }}
                                            </div>
                                            <div class="col-4">
                                                <strong>النهاية:</strong><br>
                                                {{ $financialYear->end_date->format('Y-m-d') }}
                                            </div>
                                            <div class="col-4">
                                                <strong>المدة:</strong><br>
                                                {{ $financialYear->start_date->diffInDays($financialYear->end_date) + 1 }}
                                                يوم
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- <!-- إحصائيات -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card bg-success text-white text-center">
                                    <div class="card-body">
                                        <i class="fas fa-file-invoice fa-2x mb-2"></i>
                                        <h4>{{ $financialYear->journalEntries()->count() ?? 0 }}</h4>
                                        <p class="mb-0">قيد محاسبي</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white text-center">
                                    <div class="card-body">
                                        <i class="fas fa-receipt fa-2x mb-2"></i>
                                        <h4>{{ $financialYear->invoices()->count() ?? 0 }}</h4>
                                        <p class="mb-0">فاتورة</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white text-center">
                                    <div class="card-body">
                                        <i class="fas fa-money-bill fa-2x mb-2"></i>
                                        <h4>{{ $financialYear->payments()->count() ?? 0 }}</h4>
                                        <p class="mb-0">دفعة</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-secondary text-white text-center">
                                    <div class="card-body">
                                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                                        <h4>
                                            @if ($financialYear->start_date <= now() && $financialYear->end_date >= now())
                                                {{ now()->diffInDays($financialYear->start_date) }}
                                            @else
                                                -
                                            @endif
                                        </h4>
                                        <p class="mb-0">يوم منذ البداية</p>
                                    </div>
                                </div>
                            </div>
                        </div> --}}

                        <!-- تفاصيل النظام -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-cogs"></i>
                                            تفاصيل النظام
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <strong>تاريخ الإنشاء:</strong><br>
                                                <span class="text-muted">
                                                    {{ $financialYear->created_at->format('Y-m-d H:i:s') }}
                                                    ({{ $financialYear->created_at->diffForHumans() }})
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>آخر تحديث:</strong><br>
                                                <span class="text-muted">
                                                    {{ $financialYear->updated_at->format('Y-m-d H:i:s') }}
                                                    ({{ $financialYear->updated_at->diffForHumans() }})
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <strong>ID:</strong><br>
                                                <span class="text-muted">{{ $financialYear->id }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- الإجراءات -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">
                                            <i class="fas fa-tools"></i>
                                            الإجراءات المتاحة
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex gap-2 flex-wrap">
                                            @can('activate financial years')
                                                @if (!$financialYear->is_closed && !$financialYear->is_active)
                                                    <form action="{{ route('financial-years.activate', $financialYear->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success"
                                                            onclick="return confirm('هل أنت متأكد من تفعيل هذه السنة المالية؟')">
                                                            <i class="fas fa-play"></i>
                                                            تفعيل السنة المالية
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('close financial years')
                                                @if ($financialYear->is_active)
                                                    <form action="{{ route('financial-years.close', $financialYear->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-danger"
                                                            onclick="return confirm('هل أنت متأكد من إغلاق هذه السنة المالية؟ لن تتمكن من التراجع عن هذا الإجراء.')">
                                                            <i class="fas fa-stop"></i>
                                                            إغلاق السنة المالية
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan

                                            @can('delete financial years')
                                                @if (!$financialYear->is_active && !$financialYear->is_closed)
                                                    <form action="{{ route('financial-years.destroy', $financialYear->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-outline-danger"
                                                            onclick="return confirm('هل أنت متأكد من حذف هذه السنة المالية؟')">
                                                            <i class="fas fa-trash"></i>
                                                            حذف السنة المالية
                                                        </button>
                                                    </form>
                                                @endif
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
