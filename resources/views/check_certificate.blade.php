@extends('layouts.frontend-app')
@section('content')

    <!doctype html>
    <html lang="ar" dir="rtl">

    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width,initial-scale=1" />
        <title>البحث عن شهادة</title>

        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">


    </head>

    <body>

        <h1>البحث عن شهادة</h1>

        <!-- Search form -->
        <form method="GET" action="">
            <label for="certificate_number">رقم الشهادة</label>
            <input type="text" id="certificate_number" name="certificate_number"
                value="{{ old('certificate_number', request('certificate_number')) }}" placeholder="مثال: ABC-2025-000123"
                required>
            <button type="submit">بحث</button>
        </form>

        <!-- Results -->
        @if (request()->filled('certificate_number'))
            @if ($certificate)
                <table>
                    <thead>
                        <tr>
                            <th>م</th>
                            <th>العنوان</th>
                            <th>الاسم</th>
                            <th>تاريخ الإصدار</th>
                            <th>صادر عن</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>{{ $certificate?->template->title ?? 'غير متوفر' }}</td>
                            <td>{{ $certificate?->wildcard_data['course_title'] ?? ($certificate?->wildcard_data['subject_name'] ?? 'غير متوفر') }}
                            </td>
                            <td>{{ $certificate?->created_at?->format(setting('_general.date_format')) ?? 'غير متوفر' }}
                            </td>
                            <td>{{ $certificate?->wildcard_data['issue_date'] ?? 'غير متوفر' }}</td>
                            <td class="actions">
                                <a href="{{ route('upcertify.certificate', $certificate->hash_id) }}"
                                    target="_blank">عرض</a>
                                <a href="{{ route('upcertify.download', $certificate->hash_id) }}">تحميل</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @else
                <div class="alert">
                    لم يتم العثور على شهادة بالرقم <strong>{{ e(request('certificate_number')) }}</strong>.
                </div>
            @endif
        @endif

    </body>

    </html>
@endsection
@push('styles')
    <style>
        body {
            margin: 0;
            font-family: "Inter", sans-serif;
            background: #f8fafc;
            color: #1f2937;
            padding: 30px;
            direction: rtl;
            text-align: right;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 22px;
            font-weight: 600;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 14px;
        }

        input[type="text"] {
            padding: 10px 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            width: 100%;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: right;
        }

        button {
            background: #4f46e5;
            color: #fff;
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        button:hover {
            background: #4338ca;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        thead {
            background: #f1f5f9;
        }

        th,
        td {
            padding: 12px 14px;
            font-size: 14px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
        }

        th {
            font-weight: 600;
            color: #374151;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .actions a {
            margin-left: 10px;
            color: #4f46e5;
            text-decoration: none;
            font-weight: 500;
        }

        .actions a:hover {
            text-decoration: underline;
        }

        .alert {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/css/splide.min.css" />
@endpush
