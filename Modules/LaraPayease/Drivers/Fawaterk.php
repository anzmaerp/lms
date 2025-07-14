<?php

namespace Modules\LaraPayease\Drivers;

use Illuminate\Support\Facades\DB;
use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class Fawaterk extends BasePaymentDriver
{


    use Currency;

    protected string $vendorKey;
    protected string $providerKey;
    protected string $baseUrl;

    public function __construct()
    {
        $paymentMethods = DB::table("optionbuilder__settings as os")->where('key', 'payment_method')->select('value')->first();
        $info = unserialize($paymentMethods->value)['fawaterk'];
        $this->vendorKey = $info['vendor_key'];
        $this->providerKey = $info['provider_key'];
        $this->baseUrl =  $info['enable_test_mode'] == '1'
            ? 'https://staging.fawaterk.com'
            : 'https://app.fawaterk.com';
        // dd($this->baseUrl);
    }

    public function getKeys(): array
    {
        return [
            'vendor_key' => $this->vendorKey,
            'provider_key' => $this->providerKey,
            'domain' => parse_url(url('/'), PHP_URL_HOST),
            // 'domain' => url('/'),
        ];
    }

    public function getMode(): string
    {
        return setting('fawaterk_production_mode') == '1' ? 'live' : 'test';
    }

    public function chargeCustomer(array $params)
    {
            $paymentData = session('payment_data');
            return redirect()->route('fawaterk.iframe', $paymentData['order_id']);

    }

    function generateHashKey()
    {
        $keys = $this->getKeys();
        $secretKey = $keys['vendor_key'];
        $domain = $keys['domain'];
        $providerKey = $keys['provider_key'];
        $queryParam = "Domain={$domain}&ProviderKey={$providerKey}";
        $hash = hash_hmac('sha256', $queryParam, $secretKey, false);
        return $hash;
    }

    public function paymentResponse(array $params): array
    {
        return [
            'status' => $params['status'] ?? 'pending',
            'transaction_id' => $params['transaction_id'] ?? null,
            'order_id' => session()->get('fawaterk_subscription_id'),
            'message' => $params['message'] ?? 'Transaction is being processed'
        ];
    }


    public function driverName(): string
    {
        return 'fawaterk';
    }
}
