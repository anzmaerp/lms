<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class Flutterwave extends BasePaymentDriver
{
    use Currency;

    protected $baseUrl = 'https://api.flutterwave.com/v3';

    public function chargeCustomer(array $params)
    {
        $payload = $this->prepareData($params);

        try {
            $request = $this->apiClient()->post("payments", $payload);

            if($request->successful()){
                $response = $request->json();
                if($response['status'] == 'success'){
                    session()->put('flutterwave_tx_ref', $payload['tx_ref']);
                    session()->put('flutterwave_order_id', $params['order_id']);
                    $urlLink = $response['data']['link'];
                    return redirect()->to($urlLink);
                }
            }

            return ['status' => Response::HTTP_BAD_REQUEST,'message' => $request->json()['message']];

        } catch (\Exception $e) {

            return ['status' => Response::HTTP_BAD_REQUEST,'message' => $e->getMessage() ? $e->getMessage() : null ];
        }
    }

    public function driverName() : string{
        return 'flutterwave';
    }

    public function paymentResponse(array $params = [])
    {
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }

        $txReference = session()->get('flutterwave_tx_ref');
        $orderId = session()->get('flutterwave_order_id');
        session()->forget('flutterwave_tx_ref');
        session()->forget('flutterwave_order_id');
        $transactionId = !empty($params['transaction_id']) ? $params['transaction_id'] : "";
        $status = !empty($params['status']) ? $params['status'] : '';

        if( !empty($status) && ( $status == 'cancelled' || empty($transactionId)) ) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Pyament cancelled, please try again.')];
        }

        if( !empty($txReference) && ( $txReference != $params['tx_ref'] ) ) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Transaction reference is invalid')];
        }

        if ( empty($txReference) || empty($orderId) ) {
            return ['status' => Response::HTTP_BAD_REQUEST,'message' => __('Missing Payment reference')];
        }

        $request = $this->apiClient()->get("transactions/{$transactionId}/verify");

        if( $request->successful() ){
            $response = $request->json();
            $paymentStatus = !empty($response['status']) ? $response['status'] : '';
            $paymentId = !empty($response['data']['id']) ? $response['data']['id'] : '';
            if ($paymentStatus === 'success') {
                if (!empty($paymentId)) {
                    return [
                        'status' => Response::HTTP_OK,
                        'data'   => [
                            'transaction_id' => $paymentId,
                            'order_id' => $orderId
                        ]
                    ];
                }
            }
        }

        return ['status' => Response::HTTP_BAD_REQUEST, 'order_id' => $orderId];
    }

    protected function apiClient()
    {
        $apiKeys = $this->getKeys();
        return Http::withToken($apiKeys['secret_key'])->baseUrl($this->baseUrl);
    }

    protected function prepareData($params)
    {
        $uuid = Str::uuid();
        $data = [
            'tx_ref' => $uuid,
            'amount' => $this->chargeableAmount($params['amount']),
            'currency' => $this->getCurrency(),
            'redirect_url' => $params['ipn_url'],
            'customer' => [
                'email' => $params['email'],
                'name' => $params['title']
            ],
            'customizations' => [
                'title' => $params['title'],
            ]
        ];
        return $data;
    }
}
