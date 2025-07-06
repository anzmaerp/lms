<?php

return [
    'name' => 'LaraPayease',

    'paymob' => [
        'api_key' => env('PAYMOB_API_KEY', ''),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET', ''),
        'integration_id' => env('PAYMOB_INTEGRATION_ID', ''),
        'mode' => env('PAYMOB_MODE', 'test'),
    ],

    'hesabe' => [
        'merchant_id' => env('HESABE_MERCHANT_ID', ''),
        'access_code' => env('HESABE_ACCESS_CODE', ''),
        'secret_key' => env('HESABE_SECRET_KEY', ''),
        'mode' => env('HESABE_MODE', 'live'),
    ],

    // Remove hardcoded vendor_key/provider_key for Fawaterk
    // 'fawaterk' => [
    //     'vendor_key' => '', // kept empty to avoid accidental usage
    //     'provider_key' => '',
    //     'domain' => '',
    //     'success_url' => '',
    //     'fail_url' => '',
    //     'pending_url' => '',
    //     'enable_test_mode' => false,
    // ],

    'default_payment_methods' => [
        'stripe' => [
            'currency' => 'USD',
            'stripe_key' => '',
            'stripe_secret' => '',
            'status' => 'on',
            'exchange_rate' => '',
        ],
        'paytm' => [
            'status' => 'off',
            'currency' => 'INR',
            'exchange_rate' => '',
            'enable_test_mode' => '',
            'app_id' => '',
            'app_key' => '',
            'website' => '',
            'chennel' => '',
            'industry_type' => '',
        ],
        'paymob' => [
            'status' => 'off',
            'currency' => 'EGP',
            'exchange_rate' => '',
            'enable_test_mode' => 'on',
            'api_key' => '',
            'hmac_secret' => '',
            'integration_id' => '',
        ],
        'hesabe' => [
            'status' => 'off',
            'currency' => 'KWD',
            'exchange_rate' => '',
            'enable_test_mode' => 'off',
            'merchant_id' => '',
            'access_code' => '',
            'secret_key' => '',
        ],
        // 'fawaterk' => [
        //     'status' => 'off',
        //     'currency' => 'EGP',
        //     'exchange_rate' => '',
        //     'enable_test_mode' => 'off',
        //     'vendor_key' => '', 
        //     'provider_key' => '', 
        //     'success_url' => '',
        //     'fail_url' => '',
        //     'pending_url' => '',
        //     'domain' => '',
        // ],

        'fawaterk' => [
            'currency' => 'EGP',
            'vendor_key' => '',
            'providor_key' => '',
            'status' => 'on',
            'exchange_rate' => '',
        ],

    ],
];
