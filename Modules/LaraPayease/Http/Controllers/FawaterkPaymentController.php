<?php

namespace Modules\LaraPayease\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\LaraPayease\Drivers\fawaterk;
use Modules\LaraPayease\Traits\Currency;
use Symfony\Component\HttpFoundation\Response;

class FawaterkPaymentController extends Controller
{
    use Currency;

    protected $driver;

    public function __construct()
    {
        $this->driver = new fawaterk();
    }

    public function prepareCharge(Request $request)
    {
        $params = $request->all();
        $response = $this->driver->chargeCustomer($params);

        if (isset($response['status']) && $response['status'] === Response::HTTP_BAD_REQUEST) {
            return redirect()->back()->with('error', $response['message'] ?? __('settings.fawaterk_missing_keys'));
        }

        if (isset($response['redirect_url'])) {
            return redirect()->away($response['redirect_url']);
        }

        return redirect()->back()->with('error', __('settings.fawaterk_invalid_response'));
    }

    public function handleCallback(Request $request)
    {
        $params = $request->all();
        $response = $this->driver->paymentResponse($params);

        if ($response['status'] === Response::HTTP_OK) {
            return redirect()->route('settings.success', [
                'transaction_id' => $response['data']['transaction_id'],
                'order_id' => $response['data']['order_id']
            ]);
        }

        return redirect()->route('settings.failed', [
            'order_id' => $response['data']['order_id'] ?? null,
            'message' => $response['message'] ?? __('settings.fawaterk_invalid_response')
        ]);
    }
}
