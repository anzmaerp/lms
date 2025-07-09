<?php

namespace Modules\LaraPayease\Drivers;

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
        $this->vendorKey = Crypt::decryptString(setting('fawaterk_vendor_key'));
        $this->providerKey = Crypt::decryptString(setting('fawaterk_provider_key'));
        $this->baseUrl = setting('fawaterk_production_mode') == '1'
            ? 'https://app.fawaterk.com'
            : 'https://staging.fawaterk.com';
    }

    public function getKeys(): array
    {
        return [
            'vendor_key' => $this->vendorKey,
            'provider_key' => $this->providerKey,
            'domain' => setting('fawaterk_domain'),
        ];
    }

    public function generateHashKey(array $keys): string
    {
        $query = "Domain={$keys['domain']}&ProviderKey={$keys['provider_key']}";
        return hash_hmac('sha256', $query, $keys['vendor_key'], false);
    }

    public function getMode(): string
    {
        return setting('fawaterk_production_mode') == '1' ? 'live' : 'test';
    }

    public function chargeCustomer(array $params)
    {
        try {
            $keys = $this->getKeys();

            $orderId = 'FAW_' . time() . '_' . \Str::random(6);

            session()->put('fawaterk_order_id', $orderId);
            session()->put('fawaterk_subscription_id', $params['order_id']);

            $payload = [
                'envType' => $this->getMode(),
                'hashKey' => $this->generateHashKey($keys),
                'cartTotal' => $this->chargeableAmount($params['amount']),
                'currency' => $this->getCurrency(),
                'customer' => [
                    'first_name' => $params['first_name'] ?? 'Customer',
                    'last_name' => $params['last_name'] ?? 'Name',
                    'customer_unique_id' => $params['user_id'] ?? $orderId,
                ],
                'payLoad' => [
                    'custom_field1' => $params['order_id'] ?? $orderId,
                    'custom_field2' => $params['user_id'] ?? '',
                ],
                'redirectionUrls' => [
                    'successUrl' => 'http://lms.localhost/payment/fawaterk/success',
                    'failUrl'    => 'http://lms.localhost/payment/fawaterk/fail',
                    'pendingUrl' => 'http://lms.localhost/payment/fawaterk/pending',
                ],
                'cartItems' => $params['items'],
                'redirectOutIframe' => true,
                'style' => ['listing' => 'vertical']
            ];

            $response = \Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->vendorKey,
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/v2/createInvoice', $payload);

            if ($response->successful()) {
                return [
                    'status' => 200,
                    'redirect_url' => $response->json()['url'] ?? null,
                ];
            }

            return [
                'status' => 400,
                'message' => $response->body()
            ];
        } catch (\Exception $e) {
            \Log::error('Fawaterk Error: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => $e->getMessage()
            ];
        }
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
