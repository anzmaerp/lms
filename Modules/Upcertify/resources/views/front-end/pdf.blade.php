<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate</title>

    <link rel="stylesheet" href="{{ public_path('modules/upcertify/css/main.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ public_path('modules/upcertify/css/fonts.css') }}" type="text/css">

    <style>
        body { margin: 0; padding: 0; }
        .uc-certificateprint {
            width: 100%;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="uc-certificateprint uc-download">
        {!! $body !!}
    </div>
</body>
</html>
