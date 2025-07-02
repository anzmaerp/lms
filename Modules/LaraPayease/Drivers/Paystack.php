<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class Paystack extends BasePaymentDriver
{
    use Currency;
    protected $baseUrl = 'https://api.paystack.co';

    public function chargeCustomer(array $params)
    {
        $data = $this->prepareData($params);
        $request = $this->apiClient()->post('transaction/initialize', $data);
        if($request->successful()){
            $response = $request->json();
            $redirect_url = $response['data']['authorization_url'] ?? '';
            $reference = $response['data']['reference'] ?? '';
            session()->put('paystack_reference', $reference);
            session()->put('paystack_order_id', $params['order_id']);

            return redirect($redirect_url);
        }

        return ['status' => Response::HTTP_BAD_REQUEST,'message' => $request->json()['message']];

    }

    public function driverName() : string{
        return 'paystack';
    }

    public function paymentResponse(array $params = [])
    {
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }

        $referenceId = session()->get('paystack_reference');
        $orderId = session()->get('paystack_order_id');
        session()->forget('paystack_reference');
        session()->forget('paystack_order_id');

        if (empty($referenceId)) {
            return ['status' => Response::HTTP_BAD_REQUEST,'message' => __('Missing Payment reference')];
        }

        $request = $this->apiClient()->get("transaction/verify/$referenceId");

        if($request->successful()){
            $response = $request->json();
            $paymentStatus = !empty($response['data']['status']) ? $response['data']['status'] : '';
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

        return ['status' => Response::HTTP_BAD_REQUEST,'order_id' => $orderId];
    }

    protected function apiClient() {
        $apiKeys = $this->getKeys();
        $request = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ])->withToken($apiKeys['secret_key'])->baseUrl($this->baseUrl);
        return $request;
    }

    protected function prepareData($params) {
        $data = [
            "amount" => $this->chargeableAmount($params['amount']),
            "reference" => Str::uuid(),
            "email" => $params['email'],
            "channels" =>  ['card', 'bank', 'ussd', 'qr', 'mobile_money', 'bank_transfer'],
            "first_name" => $params['name'] ?? '',
            "last_name" => $params['last_name'] ?? '',
            "callback_url" => $params['ipn_url'],
            "currency" => $this->getCurrency(),
        ];
        return $data;
    }
}
