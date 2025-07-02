<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class Paymob extends BasePaymentDriver
{
    use Currency;

    protected $baseUrl = 'https://accept.paymob.com/api';

    public function chargeCustomer(array $params)
    {
        try {
            // Step 1: Authentication
            $authToken = $this->authenticate();
            if (empty($authToken)) {
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to authenticate with Paymob')];
            }

            // Step 2: Order Registration
            $orderId = $this->registerOrder($authToken, $params);
            if (empty($orderId)) {
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to register order with Paymob')];
            }

            // Step 3: Payment Key Request
            $paymentKey = $this->getPaymentKey($authToken, $orderId, $params);
            if (empty($paymentKey)) {
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to get payment key from Paymob')];
            }

            // Store order ID in session for verification
            session()->put('paymob_order_id', $orderId);
            session()->put('paymob_subscription_id', $params['order_id']);

            // Return view with iframe URL
            return view('larapayease::paymob', [
                'iframe_url' => $this->getIframeUrl($paymentKey),
                'order_id' => $orderId
            ]);

        } catch (\Exception $e) {
            Log::error('Paymob payment error: ' . $e->getMessage());
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => $e->getMessage()];
        }
    }

    public function driverName(): string
    {
        return 'paymob';
    }

    public function paymentResponse(array $params = [])
    {
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }

        $orderId = session()->get('paymob_order_id');
        $subscriptionId = session()->get('paymob_subscription_id');
        session()->forget('paymob_order_id');
        session()->forget('paymob_subscription_id');

        if (empty($orderId) || empty($subscriptionId)) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Missing order information')];
        }

        // Verify the transaction
        $authToken = $this->authenticate();
        if (empty($authToken)) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to authenticate with Paymob')];
        }

        $transaction = $this->getTransaction($authToken, $orderId);
        if (empty($transaction)) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to verify transaction')];
        }

        // Verify HMAC signature
        if (!$this->verifyHmacSignature($transaction)) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Invalid transaction signature')];
        }

        if ($transaction['success'] === true) {
            return [
                'status' => Response::HTTP_OK,
                'data' => [
                    'transaction_id' => $transaction['id'],
                    'order_id' => $subscriptionId
                ]
            ];
        }

        return ['status' => Response::HTTP_BAD_REQUEST, 'order_id' => $subscriptionId];
    }

    protected function authenticate()
    {
        $apiKeys = $this->getKeys();
        try {
            $response = Http::post($this->baseUrl . '/auth/tokens', [
                'api_key' => $apiKeys['api_key']
            ]);

            if ($response->successful()) {
                return $response->json()['token'];
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob authentication error: ' . $e->getMessage());
            return null;
        }
    }

    protected function registerOrder($authToken, $params)
    {
        try {
            $response = Http::withToken($authToken)
                ->post($this->baseUrl . '/ecommerce/orders', [
                    'auth_token' => $authToken,
                    'delivery_needed' => false,
                    'amount_cents' => $this->chargeableAmount($params['amount']),
                    'currency' => $this->getCurrency(),
                    'items' => []
                ]);

            if ($response->successful()) {
                return $response->json()['id'];
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob order registration error: ' . $e->getMessage());
            return null;
        }
    }

    protected function getPaymentKey($authToken, $orderId, $params)
    {
        $apiKeys = $this->getKeys();
        try {
            $response = Http::withToken($authToken)
                ->post($this->baseUrl . '/acceptance/payment_keys', [
                    'auth_token' => $authToken,
                    'amount_cents' => $this->chargeableAmount($params['amount']),
                    'expiration' => 3600,
                    'order_id' => $orderId,
                    'billing_data' => [
                        'email' => $params['email'],
                        'first_name' => $params['name'] ?? '',
                        'last_name' => $params['last_name'] ?? '',
                        'phone_number' => $params['mobile'] ?? '',
                        'country' => 'EG',
                        'apartment' => 'NA',
                        'floor' => 'NA',
                        'street' => 'NA',
                        'building' => 'NA',
                        'shipping_method' => 'NA',
                        'postal_code' => 'NA',
                        'city' => 'NA',
                        'state' => 'NA'
                    ],
                    'currency' => $this->getCurrency(),
                    'integration_id' => $apiKeys['integration_id']
                ]);

            if ($response->successful()) {
                return $response->json()['token'];
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob payment key error: ' . $e->getMessage());
            return null;
        }
    }

    protected function getTransaction($authToken, $orderId)
    {
        try {
            $response = Http::withToken($authToken)
                ->get($this->baseUrl . '/acceptance/transactions/' . $orderId);

            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            Log::error('Paymob transaction verification error: ' . $e->getMessage());
            return null;
        }
    }

    protected function verifyHmacSignature($transaction)
    {
        $apiKeys = $this->getKeys();
        $concatenatedString = $transaction['id'] . $transaction['created_at'] . $transaction['amount_cents'] . $transaction['currency'];
        $calculatedHmac = hash_hmac('sha256', $concatenatedString, $apiKeys['hmac_secret']);
        return hash_equals($calculatedHmac, $transaction['hmac']);
    }

    protected function getIframeUrl($paymentKey)
    {
        $mode = $this->getMode();
        $baseUrl = $mode == 'test' ? 'https://accept.paymob.com/api/acceptance/iframes' : 'https://accept.paymob.com/api/acceptance/iframes';
        return $baseUrl . '/' . $paymentKey;
    }
} 