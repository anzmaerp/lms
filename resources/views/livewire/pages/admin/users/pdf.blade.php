@php
    use Carbon\Carbon;
@endphp
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>{{ $titleH }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lateef|Noto+Sans+Arabic&display=swap">
    <style>
        @page {
            header: page-header;
            footer: page-footer;
        }
        html,body {
            direction: {{ $dir }};
        }
        td {
            vertical-align: bottom;
        }
        .title {
            color: #003b99;
            font-size: 21px;
        }
        .main_table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #302e2e;
            text-align: center;
        }
        .main_td {
            padding: 12px 15px;
            border: 1px solid #302e2e;
            border-collapse: collapse !important;
            text-align: center;
            vertical-align: middle;
            white-space: nowrap;
        }
        .th {
            background-color: #EEEEEE;
            color: #1b1a1a;
            font-size: 14px;
        }
        a {
            text-decoration: none;
            color:initial;
        }
    </style>
</head>
<body>
    <h1>Hello World</h1>
</body>
</html>