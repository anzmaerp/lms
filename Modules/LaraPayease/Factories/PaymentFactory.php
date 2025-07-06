<?php

namespace Modules\LaraPayease\Factories;

use Modules\LaraPayease\Drivers\Paytm;
use Modules\LaraPayease\Drivers\Hesabe;
use Modules\LaraPayease\Drivers\Paymob;
use Modules\LaraPayease\Drivers\Paypal;
use Modules\LaraPayease\Drivers\Stripe;
use Modules\LaraPayease\Drivers\Iyzipay;
use Modules\LaraPayease\Drivers\PayFast;
use Modules\LaraPayease\Drivers\Fawaterk;
use Modules\LaraPayease\Drivers\Paystack;
use Modules\LaraPayease\Drivers\RazorPay;
use Modules\LaraPayease\Utils\CurrencyUtil;
use Modules\LaraPayease\Drivers\Flutterwave;

class PaymentFactory
{

    /**
     * @return \Modules\LaraPayease\Drivers\Stripe
     */

    public function stripe(): Stripe
    {
        return new Stripe();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\RazorPay
     */

    public function razorpay(): RazorPay
    {
        return new RazorPay();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Paystack
     */

    public function paystack(): Paystack
    {
        return new Paystack();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Paytm
     */

    public function paytm(): Paytm
    {
        return new Paytm();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Flutterwave
     */

    public function flutterwave(): Flutterwave
    {
        return new Flutterwave();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Payfast
     */

    public function payfast(): PayFast
    {
        return new PayFast();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Paypal
     */

    public function paypal(): Paypal
    {
        return new Paypal();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Iyzipay
     */

    public function iyzipay(): Iyzipay
    {
        return new Iyzipay();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Paymob
     */

    public function paymob(): Paymob
    {
        return new Paymob();
    }

    /**
     * @return \Modules\LaraPayease\Drivers\Hesabe
     */


    public function hesabe(): Hesabe
    {
        return new Hesabe();
    }
    public function fawaterk(): Fawaterk
    {
        return new Fawaterk();
    }
    /**
     * @return \Modules\LaraPayease\Utils\CurrencyUtil\supportedCurrencies
     */

    public function supportedCurrencies(): array
    {
        return CurrencyUtil::$supportedCurrencies;
    }

    /**
     * @return array $supportedGateways
     */
    public function supportedGateways(): array
    {
        return [
            'stripe' => [
                'keys' => [
                    'stripe_key' => '',
                    'stripe_secret' => '',
                ],
                'status' => 'off',
                'currency' => 'USD',
                'exchange_rate' => '',
                'ipn_url_type' => 'get.success'
            ],
            'paypal' => [
                'status' => 'off',
                'keys' => [
                    'client_id' => '',
                    'secret_id' => '',
                ],
                'currency' => 'USD',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'get.success'
            ],
            'razorpay' => [
                'status' => 'off',
                'keys' => [
                    'public_key' => '',
                    'secret_key' => '',
                ],
                'currency' => 'INR',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'post.success'
            ],
            'paystack' => [
                'status' => 'off',
                'keys' => [
                    'email' => '',
                    'public_key' => '',
                    'secret_key' => '',
                ],
                'currency' => 'ZAR',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'get.success'
            ],
            'paytm' => [
                'status' => 'off',
                'keys' => [
                    'app_id' => '',
                    'app_key' => '',
                    'website' => '',
                    'chennel' => '',
                    'industry_type' => '',
                ],
                'currency' => 'INR',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'post.success'
            ],
            'flutterwave' => [
                'status' => 'off',
                'keys' => [
                    'public_key' => '',
                    'secret_key' => '',
                ],
                'currency' => 'USD',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'get.success'
            ],
            'payfast' => [
                'status' => 'off',
                'keys' => [
                    'merchant_id' => '',
                    'merchant_key' => '',
                    'pass_phrase' => '',
                ],
                'currency' => 'ZAR',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'get.success'
            ],
            'iyzipay' => [
                'status' => 'off',
                'keys' => [
                    'api_key' => '',
                    'secret_key' => '',
                ],
                'currency' => 'TRY',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'post.success'
            ],
            'paymob' => [
                'status' => 'off',
                'keys' => [
                    'api_key' => '',
                    'hmac_secret' => '',
                    'integration_id' => '',
                ],
                'currency' => 'EGP',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'get.success'
            ],
            'hesabe' => [
                'status' => 'off',
                'keys' => [
                    'merchant_id' => '109142025',
                    'access_code' => '0dce0280-727e-446c-9247-21b2d082df7c',
                    'secret_key' => 'nDreP2JpOqEkaEN0zm7o0x9vXdWAGyL3',
                ],
                'currency' => 'KWD',
                'exchange_rate' => '',
                'enable_test_mode' => false,
                'ipn_url_type' => 'get.success'
            ],
            'fawaterk' => [
                'status' => 'off',
                'keys' => [
                    'vendor_key'   => '',
                    'provider_key' => '',
                ],
                'exchange_rate' => '',
                'currency' => 'ُEGP',
            ],

        ];
    }

    /**
     * @return string $getIpnUrl
     */
    public function getIpnUrl($method): string
    {
        $gateWays = $this->supportedGateways();
        if (!empty($gateWays[$method]['ipn_url_type'])) {
            return $gateWays[$method]['ipn_url_type'];
        }
        return '';
    }
}
