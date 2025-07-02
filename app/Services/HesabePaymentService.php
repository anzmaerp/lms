<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

class HesabePaymentService
{
    protected $merchantCode;
    protected $accessCode;
    protected $secretKey;
    protected $ivKey;
    protected $baseUrl;
    protected $isProduction;

    public function __construct()
    {
        $paymentMethods = DB::table("optionbuilder__settings as os")->where('key', 'payment_method')->select('value')->first();
        $info = unserialize($paymentMethods->value)['hesabe'];
        // Load Hesabe configuration from settings or use the provided values
        $this->merchantCode = $info['merchant_id'] ?? '842217';
        $this->accessCode = $info['access_code'] ?? 'c333729b-d060-4b74-a49d-7686a8353481';
        $this->secretKey = $info['secret_key'] ?? 'PkW64zMe5NVdrlPVNnjo2Jy9nOb7v1Xg';
        $this->ivKey = $info['iv_key'] ?? '5NVdrlPVNnjo2Jy9';
        $this->isProduction = empty($info['enable_test_mode']);

        // Set the base URL based on the environment
        $this->baseUrl = $this->isProduction
            ? 'https://api.hesabe.com'
            : 'https://sandbox.hesabe.com';
    }

    /**
     * Create a payment request to Hesabe
     *
     * @param array $orderData
     * @return array
     */
    public function createPayment($orderData)
    {
        try {
            $payload = $this->preparePayload($orderData);
            $payload['name'] = $orderData['name'];
            $payload['mobile_number'] = $orderData['mobile'];

            //$encryptedData = $this->encryptData(json_encode($payload));
            $encryptedData = HesabeEncryptService::encrypt(json_encode($payload), $this->secretKey, $this->ivKey);

            //dd($payload, $orderData, $encryptedData);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                "accessCode" => $this->accessCode,
            ])->post($this->baseUrl . '/checkout', [
                        'data' => $encryptedData,
                    ]);

            if ($response->successful()) {
                $responseData = $response->body();
                $decryptedData = HesabeEncryptService::decrypt($responseData, $this->secretKey, $this->ivKey);
                $paymentData = json_decode($decryptedData);
                return [
                    'success' => true,
                    'data' => $paymentData,
                    'payment_url' => $this->baseUrl . '/payment/?data=' . $paymentData->response->data ?? null,
                ];
            }

            Log::error('Hesabe payment error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Failed to communicate with Hesabe payment gateway',
            ];
        } catch (\Exception $e) {
            Log::error('Hesabe payment exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while processing the payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check the payment status from Hesabe
     *
     * @param string $paymentToken
     * @return array
     */
    public function checkPaymentStatus($paymentToken)
    {
        try {
            $data = HesabeEncryptService::decrypt($paymentToken, $this->secretKey, $this->ivKey);
            $decodedData = json_decode($data);
            $order_id = $decodedData->response->orderReferenceNumber;
            DB::table('orders')->where('id', $order_id)->update(['hesabe_status' => $data]);
            /*$response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'accessCode' => $this->accessCode,
            ])->get($this->baseUrl . '/payment-status', [
                        'token' => $decodedData->response->paymentToken,
                    ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $decryptedData = $this->decryptData($responseData['data'] ?? '');
                $paymentData = json_decode($decryptedData, true);

                return [
                    'success' => true,
                    'data' => $paymentData,
                    'status' => $paymentData['response']['status'] ?? null,
                ];
            }

            Log::error('Hesabe payment status error: ' . $response->body());
            return [
                'success' => false,
                'message' => 'Failed to check payment status',
            ];*/
            return [
                'success' => true,
                'data' => $decodedData,
                'status' => $decodedData->response->resultCode ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Hesabe payment status exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while checking payment status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare payload for Hesabe payment
     *
     * @param array $orderData
     * 
     * 
     * 
     * @return array
     */
    protected function preparePayload($orderData)
    {
        return [
            'merchantCode' => $this->merchantCode,
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'] ?? 'KWD',
            'responseUrl' => route('payment.hesabe.callback'),
            'failureUrl' => route('payment.hesabe.callback'),
            'orderReferenceNumber' => $orderData['order_id'],
            'variable1' => $orderData['order_id'],
            'variable2' => $orderData['track'] ?? '',
            'variable3' => $orderData['user_id'] ?? '',
            'variable4' => '',
            'variable5' => '',
            'paymentType' => $orderData['payment_type'] ?? 0, // 0 for any payment
            'version' => '2.0',
        ];
    }

    /**
     * Encrypt data using AES CBC PKCS7 padding
     *
     * @param string $data
     * @return string
     */
    protected function encryptData($data)
    {
        $key = $this->secretKey;
        $iv = $this->ivKey;

        $encryptedData = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        return base64_encode($encryptedData);
    }

    /**
     * Decrypt data using AES CBC PKCS7 padding
     *
     * @param string $encryptedData
     * @return string
     */
    protected function decryptData($encryptedData)
    {
        $key = $this->secretKey;
        $iv = $this->ivKey;

        $decodedData = base64_decode($encryptedData);

        return openssl_decrypt(
            $decodedData,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    public function chargeCustomer($paymentData)
    {
        try {
            $response = $this->createPayment($paymentData);

            if (!$response['success']) {
                return [
                    'message' => $response['message'] ?? 'Payment processing failed'
                ];
            }

            if (empty($response['payment_url'])) {
                return [
                    'message' => 'Invalid payment gateway response'
                ];
            }

            // Store payment token for later verification
            $order = \App\Models\Order::find($paymentData['order_id']);
            if ($order) {
                $order->update([
                    'transaction_id' => $response['data']['response']['paymentToken'] ?? null,
                ]);
            }

            // Redirect to Hesabe payment page
            return redirect($response['payment_url']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Hesabe payment exception: ' . $e->getMessage());
            return [
                'message' => 'An error occurred during payment processing: ' . $e->getMessage()
            ];
        }
    }

    public function paymentResponse($responseData)
    {
        try {
            $paymentToken = $responseData['paymentToken'] ?? null;

            if (empty($paymentToken)) {
                return [
                    'status' => 400,
                    'message' => 'Invalid payment response'
                ];
            }

            $response = $this->checkPaymentStatus($paymentToken);

            if (!$response['success']) {
                return [
                    'status' => 400,
                    'message' => $response['message'] ?? 'Payment verification failed'
                ];
            }

            $paymentData = $response['data'] ?? [];
            $status = $paymentData['response']['status'] ?? null;
            $orderId = $paymentData['response']['variable1'] ?? null;
            $transactionId = $paymentData['response']['paymentId'] ?? $paymentToken;

            if (empty($orderId)) {
                return [
                    'status' => 400,
                    'message' => 'Order ID not found in payment response'
                ];
            }

            // Check if payment was successful (status 1 = success)
            if ($status == 1) {
                return [
                    'status' => 200,
                    'data' => [
                        'order_id' => $orderId,
                        'transaction_id' => $transactionId
                    ],
                    'message' => 'Payment successful'
                ];
            } else {
                return [
                    'status' => 400,
                    'message' => 'Payment was not completed'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Hesabe payment response exception: ' . $e->getMessage());
            return [
                'status' => 500,
                'message' => 'An error occurred during payment processing'
            ];
        }
    }
}