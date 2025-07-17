<html>
<head>
    <title>{{ __('Paymob Payment Gateway') }}</title>
    <style>
        .payment-iframe-container {
            position: relative;
            padding-bottom: 600px;
            height: 0;
            overflow: hidden;
        }
        .payment-iframe-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
    </style>
</head>
<body>
<div class="stripe-payment-wrapper">
    <div class="srtipe-payment-inner-wrapper">
        <div class="text-center" style="text-align: center; margin-bottom: 20px;">
            <h3>{{ __('Pay with Paymob') }}</h3>
            <img src="{{ asset('vendor/larapayease/images/paymob-logo.png') }}" alt="Paymob" style="max-height: 50px;">
            <p style="margin-top: 20px;">{{ __('Please complete your payment using the secure payment form below.') }}</p>
        </div>

        <div class="payment-iframe-container">
            <iframe src="{{ $iframe_url }}" 
                    frameborder="0"
                    allow="payment">
            </iframe>
        </div>

        <div style="text-align: center; margin-top: 20px; color: #777;">
            <p>{{ __('Order ID:') }} {{ $order_id }}</p>
        </div>
    </div>
</div>
</body>
</html> 