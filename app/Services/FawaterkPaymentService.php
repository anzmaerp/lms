<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class fawaterk extends BasePaymentDriver
{
    use Currency;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = $this->getBaseUrl();
    }

    protected function getBaseUrl(): string
    {
        return setting('fawaterk_production_mode') == '1'
            ? 'https://app.fawaterk.com'
            : 'https://staging.fawaterk.com';
    }

    public function chargeCustomer(array $params)
    {
        try {
            $keys = $this->getKeys();

            if (empty($keys['vendor_key']) || empty($keys['provider_key']) || empty($keys['domain'])) {
                return [
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => __('Missing Fawaterk credentials'),
                ];
            }

            $orderId = 'FAW_' . time() . '_' . Str::random(6);
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
                    'successUrl' => setting('fawaterk_success_url'),
                    'failUrl' => setting('fawaterk_fail_url'),
                    'pendingUrl' => setting('fawaterk_pending_url'),
                ],
                'cartItems' => $params['items'],
                'redirectOutIframe' => true,
                'style' => [
                    'listing' => 'vertical'
                ]
            ];

            Log::info('Fawaterk Payload:', $payload);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $keys['vendor_key'],
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/api/v2/createInvoice', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'status' => Response::HTTP_OK,
                    'redirect_url' => $data['url'] ?? null,
                ];
            }

            return [
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Fawaterk Payment Exception: ' . $e->getMessage());
            return [
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function paymentResponse(array $params)
    {
        $orderId = $params['custom_field1'] ?? session()->get('fawaterk_subscription_id');
        $transactionId = $params['transaction_id'] ?? $params['paymentId'] ?? $params['id'] ?? null;

        session()->forget('fawaterk_order_id');
        session()->forget('fawaterk_subscription_id');

        if (!$orderId || !$transactionId) {
            return [
                'status' => 400,
                'message' => 'Invalid or missing payment response data',
            ];
        }

        return [
            'status' => 200,
            'data' => [
                'order_id' => $orderId,
                'transaction_id' => $transactionId,
            ],
            'message' => 'Payment success',
        ];
    }

    public function getKeys(): array
    {
        return [
            'vendor_key' => Crypt::decryptString(setting('fawaterk_vendor_key')),
            'provider_key' => Crypt::decryptString(setting('fawaterk_provider_key')),
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

    public function driverName(): string
    {
        return 'fawaterk';
    }
}
