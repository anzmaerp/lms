<?php

namespace Modules\LaraPayease\Drivers;

use Modules\LaraPayease\BasePaymentDriver;
use Modules\LaraPayease\Traits\Currency;
use Exception;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PayFast extends BasePaymentDriver
{

    use Currency;

    public function chargeCustomer(array $params){
        try{
            $settings = $this->getKeys();
            $data = array(
                // Merchant details
                'merchant_id'  => $settings['merchant_id'],
                'merchant_key' => $settings['merchant_key'],
                'return_url'   => $params['ipn_url'],
                'cancel_url'   => $params['cancel_url'],
                'notify_url'   => $settings['webhook_url'],
                // Buyer details
                "name_first" => $params['name'] ?? '',
                "name_last" => $params['last_name'] ?? '',
                'email_address'=> $params['email'],
                // Transaction details
                'm_payment_id' => Str::uuid()->toString(),
                'amount' => $this->chargeableAmount($params['amount']),
                'item_name' => $params['title'] ?? '',
                'item_description' => $params['description'] ?? '',
                'custom_str1'  => $params['order_id']
            );

            $signature = $this->generateSignature($data, $settings['pass_phrase']);
            $data['signature'] = $signature;

            return view('larapayease::payfast', ['payfast_data' => array_merge($data), 'mode' => $this->getMode()]);
        } catch (Exception $ex) {
            return ['status' => Response::HTTP_BAD_REQUEST,'message' => $ex->getMessage()];
        }
    }

    function generateSignature($data, $passPhrase = null) {
        $pfOutput = '';
        foreach( $data as $key => $val ) {
            if($val !== '') {
                $pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';
            }
        }
        $getString = substr( $pfOutput, 0, -1 );
        if( $passPhrase !== null ) {
            $getString .= '&passphrase='. urlencode( trim( $passPhrase ) );
        }
        return md5( $getString );
    }

    public function driverName() : string{
        return 'payfast';
    }

    protected function getHost() {
        return $this->getMode() == 'test' ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
    }

    public function paymentResponse(array $params = [])
    {
        if (array_key_exists('payment_method', $params)) {
            unset($params['payment_method']);
        }

        $settings = $this->getKeys();
        $pfParamString = '';
        // Strip any slashes in data
        $pfData = $params;
        foreach( $params as $key => $val ) {
            $pfData[$key] = stripslashes( $val );
        }

        // Convert posted variables to a string
        foreach( $pfData as $key => $val ) {
            if( $key !== 'signature' ) {
                $pfParamString .= $key .'='. urlencode( $val ) .'&';
            } else {
                break;
            }
        }

        $pfParamString = substr( $pfParamString, 0, -1 );

        $check1 = $this->pfValidSignature($params, $pfParamString, $settings['pass_phrase']);
        $check2 = $this->pfValidIP();
        $check3 = $this->pfValidPaymentData($params['amount_gross'], $params);
        $check4 = $this->pfValidServerConfirmation($pfParamString, $this->getHost());

        if($check1 && $check2 && $check3 && $check4) {
            return [
                    'status' => Response::HTTP_OK,
                    'data'   => [
                        'transaction_id' => $params['pf_payment_id'],
                        'order_id'       => $params['custom_str1']
                    ]
                ];
        } else {
            return ['status' => Response::HTTP_BAD_REQUEST,'order_id' => $params['custom_str1']];
        }

    }

    protected function pfValidSignature( $pfData, $pfParamString, $pfPassphrase = null ) {
        // Calculate security signature
        if($pfPassphrase === null) {
            $tempParamString = $pfParamString;
        } else {
            $tempParamString = $pfParamString.'&passphrase='.urlencode( $pfPassphrase );
        }

        $signature = md5( $tempParamString );
        return ( $pfData['signature'] === $signature );
    }

    protected function pfValidPaymentData( $cartTotal, $pfData ) {
        return !(abs((float)$cartTotal - (float)$pfData['amount_gross']) > 0.01);
    }

    protected function pfValidServerConfirmation( $pfParamString, $pfHost = 'sandbox.payfast.co.za', $pfProxy = null ) {
        // Use cURL (if available)
        if( in_array( 'curl', get_loaded_extensions(), true ) ) {
            // Variable initialization
            $url = 'https://'. $pfHost .'/eng/query/validate';

            // Create default cURL object
            $ch = curl_init();

            // Set cURL options - Use curl_setopt for greater PHP compatibility
            // Base settings
            curl_setopt( $ch, CURLOPT_USERAGENT, NULL );  // Set user agent
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );      // Return output as string rather than outputting it
            curl_setopt( $ch, CURLOPT_HEADER, false );             // Don't include header in output
            curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );

            // Standard settings
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $pfParamString );
            if( !empty( $pfProxy ) )
                curl_setopt( $ch, CURLOPT_PROXY, $pfProxy );

            // Execute cURL
            $response = curl_exec( $ch );
            curl_close( $ch );
            if ($response === 'VALID') {
                return true;
            }
        }
        return false;
    }

    protected function pfValidIP() {
        // Variable initialization
        $validHosts = array(
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
            );

        $validIps = [];

        foreach( $validHosts as $pfHostname ) {
            $ips = gethostbynamel( $pfHostname );

            if( $ips !== false )
                $validIps = array_merge( $validIps, $ips );
        }

        // Remove duplicates
        $validIps = array_unique( $validIps );
        $referrerIp = gethostbyname(parse_url($_SERVER['HTTP_REFERER'])['host']);
        if( in_array( $referrerIp, $validIps, true ) ) {
            return true;
        }
        return false;
    }
}
