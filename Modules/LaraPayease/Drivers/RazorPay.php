<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Modules\LaraPayease\Utils\CurrencyUtil;
use Exception;
use Razorpay\Api\Api;
use Symfony\Component\HttpFoundation\Response;

class RazorPay extends BasePaymentDriver
{
    use Currency;
    public function chargeCustomer(array $params)
    {
        $keys = $this->getKeys();
        $currency = $this->getCurrency();

        try {
            $api = new Api($keys['public_key'], $keys['secret_key']);
            $orderData  = $api->order->create([
                'receipt'   => (string) $params['order_id'],
                'amount'    => $this->chargeableAmount($params['amount']),
                'currency'  => $currency
            ]);

            if (!empty($orderData['status']) && $orderData['status'] == 'created') {
                $data['currency']           = $currency;
                $data['price']              = $this->chargeableAmount($params['amount']);
                $data['title']              = $params['title'];
                $data['description']        = $params['description'];
                $data['route']              = $params['ipn_url'];
                $data['order_id']           = $orderData['id'];
                $data['subscription_id']    = $params['order_id'];
                $data['name']               = $params['title'];
                $data['email']              = $params['email'];
                $data['public_key']         = $keys['public_key'];
                $data['cancel_url']         = $params['cancel_url'];

                session()->put('razorpay_order_id',$orderData['id']);

                return view('larapayease::razorpay',compact('data'));
            }
        } catch (Exception $e) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => $e->getMessage()];
        }
    }

    public function driverName() : string{
        return 'razorpay';
    }

    public function paymentResponse(array $params = [])
    {
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }

        $apiKeys = $this->getKeys();

        if (empty($params['order_id']) || empty($params['razorpay_payment_id']) || empty($params['razorpay_signature'])) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Missing Params')];
        }

        try {
            $api = new Api($apiKeys['public_key'], $apiKeys['secret_key']);
            $attributes = [
                'razorpay_order_id'     => $params['order_id'],
                'razorpay_payment_id'   => $params['razorpay_payment_id'],
                'razorpay_signature'    => $params['razorpay_signature']
            ];
            $api->utility->verifyPaymentSignature($attributes);
            return [
                'status' => Response::HTTP_OK,
                'data'   => [
                    'transaction_id' => $params['razorpay_payment_id'],
                    'order_id'       => $params['subscription_id'],
                ]
            ];
        } catch (Exception $e) {
            return [ 'status' => Response::HTTP_BAD_REQUEST, 'message'=> $e->getMessage(), 'order_id' => $params['order_id']];
        }
    }
}
