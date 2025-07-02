<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class Paypal extends BasePaymentDriver
{
    use Currency;
    protected $clientId = '';
    protected $secretId = '';

    public function getBaseUrl()
    {
        $mode = $this->getMode();
        return $mode == 'test' ? 'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
    }

    public function chargeCustomer(array $params)
    {
        $order = $this->createOrder($params);

        if(!empty($order['status']) && $order['status'] == 'CREATED'){
            session()->put('subscription_id', $params['order_id']);
            session()->put('paypal_order_id', $order['id']);
            $approvedLink = array_filter($order['links'], fn($link) => $link['rel'] === 'approve');
            $approvedHref = $approvedLink ? array_column($approvedLink, 'href')[0] : null;
            if(!empty($approvedHref)){
                return redirect()->to($approvedHref);
            }
        } else {
            return $order;
        }
    }

    private function generateAccessToken()
    {
        $baseUrl  = $this->getBaseUrl();

        $apiKeys = $this->getKeys();
        try {
            if (empty($apiKeys['client_id']) || empty($apiKeys['secret_id'])) {
                throw new \Exception("MISSING_API_CREDENTIALS");
            }

            $response = Http::baseUrl("$baseUrl/v1/")
            ->withBasicAuth($apiKeys['client_id'], $apiKeys['secret_id'])
            ->asForm()->post("oauth2/token", [ 'grant_type' => 'client_credentials']);

            return $response->json()['access_token'];
        } catch (\Exception $e) {
            Log::error('Failed to generate Access Token: ' . $e->getMessage());
            return null;
        }
    }

    public function createOrder($params)
    {
        try {
            $payload = $this->prepareData($params);
            $response = $this->apiClient()->post("checkout/orders", $payload);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to create order: ' . $e->getMessage());
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => $e->getMessage() ];
        }
    }

    public function captureOrder($orderID)
    {
        try {
            $url = "checkout/orders/{$orderID}/capture";
            return $this->apiClient()->post($url, ['intent' => 'CAPTURE' ]);
        } catch (\Exception $e) {
            Log::error('Failed to capture order: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to capture order.'], 500);
        }
    }

    public function driverName() : string{
        return 'paypal';
    }

    public function paymentResponse(array $params = [])
    {
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }

        $orderId = session()->get('paypal_order_id');
        $subscriptionId = session()->get('subscription_id');
        session()->forget('paypal_order_id');
        session()->forget('subscription_id');
        $token = $params['token'] ?? '';

        if( empty($orderId) || empty($token) || ( $orderId != $token ) ) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Order ID is invalid')];
        }

        $request = $this->captureOrder($orderId);

        if( $request->successful() ){
            $response = $request->json();
            $paymentStatus = !empty($response['status']) ? $response['status'] : '';
            $paymentId = !empty($response['id']) ? $response['id'] : '';
            if ($paymentStatus === 'COMPLETED') {
                if (!empty($paymentId)) {
                    return [
                        'status' => Response::HTTP_OK,
                        'data'   => [
                            'transaction_id' => $paymentId,
                            'order_id' => $subscriptionId
                        ]
                    ];
                }
            }
        }

        return ['status' => Response::HTTP_BAD_REQUEST, 'order_id' => $subscriptionId];
    }

    protected function apiClient()
    {
        $accessToken = $this->generateAccessToken();
        $baseUrl  = $this->getBaseUrl();
        return Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->withToken($accessToken)->baseUrl("$baseUrl/v2/");
    }

    protected function prepareData($params)
    {
        $data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => $this->getCurrency(),
                        'value' => $this->chargeableAmount($params['amount']),
                    ]
                ]
            ],
            "application_context" => [
                "return_url" => $params['ipn_url'],
                "cancel_url" => $params['cancel_url']
            ]
        ];
        return $data;
    }
}
