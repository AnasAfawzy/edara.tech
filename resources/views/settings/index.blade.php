@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <h5 class="card-header">الإعدادات العامة</h5>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <form action="{{ route('settings.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <h4>بيانات الشركة</h4>
                            <div class="mb-3">
                                <label>اسم الشركة</label>
                                <input type="text" name="company_name" class="form-control"
                                    value="{{ $settings->company_name ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label>البريد الإلكتروني</label>
                                <input type="email" name="company_email" class="form-control"
                                    value="{{ $settings->company_email ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label>رقم الهاتف</label>
                                <input type="text" name="company_phone" class="form-control"
                                    value="{{ $settings->company_phone ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label>العنوان</label>
                                <input type="text" name="company_address" class="form-control"
                                    value="{{ $settings->company_address ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label>شعار الشركة</label>
                                <input type="file" name="company_logo" class="form-control">
                                @if (!empty($settings->company_logo))
                                    <img src="{{ asset('storage/' . $settings->company_logo) }}" width="100"
                                        class="mt-2">
                                @endif
                            </div>

                            <h4>الإعدادات المالية</h4>
                            <div class="mb-3">
                                <label>الرقم الضريبي</label>
                                <input type="text" name="tax_number" class="form-control"
                                    value="{{ $settings->tax_number ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label>نسبة الضريبة الافتراضية</label>
                                <input type="number" step="0.01" name="default_tax_rate" class="form-control"
                                    value="{{ $settings->default_tax_rate ?? '' }}">
                            </div>

                            <h4>إعدادات النظام</h4>
                            <div class="mb-3">
                                <label>العملة</label>
                                <input type="text" name="currency" class="form-control"
                                    value="{{ $settings->currency ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label>المنطقة الزمنية</label>
                                <input type="text" name="timezone" class="form-control"
                                    value="{{ $settings->timezone ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label>اللغة</label>
                                <input type="text" name="language" class="form-control"
                                    value="{{ $settings->language ?? '' }}">
                            </div>

                            <h4>طرق الدفع</h4>
                            <div class="mb-3">
                                <select name="payment_methods[]" class="form-control" multiple>
                                    <option value="cash"
                                        {{ in_array('cash', $settings->payment_methods ?? []) ? 'selected' : '' }}>كاش
                                    </option>
                                    <option value="card"
                                        {{ in_array('card', $settings->payment_methods ?? []) ? 'selected' : '' }}>بطاقة
                                    </option>
                                    <option value="bank"
                                        {{ in_array('bank', $settings->payment_methods ?? []) ? 'selected' : '' }}>تحويل
                                        بنكي</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">حفظ</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
