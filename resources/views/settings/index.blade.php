@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container">
    <h2 class="mb-4">System Settings</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <ul class="nav nav-tabs" id="settingsTab" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#general">General</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#timezone">Timezone</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tax">Taxes</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#payment">Payments</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#ui">UI</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#report">Reports</a></li>
        </ul>

        <div class="tab-content border p-3 mt-2">
            {{-- General --}}
            <div class="tab-pane fade show active" id="general">
                <div class="mb-3">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control"
                           value="{{ $settings['company_name'] ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Logo</label>
                    <input type="file" name="company_logo" class="form-control">
                    @if(!empty($settings['company_logo']))
                        <img src="{{ asset('storage/'.$settings['company_logo']) }}" height="60" class="mt-2">
                    @endif
                </div>
                <div class="mb-3">
                    <label>Default Language</label>
                    <select name="language" class="form-select">
                        <option value="en" @selected(($settings['language'] ?? '')=='en')>English</option>
                        <option value="ar" @selected(($settings['language'] ?? '')=='ar')>Arabic</option>
                    </select>
                </div>
            </div>

            {{-- Timezone --}}
            <div class="tab-pane fade" id="timezone">
                <div class="mb-3">
                    <label>Timezone</label>
                    <input type="text" name="timezone" class="form-control"
                           value="{{ $settings['timezone'] ?? 'UTC' }}">
                </div>
            </div>

            {{-- Taxes --}}
            <div class="tab-pane fade" id="tax">
                <div class="mb-3">
                    <label>Tax Percentage</label>
                    <input type="number" step="0.01" name="tax_percentage" class="form-control"
                           value="{{ $settings['tax_percentage'] ?? 0 }}">
                </div>
                <div class="mb-3">
                    <label>VAT Percentage</label>
                    <input type="number" step="0.01" name="vat_percentage" class="form-control"
                           value="{{ $settings['vat_percentage'] ?? 0 }}">
                </div>
            </div>

            {{-- Payments --}}
            <div class="tab-pane fade" id="payment">
                <div class="mb-3">
                    <label>Enabled Payment Methods</label><br>
                    <label><input type="checkbox" name="payment_cash" value="1"
                        {{ ($settings['payment_cash'] ?? false) ? 'checked' : '' }}> Cash</label><br>
                    <label><input type="checkbox" name="payment_bank" value="1"
                        {{ ($settings['payment_bank'] ?? false) ? 'checked' : '' }}> Bank Transfer</label><br>
                    <label><input type="checkbox" name="payment_card" value="1"
                        {{ ($settings['payment_card'] ?? false) ? 'checked' : '' }}> Credit Card</label>
                </div>
            </div>

            {{-- UI --}}
            <div class="tab-pane fade" id="ui">
                <div class="mb-3">
                    <label>Theme</label>
                    <select name="theme" class="form-select">
                        <option value="light" @selected(($settings['theme'] ?? '')=='light')>Light</option>
                        <option value="dark" @selected(($settings['theme'] ?? '')=='dark')>Dark</option>
                    </select>
                </div>
            </div>

            {{-- Reports --}}
            <div class="tab-pane fade" id="report">
                <div class="mb-3">
                    <label>Report Header</label>
                    <textarea name="report_header" class="form-control">{{ $settings['report_header'] ?? '' }}</textarea>
                </div>
                <div class="mb-3">
                    <label>Report Footer</label>
                    <textarea name="report_footer" class="form-control">{{ $settings['report_footer'] ?? '' }}</textarea>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <button class="btn btn-primary">Save Settings</button>
        </div>
    </form>
</div>
@endsection
