@extends('layouts.app')

@section('title', __('Accounts Settings'))

@section('content')
    {!! breadcrumb([['title' => __('Settings')], ['title' => __('Accounts Settings')]]) !!}

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">{{ __('Accounts Settings') }}</h5>
                    <div class="card-body">
                        <form action="{{ route('accounting-settings.store') }}" method="POST">
                            @csrf

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Default Customers Account') }}</label>
                                    @php
                                        $customerAccountId = acc_setting('default_customer_account');
                                        $customerAccount = $accounts->firstWhere('id', $customerAccountId);
                                    @endphp
                                    <input class="form-control" list="accountsList" id="defaultCustomerAccountInput"
                                        placeholder="{{ __('Search for account by name or code') }}"
                                        value="{{ old('default_customer_account_name', $customerAccount ? $customerAccount->name . ' — ' . $customerAccount->code : '') }}">
                                    <input type="hidden" name="default_customer_account" id="defaultCustomerAccountId"
                                        value="{{ old('default_customer_account', $customerAccountId) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Default Suppliers Account') }}</label>
                                    @php
                                        $supplierAccountId = acc_setting('default_supplier_account');
                                        $supplierAccount = $accounts->firstWhere('id', $supplierAccountId);
                                    @endphp
                                    <input class="form-control" list="accountsList" id="defaultSupplierAccountInput"
                                        placeholder="{{ __('Search for account by name or code') }}"
                                        value="{{ old('default_supplier_account_name', $supplierAccount ? $supplierAccount->name . ' — ' . $supplierAccount->code : '') }}">
                                    <input type="hidden" name="default_supplier_account" id="defaultSupplierAccountId"
                                        value="{{ old('default_supplier_account', $supplierAccountId) }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Default Banks Account') }}</label>
                                    @php
                                        $bankAccountId = acc_setting('default_bank_account');
                                        $bankAccount = $accounts->firstWhere('id', $bankAccountId);
                                    @endphp
                                    <input class="form-control" list="accountsList" id="defaultBankAccountInput"
                                        placeholder="{{ __('Search for account by name or code') }}"
                                        value="{{ old('default_bank_account_name', $bankAccount ? $bankAccount->name . ' — ' . $bankAccount->code : '') }}">
                                    <input type="hidden" name="default_bank_account" id="defaultBankAccountId"
                                        value="{{ old('default_bank_account', $bankAccountId) }}">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">{{ __('Default Vaults Account') }}</label>
                                    @php
                                        $cashVaultAccountId = acc_setting('default_cash_vault_account');
                                        $cashVaultAccount = $accounts->firstWhere('id', $cashVaultAccountId);
                                    @endphp
                                    <input class="form-control" list="accountsList" id="defaultCashVaultAccountInput"
                                        placeholder="{{ __('Search for account by name or code') }}"
                                        value="{{ old('default_cash_vault_account_name', $cashVaultAccount ? $cashVaultAccount->name . ' — ' . $cashVaultAccount->code : '') }}">
                                    <input type="hidden" name="default_cash_vault_account" id="defaultCashVaultAccountId"
                                        value="{{ old('default_cash_vault_account', $cashVaultAccountId) }}">
                                </div>
                            </div>

                            <datalist id="accountsList">
                                @foreach ($accounts as $acc)
                                    <option value="{{ $acc->name }} — {{ $acc->code }}"
                                        data-id="{{ $acc->id }}">
                                @endforeach
                            </datalist>

                            <button type="submit" class="btn btn-primary">
                                {{ __('Save Settings') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/sweetalert2.js') }}"></script>
    <script>
        // لكل خانة: عند اختيار الحساب من القائمة، خزّن الـ id في الحقل المخفي
        function handleAccountInput(inputId, hiddenId) {
            const input = document.getElementById(inputId);
            const hidden = document.getElementById(hiddenId);
            input.addEventListener('input', function() {
                const val = this.value.trim();
                let matchedOption = Array.from(document.querySelectorAll('#accountsList option'))
                    .find(opt => opt.value === val);
                if (matchedOption) {
                    hidden.value = matchedOption.dataset.id;
                } else {
                    hidden.value = '';
                }
            });
        }
        handleAccountInput('defaultCustomerAccountInput', 'defaultCustomerAccountId');
        handleAccountInput('defaultSupplierAccountInput', 'defaultSupplierAccountId');
        handleAccountInput('defaultBankAccountInput', 'defaultBankAccountId');
        handleAccountInput('defaultCashVaultAccountInput', 'defaultCashVaultAccountId');

        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: '{{ __('Success') }}',
                text: '{{ session('success') }}',
                timer: 1800,
                showConfirmButton: false
            });
        @endif

        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: '{{ __('Error') }}',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                showConfirmButton: true
            });
        @endif
    </script>
@endpush
