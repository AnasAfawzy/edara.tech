<tbody>
    @forelse($financialYears as $year)
        <tr id="financial-year-row-{{ $year->id }}">
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
                <div class="dropdown">
                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                        <i class="icon-base ti tabler-dots-vertical"></i>
                    </button>
                    <div class="dropdown-menu">
                        @if (!$year->is_closed)
                            <a class="dropdown-item edit-financial-year" href="javascript:void(0);" data-id="{{ $year->id }}">
                                <i class="icon-base ti tabler-pencil me-1"></i> تعديل
                            </a>
                        @endif

                        @if (!$year->is_closed && !$year->is_active)
                            <a class="dropdown-item activate-financial-year" href="javascript:void(0);" data-id="{{ $year->id }}">
                                <i class="icon-base ti tabler-play me-1"></i> تفعيل
                            </a>
                        @endif

                        @if ($year->is_active)
                            <a class="dropdown-item close-financial-year" href="javascript:void(0);" data-id="{{ $year->id }}">
                                <i class="icon-base ti tabler-lock me-1"></i> إغلاق
                            </a>
                        @endif

                        @if (!$year->is_active && !$year->is_closed)
                            <a class="dropdown-item delete-financial-year" href="javascript:void(0);" data-id="{{ $year->id }}">
                                <i class="icon-base ti tabler-trash me-1"></i> حذف
                            </a>
                        @endif
                    </div>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">لا توجد سنوات مالية مسجلة</p>
            </td>
        </tr>
    @endforelse
</tbody>
