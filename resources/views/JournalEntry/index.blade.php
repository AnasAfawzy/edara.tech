{{-- filepath: d:\xampp\htdocs\edara.tech_old\resources\views\JournalEntry\index.blade.php --}}
@extends('layouts.app')

@section('title', __('Journal Entries'))

@section('content')
    {!! breadcrumb([['title' => __('Accounting')], ['title' => __('Journal Entries')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- شريط البحث وعدد العناصر -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('journal-entries.index') }}"
                                class="d-flex align-items-center">
                                <input type="hidden" name="search" value="{{ $search ?? '' }}">
                                <input type="hidden" name="date_from" value="{{ $dateFrom ?? '' }}">
                                <input type="hidden" name="date_to" value="{{ $dateTo ?? '' }}">
                                <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    style="width: auto;" onchange="this.form.submit()">
                                    <option value="10" {{ ($perPage ?? 25) == 10 ? 'selected' : '' }}>10</option>
                                    <option value="25" {{ ($perPage ?? 25) == 25 ? 'selected' : '' }}>25</option>
                                    <option value="50" {{ ($perPage ?? 25) == 50 ? 'selected' : '' }}>50</option>
                                    <option value="100" {{ ($perPage ?? 25) == 100 ? 'selected' : '' }}>100</option>
                                </select>
                                <span class="ms-2">{{ __('entries') }}</span>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- فلاتر التاريخ والبحث -->
                            <form method="GET" action="{{ route('journal-entries.index') }}"
                                class="d-flex align-items-center gap-2">
                                <input type="hidden" name="perPage" value="{{ $perPage ?? 25 }}">

                                <!-- من تاريخ -->
                                <div class="input-group" style="width: 150px;">
                                    <input type="date" name="date_from" id="dateFrom"
                                        class="form-control form-control-sm" value="{{ $dateFrom ?? '' }}"
                                        placeholder="{{ __('From Date') }}">
                                </div>

                                <!-- إلى تاريخ -->
                                <div class="input-group" style="width: 150px;">
                                    <input type="date" name="date_to" id="dateTo" class="form-control form-control-sm"
                                        value="{{ $dateTo ?? '' }}" placeholder="{{ __('To Date') }}">
                                </div>

                                <!-- البحث -->
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search journal entries...') }}" value="{{ $search ?? '' }}">
                                    <button class="btn btn-outline-secondary btn-sm" type="submit">
                                        <i class="icon-base ti tabler-search"></i>
                                    </button>
                                </div>
                            </form>

                            <!-- الأزرار -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('journal-entries.create') }}" class="btn btn-primary btn-sm">
                                    <i class="icon-base ti tabler-plus me-1"></i>
                                    {{ __('Add Journal Entry') }}
                                </a>

                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-success btn-sm dropdown-toggle"
                                        data-bs-toggle="dropdown">
                                        <i class="icon-base ti tabler-file-export me-1"></i>{{ __('Export') }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" id="exportExcel">
                                                <i
                                                    class="icon-base ti tabler-file-spreadsheet me-1"></i>{{ __('Excel') }}</a>
                                        </li>
                                        <li><a class="dropdown-item" href="#" id="exportPdf">
                                                <i class="icon-base ti tabler-file-pdf me-1"></i>{{ __('PDF') }}</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-datatable table-responsive pt-0">
                        <table class="table table-hover" id="journalEntriesTable">
                            <thead class="table-light">
                                <tr class="text-center">
                                    <th>{{ __('Entry Number') }}</th>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('Currency') }}</th>
                                    <th>{{ __('Total Debit') }}</th>
                                    <th>{{ __('Total Credit') }}</th>
                                    <th>{{ __('Source Type') }}</th>
                                    <th>{{ __('Financial Year') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            @include('JournalEntry.partials.table', ['journalEntries' => $journalEntries])
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if ($journalEntries instanceof \Illuminate\Pagination\LengthAwarePaginator && $journalEntries->hasPages())
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    {{ __('Showing') }} {{ $journalEntries->firstItem() }} {{ __('to') }}
                                    {{ $journalEntries->lastItem() }} {{ __('of') }} {{ $journalEntries->total() }}
                                    {{ __('entries') }}
                                </div>
                                {{ $journalEntries->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal - أعرض من قبل -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-lg-down modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('View Journal Entry') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewEntryContent" style="min-height: 500px; padding: 1.5rem;">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        function reloadJournalEntriesTable() {
            let search = document.getElementById('search').value;
            let dateFrom = document.getElementById('dateFrom').value;
            let dateTo = document.getElementById('dateTo').value;
            let perPage = document.getElementById('perPage').value;

            const params = new URLSearchParams({
                search: search,
                date_from: dateFrom,
                date_to: dateTo,
                per_page: perPage
            });

            fetch(`{{ route('journal-entries.search') }}?${params}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelector('#journalEntriesTable tbody').outerHTML = data.html;
                        reattachEventListeners();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

            // View Journal Entry
            function attachViewEvents() {
                document.querySelectorAll('.view-entry').forEach(button => {
                    button.addEventListener('click', function() {
                        const entryId = this.dataset.id;

                        // إضافة loading state
                        const viewContent = document.getElementById('viewEntryContent');
                        viewContent.innerHTML = `
                            <div class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">{{ __('Loading...') }}</span>
                                </div>
                                <p class="mt-3 text-muted">{{ __('Loading journal entry details...') }}</p>
                            </div>
                        `;

                        fetch(`{{ url('journal-entries') }}/${entryId}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.text();
                            })
                            .then(html => {
                                viewContent.innerHTML = html;
                                viewModal.show();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                viewContent.innerHTML = `
                                    <div class="text-center py-5">
                                        <i class="icon-base ti tabler-alert-circle text-danger" style="font-size: 4rem;"></i>
                                        <h6 class="mt-3 text-danger">{{ __('Error') }}</h6>
                                        <p class="text-muted">{{ __('Failed to load journal entry details') }}</p>
                                    </div>
                                `;
                                viewModal.show();
                            });
                    });
                });
            }

            // Delete Journal Entry
            function attachDeleteEvents() {
                document.querySelectorAll('.delete-entry').forEach(button => {
                    button.addEventListener('click', function() {
                        const entryId = this.dataset.id;

                        Swal.fire({
                            title: '{{ __('Are you sure?') }}',
                            text: '{{ __('This action cannot be undone') }}',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: '{{ __('Yes, delete it!') }}',
                            cancelButtonText: '{{ __('Cancel') }}'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch(`{{ url('journal-entries') }}/${entryId}`, {
                                        method: 'DELETE',
                                        headers: {
                                            'X-CSRF-TOKEN': document.querySelector(
                                                'meta[name="csrf-token"]').content,
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                icon: 'success',
                                                title: '{{ __('Deleted!') }}',
                                                text: data.message,
                                                timer: 2000,
                                                showConfirmButton: false
                                            }).then(() => {
                                                reloadJournalEntriesTable();
                                            });
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: '{{ __('Error') }}',
                                                text: data.message ||
                                                    '{{ __('Failed to delete journal entry') }}'
                                            });
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            icon: 'error',
                                            title: '{{ __('Error') }}',
                                            text: '{{ __('An error occurred while deleting') }}'
                                        });
                                    });
                            }
                        });
                    });
                });
            }

            // Re-attach event listeners function
            function reattachEventListeners() {
                attachViewEvents();
                attachDeleteEvents();
            }

            // Search functionality
            document.getElementById('search').addEventListener('keyup', function() {
                reloadJournalEntriesTable();
            });

            // Date filter change
            document.getElementById('dateFrom').addEventListener('change', function() {
                reloadJournalEntriesTable();
            });

            document.getElementById('dateTo').addEventListener('change', function() {
                reloadJournalEntriesTable();
            });

            // Attach initial event listeners
            reattachEventListeners();

            // Export functionality
            document.getElementById('exportExcel').addEventListener('click', function(e) {
                e.preventDefault();
                // TODO: Implement Excel export
            });

            document.getElementById('exportPdf').addEventListener('click', function(e) {
                e.preventDefault();
                // TODO: Implement PDF export
            });
        });
    </script>
@endsection
