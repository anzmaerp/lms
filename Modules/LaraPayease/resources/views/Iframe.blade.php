<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('fawaterk Payment') }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <script src="https://app.fawaterk.com/fawaterkPlugin/fawaterkPlugin.min.js"></script>

    <style>
        body {
            background-color: #f7f7f7;
            font-family: Arial, sans-serif;
            padding: 20px;
            margin: 0;
        }

        .payment-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }

        h2 {
            text-align: center;
            color: #333;
        }

        #fawaterkDivId {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <h2>{{ __('Complete Your Payment') }}</h2>
        <div id="fawaterkDivId"></div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const pluginConfig = {
                envType: "{{ $envType }}",
                hashKey: "{{ $hashKey }}",
                style: {
                    listing: "horizontal"
                },
                version: "0",
                requestBody: {
                    cartTotal: "{{ $amount }}",
                    currency: "{{ $currency }}",
                    customer: {
                        first_name: "{{ $customer['first_name'] }}",
                        last_name: "{{ $customer['last_name'] }}",
                        email: "{{ $customer['email'] }}",
                        phone: "{{ $customer['phone'] }}",
                        address: "{{ $customer['address'] }}"
                    },
                    redirectionUrls: {
                        successUrl: "{{ url('/payment/fawaterk/success') }}",
                        failUrl: "{{ url('/payment/fawaterk/fail') }}",
                        pendingUrl: "{{ url('/payment/fawaterk/pending') }}"
                    },
                    cartItems: @json($cartItems),
                    payLoad: {
                        custom_field1: "{{ $order_id }}",
                        custom_field2: "{{ $user_id }}"
                    }
                }
            };

            fawaterkCheckout(pluginConfig);
        });
    </script>
</body>
</html>
