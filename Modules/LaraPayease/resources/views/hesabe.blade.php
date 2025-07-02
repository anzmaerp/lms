<html>
<head>
    <title>{{ __('Hesabe Payment Gateway') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        .loader {
            border: 16px solid #f3f3f3;
            border-radius: 50%;
            border-top: 16px solid #3498db;
            width: 80px;
            height: 80px;
            margin: 0 auto;
            animation: spin 2s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .payment-button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <h2>{{ __('Hesabe Payment Gateway') }}</h2>
    <p>{{ __('Please click the button below to proceed to payment.') }}</p>
    
    <form id="hesabeForm" action="{{ $payment_url }}" method="GET">
        <input type="hidden" name="merchantCode" value="{{ $merchant_code }}">
        <input type="hidden" name="accessCode" value="{{ $access_code }}">
        <input type="hidden" name="data" value="{{ $encrypted_data }}">
        <button type="submit" class="payment-button" id="paymentButton">
            {{ __('Proceed to Payment') }}
        </button>
    </form>
    
    <div class="hidden loader" id="loader"></div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('hesabeForm').addEventListener('submit', function() {
                document.getElementById('paymentButton').classList.add('hidden');
                document.getElementById('loader').classList.remove('hidden');
            });
            
            // Auto-submit the form after a short delay
            setTimeout(function() {
                document.getElementById('hesabeForm').submit();
                document.getElementById('paymentButton').classList.add('hidden');
                document.getElementById('loader').classList.remove('hidden');
            }, 1500);
        });
    </script>
</body>
</html> 