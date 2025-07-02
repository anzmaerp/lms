<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ __('fawaterk Payment Gateway') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 60px 20px;
            background-color: #f9f9f9;
        }
        h2 {
            color: #333;
        }
        .loader {
            border: 10px solid #f3f3f3;
            border-top: 10px solid #3498db;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            margin: 30px auto;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .payment-button {
            background-color: #4CAF50;
            color: white;
            padding: 14px 30px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 20px;
            transition: background-color 0.3s;
        }
        .payment-button:hover {
            background-color: #45a049;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>

    <h2>{{ __('fawaterk Payment Gateway') }}</h2>
    <p>{{ __('You will be redirected to complete your payment shortly.') }}</p>

    <form id="fawaterkForm" action="{{ $payment_url }}" method="GET">
        <button type="submit" class="payment-button" id="paymentButton">
            {{ __('Proceed to Payment') }}
        </button>
    </form>

    <div id="loader" class="hidden loader"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('fawaterkForm');
            const button = document.getElementById('paymentButton');
            const loader = document.getElementById('loader');

            form.addEventListener('submit', function(event) {
                button.classList.add('hidden');
                loader.classList.remove('hidden');
            });

            // Auto-submit the form after a short delay (optional)
            setTimeout(() => {
                if (form && !form.classList.contains('submitted')) {
                    form.classList.add('submitted');
                    form.submit();
                }
            }, 1500);
        });
    </script>

</body>
</html>
