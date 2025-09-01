<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ __('Journal Entries') }}</title>
    <style>
        body {
            font-family: 'dejavu sans', sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: right;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
        }

        .header p {
            margin: 5px 0;
        }
    </style>
</head>

<body dir="rtl">
    <div class="header">
        <h1>{{ __('Journal Entries Report') }}</h1>
        <p>{{ __('Date') }}: {{ now()->format('Y-m-d') }}</p>
        @if (request('date_from') || request('date_to'))
            <p>{{ __('Period') }}: {{ request('date_from', 'N/A') }} {{ __('to') }}
                {{ request('date_to', 'N/A') }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('Entry Number') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Description') }}</th>
                <th>{{ __('Currency') }}</th>
                <th>{{ __('Total Debit') }}</th>
                <th>{{ __('Total Credit') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Source Type') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($journalEntries as $entry)
                <tr>
                    <td>{{ $entry->entry_number }}</td>
                    <td>{{ $entry->entry_date->format('Y-m-d') }}</td>
                    <td>{{ $entry->description }}</td>
                    <td>{{ $entry->currency->code ?? 'N/A' }}</td>
                    <td>{{ number_format($entry->details->sum('debit'), 2) }}</td>
                    <td>{{ number_format($entry->details->sum('credit'), 2) }}</td>
                    <td>{{ ucfirst($entry->status) }}</td>
                    <td>{{ ucfirst($entry->source_type) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">
                        {{ __('No journal entries found for the selected criteria.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>

</html>
