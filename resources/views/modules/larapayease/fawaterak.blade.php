<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('fawaterk Payment') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: #f5f5f5;
        }

        iframe {
            width: 100%;
            height: 100vh;
            border: none;
        }
    </style>
</head>
<body>
    <iframe src="{{ $payment_url }}" allowpaymentrequest></iframe>
</body>
</html>
