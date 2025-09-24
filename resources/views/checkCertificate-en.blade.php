<!doctype html>
<html lang="en" dir="ltr">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: "Inter", sans-serif;
            background: #f8fafc;
            color: #1f2937;
            padding: 30px;
            direction: ltr;
            text-align: left;
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
            width: 97%;
            font-size: 14px;
            margin-bottom: 10px;
            text-align: left;
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
            text-align: left;
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
            margin-right: 10px;
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
</head>

<body>

    {{-- <h1>Search for Certificate</h1> --}}

    <form method="GET" action="">
        <label for="certificate_number">Certificate Number</label>
        <input type="text" id="certificate_number" name="certificate_number"
            value="{{ old('certificate_number', request('certificate_number')) }}" placeholder="e.g. ABC-2025-000123"
            required>
        <button type="submit">Search</button>
    </form>

    @if (request()->filled('certificate_number'))
        @if ($certificate)
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Address</th>
                        <th>Name</th>
                        <th>Issue Date</th>
                        <th>Issued By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>{{ $certificate?->template->title ?? 'Not Available' }}</td>
                        <td>{{ $certificate?->wildcard_data['course_title'] ?? ($certificate?->wildcard_data['subject_name'] ?? 'Not Available') }}
                        </td>
                        <td>{{ $certificate?->created_at?->format(setting('_general.date_format')) ?? 'Not Available' }}
                        </td>
                        <td>{{ $certificate?->wildcard_data['issue_date'] ?? 'Not Available' }}</td>
                        <td class="actions">
                            <a href="{{ route('upcertify.certificate', $certificate->hash_id) }}"
                                target="_blank">View</a>
                            <a href="{{ route('upcertify.download', $certificate->hash_id) }}">Download</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        @else
            <div class="alert">
                No certificate was found with the number <strong>{{ e(request('certificate_number')) }}</strong>.
            </div>
        @endif
    @endif

</body>
</html>
