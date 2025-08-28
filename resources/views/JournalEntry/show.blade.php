@extends('layouts.app')

@section('title', __('Journal Entry Details'))

@section('content')
    {!! breadcrumb([
        ['title' => __('Accounting')],
        ['title' => __('Journal Entries'), 'url' => route('journal-entries.index')],
        ['title' => __('Details')],
    ]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ __('Journal Entry Details') }}</h5>
                        <div class="btn-group">
                            <a href="{{ route('journal-entries.index') }}" class="btn btn-secondary btn-sm">
                                <i class="icon-base ti tabler-arrow-left me-1"></i>{{ __('Back to List') }}
                            </a>
                            <a href="{{ route('journal-entries.edit', $journalEntry->id) }}" class="btn btn-primary btn-sm">
                                <i class="icon-base ti tabler-edit me-1"></i>{{ __('Edit') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @include('JournalEntry.partials.show', ['journalEntry' => $journalEntry, 'attachments' => $attachments])

                        <!-- Attachments Section -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">{{ __('Attachments') }}</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($attachments->isNotEmpty())
                                            <div class="list-group">
                                                @foreach($attachments as $attachmentGroup)
                                                    @foreach($attachmentGroup->files as $file)
                                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                                            <a href="{{ route('journal-entries.attachments.download', $file->id) }}" target="_blank">
                                                                <i class="icon-base ti ti-file me-2"></i>{{ $file->file_name }} ({{ round($file->size / 1024, 2) }} KB)
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted">{{ __('No attachments found for this entry.') }}</p>
                                        @endif
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
