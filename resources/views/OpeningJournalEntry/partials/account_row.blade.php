@php
    $debit = 0;
    $credit = 0;
    $statement = '';

    if (isset($openingJournalEntryDetailsMapped[$account->id])) {
        $detail = $openingJournalEntryDetailsMapped[$account->id];
        $debit = $detail->debit;
        $credit = $detail->credit;
        $statement = $detail->statement;
    }

    $padding = $level * 20;

    $canInput = $account->canInput ?? $account->slave == 1 && $account->has_sub == 0;
    $shouldShowSubtotal = $account->shouldShowSubtotal ?? $account->slave == 1 && $account->has_sub == 1;
    $isMainTitle = $account->isMainTitle ?? $account->slave == 0;

    if ($isMainTitle) {
        $rowClass = 'table-secondary fw-bold';
        $textClass = 'text-secondary fw-bold';
        $icon = '<i class="icon-base ti tabler-folder me-1"></i>';
    } elseif ($shouldShowSubtotal) {
        $rowClass = 'table-warning fw-bold';
        $textClass = 'text-warning fw-bold';
        $icon = '<i class="icon-base ti tabler-file-spark me-1"></i>';
    } else {
        $rowClass = '';
        $textClass = 'text-primary';
        $icon = '<i class="icon-base ti tabler-file me-1"></i>';
    }

    $ownerElForHtml = '';
    if ($account->ownerEl !== null && $account->ownerEl !== '') {
        $ownerElForHtml = (string) $account->ownerEl;
    }
@endphp

<tr class="{{ $rowClass }}" data-account-id="{{ $account->id }}" data-owner-el="{{ $ownerElForHtml }}"
    data-level="{{ $level }}" data-slave="{{ $account->slave }}" data-has-sub="{{ $account->has_sub }}">

    {{-- Account Code --}}
    <td class="align-middle">
        <span style="margin-left: {{ $padding }}px;" class="{{ $textClass }}">
            {!! $icon !!} {{ $account->code }}
        </span>
    </td>

    {{-- Account Name --}}
    <td class="align-middle">
        <span style="margin-left: {{ $padding }}px;" class="{{ $textClass }}">
            {{ $account->name }}
        </span>
    </td>

    @if ($canInput)
        {{-- الحسابات النهائية - يمكن الإدخال بها --}}
        <td class="text-center">
            <input type="number" min="0" name="details[{{ $account->id }}][debit]"
                class="form-control form-control-sm text-end debit-input"
                value="{{ old('details.' . $account->id . '.debit', $debit) }}" data-account-id="{{ $account->id }}">
        </td>

        <td class="text-center">
            <input type="number" min="0" name="details[{{ $account->id }}][credit]"
                class="form-control form-control-sm text-end credit-input"
                value="{{ old('details.' . $account->id . '.credit', $credit) }}"
                data-account-id="{{ $account->id }}">
        </td>

        <td>
            <input type="text" name="details[{{ $account->id }}][statement]" class="form-control form-control-sm"
                value="{{ old('details.' . $account->id . '.statement', $statement) }}"
                placeholder="{{ __('Optional description') }}">
        </td>
    @elseif ($shouldShowSubtotal)
        {{-- الحسابات الأب - عرض مجموع الفروع --}}
        <td class="text-center {{ $rowClass }}">
            <span class="fw-bold text-success subtotal-debit" data-account-id="{{ $account->id }}">0.00</span>
        </td>

        <td class="text-center {{ $rowClass }}">
            <span class="fw-bold text-danger subtotal-credit" data-account-id="{{ $account->id }}">0.00</span>
        </td>

        <td class="{{ $rowClass }}"></td>
    @else
        {{-- الحسابات الرئيسية - فارغة --}}
        <td class="{{ $rowClass }}"></td>
        <td class="{{ $rowClass }}"></td>
        <td class="{{ $rowClass }}"></td>
    @endif
</tr>
