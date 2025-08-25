<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="utf-8">
    <title>تصدير العملات</title>
    <style>
        @font-face {
            font-family: 'Cairo';
            src: url({{ base_path('public/fonts/Cairo-Regular.ttf') }}) format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: 'Cairo', Arial, sans-serif;
            font-size: 12px;
            direction: rtl;
            text-align: right;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <h2 style="text-align:center;">تصدير العملات</h2>
    <table>
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>اسم العملة</th>
                <th>كود العملة</th>
                <th class="text-center">سعر الصرف</th>
                <th class="text-center">الحالة</th>
                <th class="text-center">تاريخ الإنشاء</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($currencies as $currency)
                <tr>
                    <td class="text-center">{{ $currency->id }}</td>
                    <td>{{ $currency->name }}</td>
                    <td>{{ $currency->code }}</td>
                    <td class="text-center">{{ number_format($currency->exchange_rate, 4) }}</td>
                    <td class="text-center">نشط</td>
                    <td class="text-center">{{ $currency->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
