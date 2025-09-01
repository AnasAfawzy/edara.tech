@extends('layouts.app')

@section('title', __('Journal Entries'))

@section('content')
    {!! breadcrumb([['title' => __('Accounting')], ['title' => __('Journal Entries')]]) !!}

    <div class="container-fluid">

        <!-- Search and Filters Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3"><i class="icon-base ti tabler-filter"></i> {{ __('Filters') }}</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="search" class="form-label">{{ __('Search') }}</label>
                        <input type="text" name="search" id="search" value="{{ request('search') }}"
                            class="form-control" placeholder="{{ __('Search by number, description, account...') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="dateFrom" class="form-label">{{ __('From Date') }}</label>
                        <input type="date" name="date_from" id="dateFrom" value="{{ request('date_from') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label for="dateTo" class="form-label">{{ __('To Date') }}</label>
                        <input type="date" name="date_to" id="dateTo" value="{{ request('date_to') }}"
                            class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label for="statusFilter" class="form-label">{{ __('Entry Status') }}</label>
                        <select name="status" id="statusFilter" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="draft" @selected(request('status') == 'draft')>{{ __('Draft') }}</option>
                            <option value="pending" @selected(request('status') == 'pending')>{{ __('Pending') }}</option>
                            <option value="approved" @selected(request('status') == 'approved')>{{ __('Approved') }}</option>
                            <option value="posted" @selected(request('status') == 'posted')>{{ __('Posted') }}</option>
                            <option value="reversed" @selected(request('status') == 'reversed')>{{ __('Reversed') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="sourceType" class="form-label">{{ __('Source Type') }}</label>
                        <select name="source_type" id="sourceType" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="manual" @selected(request('source_type') == 'manual')>{{ __('Manual') }}</option>
                            <option value="system" @selected(request('source_type') == 'system')>{{ __('System') }}</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="reversalStatus" class="form-label">{{ __('Reversal Status') }}</label>
                        <select name="reversal_status" id="reversalStatus" class="form-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="original" @selected(request('reversal_status') == 'original')>{{ __('Original') }}</option>
                            <option value="reversed" @selected(request('reversal_status') == 'reversed')>{{ __('Reversed') }}</option>
                            <option value="reversing" @selected(request('reversal_status') == 'reversing')>{{ __('Reversing') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('journal-entries.index') }}" id="perPageForm"
                                class="d-flex align-items-center">
                                <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    style="width: auto;" onchange="reloadJournalEntriesTable()">
                                    <option value="10" @selected(request('perPage', 25) == 10)>10</option>
                                    <option value="25" @selected(request('perPage', 25) == 25)>25</option>
                                    <option value="50" @selected(request('perPage', 25) == 50)>50</option>
                                    <option value="100" @selected(request('perPage', 25) == 100)>100</option>
                                </select>
                                <span class="ms-2">{{ __('entries') }}</span>
                            </form>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('journal-entries.create') }}" class="btn btn-primary">
                                <i class="icon-base ti tabler-plus me-1"></i>
                                {{ __('Add Journal Entry') }}
                            </a>
                            <div class="dropdown">
                                <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="icon-base ti tabler-file-export me-1"></i>{{ __('Export') }}
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" id="exportExcel"><i
                                                class="icon-base ti tabler-file-spreadsheet me-1"></i>{{ __('Excel') }}</a>
                                    </li>
                                    <li><a class="dropdown-item" href="#" id="exportPdf"><i
                                                class="icon-base ti tabler-file-pdf me-1"></i>{{ __('PDF') }}</a></li>
                                </ul>
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
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Financial Year') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            @include('JournalEntry.partials.table', ['journalEntries' => $journalEntries])
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="card-footer" id="pagination-container">
                        {{ $journalEntries->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
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
        document.addEventListener('DOMContentLoaded', function() {
            const viewModal = new bootstrap.Modal(document.getElementById('viewModal'));

            function debounce(func, delay) {
                let timeout;
                return function(...args) {
                    const context = this;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), delay);
                };
            }

            function getFilterParams() {
                let search = document.getElementById('search').value;
                let dateFrom = document.getElementById('dateFrom').value;
                let dateTo = document.getElementById('dateTo').value;
                let sourceType = document.getElementById('sourceType').value;
                let reversalStatus = document.getElementById('reversalStatus').value;
                let status = document.getElementById('statusFilter').value;

                return new URLSearchParams({
                    search: search,
                    date_from: dateFrom,
                    date_to: dateTo,
                    source_type: sourceType,
                    reversal_status: reversalStatus,
                    status: status
                });
            }

            function reloadJournalEntriesTable() {
                let perPage = document.getElementById('perPage').value;
                const params = getFilterParams();
                params.append('per_page', perPage);

                window.history.pushState({}, '', `{{ route('journal-entries.index') }}?${params}`);

                fetch(`{{ route('journal-entries.search') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector('#journalEntriesTable tbody').innerHTML = data.html;
                            document.getElementById('pagination-container').innerHTML = data.pagination;
                            reattachEventListeners();
                        }
                    })
                    .catch(error => console.error('Error:', error));
            }

            function attachViewEvents() {
                document.querySelectorAll('.view-entry').forEach(button => {
                    button.addEventListener('click', function() {
                        const entryId = this.dataset.id;
                        const viewContent = document.getElementById('viewEntryContent');
                        viewContent.innerHTML =
                            `<div class="text-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">{{ __('Loading...') }}</span></div></div>`;

                        fetch(`{{ url('journal-entries') }}/${entryId}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'text/html'
                                }
                            })
                            .then(response => response.text())
                            .then(html => {
                                viewContent.innerHTML = html;
                                viewModal.show();
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                viewContent.innerHTML =
                                    `<div class="text-center py-5 text-danger">{{ __('Failed to load details') }}</div>`;
                                viewModal.show();
                            });
                    });
                });
            }

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
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire('{{ __('Deleted!') }}', data
                                                .message, 'success');
                                            reloadJournalEntriesTable();
                                        } else {
                                            Swal.fire('{{ __('Error') }}', data
                                                .message ||
                                                '{{ __('Failed to delete') }}',
                                                'error');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire('{{ __('Error') }}',
                                            '{{ __('An error occurred') }}',
                                            'error');
                                    });
                            }
                        });
                    });
                });
            }

            function attachWorkflowEvents() {
                document.querySelectorAll('.workflow-action').forEach(button => {
                    button.addEventListener('click', function() {
                        const entryId = this.dataset.id;
                        const action = this.dataset.action;

                        // منع الضغط المتكرر
                        if (this.disabled) return;

                        // حفظ النص الأصلي للزر
                        if (!this.dataset.originalHtml) {
                            this.dataset.originalHtml = this.innerHTML;
                        }

                        let confirmTitle = '{{ __('Are you sure?') }}';
                        let confirmText =
                            `{{ __('You are about to') }} ${action} {{ __('this entry.') }}`;
                        let confirmButton = '{{ __('Yes') }}';

                        if (action === 'approve') {
                            confirmTitle = '{{ __('Approve Entry?') }}';
                            confirmText = '{{ __('This will approve the journal entry.') }}';
                            confirmButton = '{{ __('Yes, approve it!') }}';
                        }

                        Swal.fire({
                            title: confirmTitle,
                            text: confirmText,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: confirmButton,
                            cancelButtonText: '{{ __('Cancel') }}'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // تعطيل الزر أثناء المعالجة
                                this.disabled = true;
                                this.innerHTML =
                                    '<span class="spinner-border spinner-border-sm me-1"></span>{{ __('Processing...') }}';

                                // إضافة timeout أقصر للاختبار
                                const timeoutId = setTimeout(() => {
                                    console.error('Request timeout');
                                    Swal.fire('{{ __('Error') }}',
                                        '{{ __('Request timeout. Please check the logs.') }}',
                                        'error');
                                    this.disabled = false;
                                    this.innerHTML = this.dataset.originalHtml;
                                }, 10000); // 10 seconds timeout للاختبار

                                console.log(
                                    `Starting ${action} request for entry ${entryId}`);

                                fetch(`{{ url('journal-entries') }}/${entryId}/${action}`, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Content-Type': 'application/json'
                                        }
                                    })
                                    .then(response => {
                                        clearTimeout(timeoutId);
                                        console.log(
                                            `Response status: ${response.status}`);

                                        if (!response.ok) {
                                            throw new Error(
                                                `HTTP error! status: ${response.status}`
                                                );
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        console.log('Response data:', data);
                                        if (data.success) {
                                            Swal.fire('{{ __('Success!') }}', data
                                                .message, 'success');
                                            reloadJournalEntriesTable();
                                        } else {
                                            Swal.fire('{{ __('Error') }}', data
                                                .message ||
                                                '{{ __('An error occurred') }}',
                                                'error');
                                        }
                                    })
                                    .catch(error => {
                                        clearTimeout(timeoutId);
                                        console.error('Request error:', error);
                                        Swal.fire('{{ __('Error') }}',
                                            `{{ __('Network error: ') }}${error.message}`,
                                            'error');
                                    })
                                    .finally(() => {
                                        this.disabled = false;
                                        this.innerHTML = this.dataset.originalHtml;
                                    });
                            }
                        });
                    });
                });
            }

            function reattachEventListeners() {
                attachViewEvents();
                attachDeleteEvents();
                attachWorkflowEvents();
            }

            const debouncedReload = debounce(reloadJournalEntriesTable, 400);
            document.getElementById('search').addEventListener('keyup', debouncedReload);
            document.getElementById('dateFrom').addEventListener('change', reloadJournalEntriesTable);
            document.getElementById('dateTo').addEventListener('change', reloadJournalEntriesTable);

            document.getElementById('sourceType').addEventListener('change', reloadJournalEntriesTable);
            document.getElementById('reversalStatus').addEventListener('change', reloadJournalEntriesTable);
            document.getElementById('statusFilter').addEventListener('change', reloadJournalEntriesTable);

            reattachEventListeners();

            // Export functionality
            document.getElementById('exportExcel').addEventListener('click', function(e) {
                e.preventDefault();
                const params = getFilterParams();
                window.location.href = `{{ route('journal-entries.export.excel') }}?${params}`;
            });

            document.getElementById('exportPdf').addEventListener('click', function(e) {
                e.preventDefault();
                const params = getFilterParams();
                window.location.href = `{{ route('journal-entries.export.pdf') }}?${params}`;
            });
        });
    </script>
@endsection
