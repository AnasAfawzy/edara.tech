@extends('layouts.app')

@section('title', __('Journal Entries'))

@section('content')
    {!! breadcrumb([['title' => __('Accounting')], ['title' => __('Journal Entries')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- Search and Filters -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('journal-entries.index') }}" id="perPageForm"
                                class="d-flex align-items-center">
                                <label for="perPage" class="form-label me-2 mb-0">{{ __('Show') }}:</label>
                                <select name="perPage" id="perPage" class="form-select form-select-sm"
                                    style="width: auto;" onchange="document.getElementById('perPageForm').submit()">
                                    <option value="10" @selected(request('perPage', 25) == 10)>10</option>
                                    <option value="25" @selected(request('perPage', 25) == 25)>25</option>
                                    <option value="50" @selected(request('perPage', 25) == 50)>50</option>
                                    <option value="100" @selected(request('perPage', 25) == 100)>100</option>
                                </select>
                                <span class="ms-2">{{ __('entries') }}</span>
                            </form>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <!-- Filters -->
                            <div class="d-flex align-items-center gap-2">
                                <input type="date" name="date_from" id="dateFrom" class="form-control form-control-sm"
                                    value="{{ request('date_from') }}" placeholder="{{ __('From Date') }}"
                                    style="width: 150px;">
                                <input type="date" name="date_to" id="dateTo" class="form-control form-control-sm"
                                    value="{{ request('date_to') }}" placeholder="{{ __('To Date') }}"
                                    style="width: 150px;">
                                <div class="input-group" style="width: 250px;">
                                    <input type="text" name="search" id="search" class="form-control form-control-sm"
                                        placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
                                </div>

                                <select name="source_type" id="sourceType" class="form-select form-select-sm"
                                    style="width: 150px;">
                                    <option value="">{{ __('All Sources') }}</option>
                                    <option value="manual" @selected(request('source_type') == 'manual')>{{ __('Manual') }}</option>
                                    <option value="system" @selected(request('source_type') == 'system')>{{ __('System') }}</option>
                                </select>

                                <select name="reversal_status" id="reversalStatus" class="form-select form-select-sm"
                                    style="width: 150px;">
                                    <option value="">{{ __('All Statuses') }}</option>
                                    <option value="original" @selected(request('reversal_status') == 'original')>{{ __('Original') }}</option>
                                    <option value="reversed" @selected(request('reversal_status') == 'reversed')>{{ __('Reversed') }}</option>
                                    <option value="reversing" @selected(request('reversal_status') == 'reversing')>{{ __('Reversing') }}</option>
                                </select>
                            </div>

                            <!-- Action Buttons -->
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

            function reloadJournalEntriesTable() {
                let search = document.getElementById('search').value;
                let dateFrom = document.getElementById('dateFrom').value;
                let dateTo = document.getElementById('dateTo').value;
                let perPage = document.getElementById('perPage').value;
                let sourceType = document.getElementById('sourceType').value; // New
                let reversalStatus = document.getElementById('reversalStatus').value; // New

                const params = new URLSearchParams({
                    search: search,
                    date_from: dateFrom,
                    date_to: dateTo,
                    per_page: perPage,
                    source_type: sourceType, // New
                    reversal_status: reversalStatus // New
                });

                // Update URL for bookmarking
                // window.history.pushState({}, '', `{{ route('journal-entries.index') }}?${params}`);

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

            function reattachEventListeners() {
                attachViewEvents();
                attachDeleteEvents();
            }

            const debouncedReload = debounce(reloadJournalEntriesTable, 400);
            document.getElementById('search').addEventListener('keyup', debouncedReload);
            document.getElementById('dateFrom').addEventListener('change', reloadJournalEntriesTable);
            document.getElementById('dateTo').addEventListener('change', reloadJournalEntriesTable);

            // Add event listeners for new filters
            document.getElementById('sourceType').addEventListener('change', reloadJournalEntriesTable);
            document.getElementById('reversalStatus').addEventListener('change', reloadJournalEntriesTable);

            reattachEventListeners();

            // Export functionality (TODO)
            document.getElementById('exportExcel').addEventListener('click', (e) => e.preventDefault());
            document.getElementById('exportPdf').addEventListener('click', (e) => e.preventDefault());
        });
    </script>
@endsection
