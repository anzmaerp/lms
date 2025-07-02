<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

class Hesabe extends BasePaymentDriver
{
    use Currency;

    protected $baseUrl;

    public function __construct()
    {
        $this->baseUrl = $this->getBaseUrl();
    }

    protected function getBaseUrl()
    {
        // Always use production URL since we're using production credentials
        return 'https://api.hesabe.com';
    }

    public function chargeCustomer(array $params)
    {
        try {
            $apiKeys = $this->getKeys();
            if (empty($apiKeys['merchant_id']) || empty($apiKeys['access_code']) || empty($apiKeys['secret_key']) || empty($apiKeys['iv_key'])) {
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Missing Hesabe credentials')];
            }

            // Generate a unique order ID
            $orderId = 'HES_' . time() . '_' . Str::random(8);
            
            // Store order ID in session for verification
            session()->put('hesabe_order_id', $orderId);
            session()->put('hesabe_subscription_id', $params['order_id']);

            // Prepare payment data for direct form submission
            $formData = [
                'merchantCode' => $apiKeys['merchant_id'],
                'amount' => $this->chargeableAmount($params['amount']),
                'currency' => $this->getCurrency(),
                'orderReferenceNumber' => $orderId,
                'responseUrl' => $params['ipn_url'],
                'failureUrl' => $params['cancel_url'],
                'payerName' => $params['name'] ?? '',
                'payerPhone' => $params['mobile'] ?? '',
                'payerEmail' => $params['email'] ?? '',
                'lang' => 'en',
                'variable1' => $params['order_id'],  // Store our reference for verification
                'variable2' => '',
                'variable3' => '',
                'variable4' => '',
                'variable5' => '',
                'paymentType' => 0,  // Default to cards
                'version' => '2.0'
            ];

            // Log payment data for debugging
            Log::info('Hesabe payment request data: ' . json_encode($formData));

            // Encrypt data using fixed IV key
            $encryptedData = $this->hesabeEncrypt(json_encode($formData), $apiKeys['secret_key'], $apiKeys['iv_key']);
            if (!$encryptedData) {
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to encrypt payment data')];
            }
            dd( $this->baseUrl . '/payment');
            // Try the direct form submission to /payment endpoint
            return view('larapayease::hesabe', [
                'payment_url' => $this->baseUrl . '/payment',
                'encrypted_data' => $encryptedData,
                'merchant_code' => $apiKeys['merchant_id'],
                'access_code' => $apiKeys['access_code']
            ]);

        } catch (\Exception $e) {
            Log::error('Hesabe payment error: ' . $e->getMessage());
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => $e->getMessage()];
        }
    }

    /**
     * Encrypt data using the fixed IV key from Hesabe
     */
    protected function hesabeEncrypt($data, $secretKey, $ivKey)
    {
        try {
            // Log the inputs for debugging
            Log::info('Encrypting data with secretKey length: ' . strlen($secretKey) . ', ivKey: ' . $ivKey);

            // Create encryption key from secret key
            $encryptionKey = hash('sha256', $secretKey, false);
            $encryptionKey = substr($encryptionKey, 0, 32);
            
            // Use the fixed IV Key - directly from the string, not hex
            $iv = $ivKey;
            
            // Ensure IV is exactly 16 bytes
            if (strlen($iv) > 16) {
                $iv = substr($iv, 0, 16);
            } elseif (strlen($iv) < 16) {
                // Pad with zeros if less than 16 bytes
                $iv = str_pad($iv, 16, "\0");
            }
            
            // Log the IV and key lengths
            Log::info('IV length: ' . strlen($iv) . ', Encryption key length: ' . strlen($encryptionKey));

            // Encrypt the data using the fixed IV
            $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $encryptionKey, 0, $iv);
            if ($encryptedData === false) {
                Log::error('Encryption error: ' . openssl_error_string());
                return false;
            }

            // Return the base64 encoded encrypted data
            return $encryptedData; // Already base64 encoded by openssl_encrypt
        } catch (\Exception $e) {
            Log::error('Encryption error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Decrypt data using the fixed IV key from Hesabe
     */
    protected function hesabeDecrypt($encryptedData, $secretKey, $ivKey)
    {
        try {
            // Log the inputs for debugging
            Log::info('Decrypting data with secretKey length: ' . strlen($secretKey) . ', ivKey: ' . $ivKey);
            
            // Create decryption key from secret key
            $decryptionKey = hash('sha256', $secretKey, false);
            $decryptionKey = substr($decryptionKey, 0, 32);
            
            // Use the fixed IV Key - directly from the string, not hex
            $iv = $ivKey;
            
            // Ensure IV is exactly 16 bytes
            if (strlen($iv) > 16) {
                $iv = substr($iv, 0, 16);
            } elseif (strlen($iv) < 16) {
                // Pad with zeros if less than 16 bytes
                $iv = str_pad($iv, 16, "\0");
            }
            
            // Log the IV and key lengths
            Log::info('IV length: ' . strlen($iv) . ', Decryption key length: ' . strlen($decryptionKey));

            // Decrypt the data
            $decryptedData = openssl_decrypt($encryptedData, 'AES-256-CBC', $decryptionKey, 0, $iv);
            if ($decryptedData === false) {
                Log::error('Decryption error: ' . openssl_error_string());
                return false;
            }
            
            return $decryptedData;
        } catch (\Exception $e) {
            Log::error('Decryption error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Legacy encryption method (kept for reference)
     */
    protected function encryptData($data, $secretKey)
    {
        try {
            $iv = substr(sha1(mt_rand()), 0, 16);
            $password = substr(sha1($secretKey), 0, 32);
            
            $encryptedData = openssl_encrypt($data, 'AES-256-CBC', $password, 0, $iv);
            if ($encryptedData === false) {
                Log::error('OpenSSL encryption error: ' . openssl_error_string());
                return false;
            }

            // Combine IV and encrypted data
            $combinedData = $iv . $encryptedData;
            
            // Base64 encode for transmission
            return base64_encode($combinedData);
        } catch (\Exception $e) {
            Log::error('Encryption error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Legacy decryption method (kept for reference)
     */
    protected function decryptData($encryptedData, $secretKey)
    {
        try {
            // Base64 decode
            $combinedData = base64_decode($encryptedData);
            if ($combinedData === false) {
                return false;
            }
            
            // Extract IV (first 16 bytes) and encrypted data
            $iv = substr($combinedData, 0, 16);
            $encryptedText = substr($combinedData, 16);
            $password = substr(sha1($secretKey), 0, 32);
            
            // Decrypt
            $decryptedData = openssl_decrypt($encryptedText, 'AES-256-CBC', $password, 0, $iv);
            if ($decryptedData === false) {
                Log::error('OpenSSL decryption error: ' . openssl_error_string());
                return false;
            }
            
            return $decryptedData;
        } catch (\Exception $e) {
            Log::error('Decryption error: ' . $e->getMessage());
            return false;
        }
    }

    public function driverName(): string
    {
        return 'hesabe';
    }

    public function paymentResponse(array $params = [])
    {
        // Log the response data
        Log::info('Hesabe payment response received: ' . json_encode($params));
        
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }

        $orderId = session()->get('hesabe_order_id');
        $subscriptionId = session()->get('hesabe_subscription_id');
        session()->forget('hesabe_order_id');
        session()->forget('hesabe_subscription_id');

        if (empty($orderId) || empty($subscriptionId)) {
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Missing order information')];
        }

        // Check for data parameter in the response
        if (!empty($params['data'])) {
            try {
                $apiKeys = $this->getKeys();
                
                // Decrypt the response data using fixed IV
                $decryptedData = $this->hesabeDecrypt($params['data'], $apiKeys['secret_key'], $apiKeys['iv_key']);
                if (!$decryptedData) {
                    return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to decrypt response data')];
                }
                
                // Parse the JSON response
                $responseData = json_decode($decryptedData, true);
                Log::info('Hesabe decrypted response: ' . json_encode($responseData));
                
                // Check if the response contains valid data
                if (empty($responseData) || !isset($responseData['status'])) {
                    return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Invalid response format from Hesabe')];
                }
                
                // Check if transaction was successful based on Hesabe documentation
                $success = false;
                
                // Per documentation: status is boolean true for success
                if ($responseData['status'] === true && isset($responseData['code']) && $responseData['code'] === 1) {
                    $success = true;
                }
                
                // Additional check for result code if response contains that data
                if (isset($responseData['response']) && isset($responseData['response']['data'])) {
                    $resultData = $responseData['response']['data'];
                    $resultCode = $resultData['resultCode'] ?? '';
                    
                    // According to docs, these codes indicate success
                    if (in_array($resultCode, ['CAPTURED', 'ACCEPT', 'SUCCESS'])) {
                        $success = true;
                    }
                    
                    if ($success) {
                        // Get transaction details from the response
                        $transactionId = $resultData['paymentId'] ?? '';
                        $variable1 = $resultData['variable1'] ?? '';
                        
                        // If variable1 was set to our order_id, use it
                        if (!empty($variable1)) {
                            $subscriptionId = $variable1;
                        }
                        
                        return [
                            'status' => Response::HTTP_OK,
                            'data' => [
                                'transaction_id' => $transactionId ?: $orderId,
                                'order_id' => $subscriptionId
                            ]
                        ];
                    }
                }
                
                // Payment failed
                $errorCode = $responseData['code'] ?? 0;
                $errorMessage = $responseData['message'] ?? __('Payment was not successful');
                
                return [
                    'status' => Response::HTTP_BAD_REQUEST, 
                    'order_id' => $subscriptionId, 
                    'message' => $errorMessage . ' (Code: ' . $errorCode . ')'
                ];
                
            } catch (\Exception $e) {
                Log::error('Error processing Hesabe response: ' . $e->getMessage());
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Error processing payment response: ') . $e->getMessage()];
            }
        }

        return ['status' => Response::HTTP_BAD_REQUEST, 'order_id' => $subscriptionId, 'message' => __('Payment was not successful')];
    }

    public function getKeys(): array
    {
        // Production credentials
        $keys = [
            'merchant_id' => '109142025',
            'access_code' => '0dce0280-727e-446c-9247-21b2d082df7c',
            'secret_key' => 'nDreP2JpOqEkaEN0zm7o0x9vXdWAGyL3',
            'iv_key' => 'OqEkaEN0zm7o0x9v' // Fixed IV key from Hesabe
        ];
        return $keys;
    }

    // Override parent method to always return 'live' mode
    public function getMode(): string
    {
        return 'live';
    }

    /**
     * Alternative payment method using the checkout endpoint first
     */
    public function useCheckoutApi(array $params)
    {
        try {
            $apiKeys = $this->getKeys();
            if (empty($apiKeys['merchant_id']) || empty($apiKeys['access_code']) || empty($apiKeys['secret_key']) || empty($apiKeys['iv_key'])) {
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Missing Hesabe credentials')];
            }

            // Generate a unique order ID
            $orderId = 'HES_' . time() . '_' . Str::random(8);
            
            // Store order ID in session for verification
            session()->put('hesabe_order_id', $orderId);
            session()->put('hesabe_subscription_id', $params['order_id']);

            // Prepare payment data
            $requestData = [
                'merchantCode' => $apiKeys['merchant_id'],
                'amount' => $this->chargeableAmount($params['amount']),
                'currency' => $this->getCurrency(),
                'orderReferenceNumber' => $orderId,
                'responseUrl' => $params['ipn_url'],
                'failureUrl' => $params['cancel_url'],
                'payerName' => $params['name'] ?? '',
                'payerPhone' => $params['mobile'] ?? '',
                'payerEmail' => $params['email'] ?? '',
                'lang' => 'en',
                'variable1' => $params['order_id'],  // Store our reference for verification
                'variable2' => '',
                'variable3' => '',
                'variable4' => '',
                'variable5' => '',
                'paymentType' => 0,  // Default to cards
                'version' => '2.0'
            ];

            // Log payment data for debugging
            Log::info('Hesabe payment request data: ' . json_encode($requestData));

            // Encrypt data using fixed IV key
            $encryptedData = $this->hesabeEncrypt(json_encode($requestData), $apiKeys['secret_key'], $apiKeys['iv_key']);
            if (!$encryptedData) {
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Failed to encrypt payment data')];
            }

            // Send request to checkout endpoint
            $response = Http::withHeaders([
                'accessCode' => $apiKeys['access_code'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/checkout', [
                'data' => $encryptedData
            ]);

            // Log response for debugging
            Log::info('Hesabe checkout response: ' . $response->body());
            Log::info('Hesabe checkout status: ' . $response->status());

            if (!$response->successful()) {
                Log::error('Hesabe checkout failed: ' . $response->body());
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('Checkout failed: ') . $response->body()];
            }

            // Parse response
            $responseData = $response->json();
            Log::info('Hesabe checkout response JSON: ' . json_encode($responseData));

            // Look for token in various response formats
            $token = null;
            if (isset($responseData['data'])) {
                $token = $responseData['data'];
            } elseif (isset($responseData['response']) && isset($responseData['response']['data'])) {
                $token = $responseData['response']['data'];
            } elseif (isset($responseData['token'])) {
                $token = $responseData['token'];
            }

            if (!$token) {
                Log::error('No token found in response: ' . json_encode($responseData));
                return ['status' => Response::HTTP_BAD_REQUEST, 'message' => __('No payment token received')];
            }

            // Redirect to payment URL with token
            $paymentUrl = $this->baseUrl . '/payment?data=' . urlencode($token);
            Log::info('Redirecting to: ' . $paymentUrl);
            return redirect()->away($paymentUrl);

        } catch (\Exception $e) {
            Log::error('Hesabe checkout error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return ['status' => Response::HTTP_BAD_REQUEST, 'message' => $e->getMessage()];
        }
    }
} 