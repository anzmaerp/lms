<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Modules\LaraPayease\Utils\CurrencyUtil;
use Modules\LaraPayease\Utils\PaytmChecksum;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class Paytm extends BasePaymentDriver
{
    use Currency;

    protected function baseUrl()
    {
        $mode = $this->getMode();
        return $mode == 'test' ? 'https://securegw-stage.paytm.in' : 'https://securegw.paytm.in';
    }

    protected function prepareData($params) {
        $apiKeys = $this->getKeys();
        $orderId = "PYTM_BLINK_".time();
        $checksumParams = [
            "requestType"   => "Payment",
            "mid"           => $apiKeys['app_id'],
            "orderId"       => $orderId,
            "websiteName"   => $apiKeys['website'],
            "txnAmount" => [
                "value"     => $this->chargeableAmount($params['amount']),
                "currency"  => $this->getCurrency(),
            ],
            "userInfo" => [
                "custId"    => Str::uuid()->toString(),
                "mobile"    => $params['mobile'] ?? '',
                "email"     => $params['email'] ?? '',
            ],
            "callbackUrl" => $params['ipn_url']
        ];

        $paytmData = [
            "body" => $checksumParams,
            "head" => [
                "signature" => PaytmChecksum::generateSignature(json_encode($checksumParams, JSON_UNESCAPED_SLASHES), $apiKeys['app_key'])
            ]
        ];
        session()->put('subscription_id',$params['order_id']);
        return $paytmData;
    }

    protected function getcURLRequest($url , $postData = array(), $headers = array("Content-Type: application/json")){

		$post_data_string = json_encode($postData, JSON_UNESCAPED_SLASHES);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
		$response = curl_exec($ch);
		return json_decode($response,true);
	}

    public function chargeCustomer(array $params)
    {
        $apiKeys = $this->getKeys();

        $paytmData = $this->prepareData($params);
        $url = $this->baseUrl()."/theia/api/v1/initiateTransaction?mid=".$apiKeys['app_id']."&orderId=".$paytmData['body']['orderId'];
        $getcURLResponse = $this->getcURLRequest($url, $paytmData);
        if(empty($getcURLResponse)) {
            return ['status' => Response::HTTP_BAD_REQUEST,'message' => __('There is an issue with integrating the Paytm payment method. Please contact the support team for assistance.')];
        }

        if(!empty($getcURLResponse) && strtolower($getcURLResponse['body']['resultInfo']['resultMsg']) == 'success') {
            $result = $this->handleResponse($getcURLResponse , $paytmData);
            return view('larapayease::paytm', ['paytm_data' => $result, 'mode' => $this->getMode()]);
        } else {
            return ['status' => Response::HTTP_BAD_REQUEST,'message' => __('There is an issue with integrating the Paytm payment method. Please contact the support team for assistance.')];
        }
    }

    protected function handleResponse($getcURLResponse, $paytmData) {
        $apiKeys = $this->getKeys();
        if(!empty($getcURLResponse['body']['resultInfo']['resultStatus']) && $getcURLResponse['body']['resultInfo']['resultStatus'] == 'S'){
			$result = array('success' => true, 'orderId' => $paytmData['body']['orderId'], 'txnToken' => $getcURLResponse['body']['txnToken'], 'amount' => $paytmData['body']['txnAmount']['value'], 'message' => $getcURLResponse['body']['resultInfo']['resultMsg']);
		} else {
			$result = array('success' => false, 'orderId' => $paytmData['body']['orderId'], 'txnToken' => '', 'amount' => $paytmData['body']['txnAmount']['value'], 'message' => $getcURLResponse['body']['resultInfo']['resultMsg']);
		}

        $result['app_id'] = $apiKeys['app_id'];
        return $result;
    }



    public function driverName() : string{
        return 'paytm';
    }

    public function paymentResponse(array $params = [])
    {
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }
        $orderId = session()->get('subscription_id');
        session()->forget('subscription_id');

        if (empty($params['CHECKSUMHASH'])) {
            return ['status' => Response::HTTP_BAD_REQUEST,'message' => __('Missing CHECKSUMHASH')];
        }
        $checksum = (!empty($params['CHECKSUMHASH'])) ? $params['CHECKSUMHASH'] : '';
        unset($params['CHECKSUMHASH']);
        $verified = PaytmChecksum::verifySignature($params, $this->getKeys()['app_key'], $checksum);
        if($verified) {
            $res = $this->transactionStatus($params);
            if(!empty($res)) {
                if($res['body']['resultInfo']['resultStatus'] == "TXN_SUCCESS") {
                    return [
                        'status' => Response::HTTP_OK,
                        'data'   => [
                            'transaction_id' => $params['TXNID'],
                            'order_id' => $orderId
                        ]
                    ];
                } else {
                    return [
                        'status' => Response::HTTP_BAD_REQUEST,
                        'data'   => [
                            'order_id' => $orderId
                        ]
                    ];
                }
            }
        }

        return ['status' => Response::HTTP_BAD_REQUEST,'order_id' => $orderId];
    }

    protected function transactionStatus($params) {
        $apiKeys = $this->getKeys();
		$paytmParams = array();
		$paytmParams["body"] = array(
			"mid" => $apiKeys['app_id'],
			"orderId" => $params['ORDERID'],
		);
		$checksum = PaytmChecksum::generateSignature(json_encode($paytmParams["body"], JSON_UNESCAPED_SLASHES), $apiKeys['app_key']);

		$paytmParams["head"] = array(
			"signature"	=> $checksum
		);

		$url = $this->baseUrl()."/v3/order/status";

        $getcURLResponse = $this->getcURLRequest($url, $paytmParams);
		return $getcURLResponse;
	}

}
