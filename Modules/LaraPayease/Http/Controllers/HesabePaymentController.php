<?php

namespace Modules\LaraPayease\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LaraPayease\Drivers\Hesabe;
use Modules\LaraPayease\Traits\Currency;
use Symfony\Component\HttpFoundation\Response;

class HesabePaymentController extends Controller
{
    use Currency;

    protected $driver;

    public function __construct()
    {
        $this->driver = new Hesabe();
    }

    public function prepareCharge(Request $request)
    {
        dd(2);
        $params = $request->all();
        $response = $this->driver->chargeCustomer($params);
        
        if (isset($response['status']) && $response['status'] === Response::HTTP_BAD_REQUEST) {
            return redirect()->back()->with('error', $response['message']);
        }

        return $response;
    }

    public function handleCallback(Request $request)
    {
        $params = $request->all();
        $response = $this->driver->paymentResponse($params);

        if ($response['status'] === Response::HTTP_OK) {
            return redirect()->route('payment.success', [
                'transaction_id' => $response['data']['transaction_id'],
                'order_id' => $response['data']['order_id']
            ]);
        }

        return redirect()->route('payment.failed', [
            'order_id' => $response['order_id'],
            'message' => $response['message'] ?? __('Payment failed')
        ]);
    }
} 