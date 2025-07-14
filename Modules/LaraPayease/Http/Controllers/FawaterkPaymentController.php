<?php

namespace Modules\LaraPayease\Http\Controllers;

use App\Facades\Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\LaraPayease\Traits\Currency;
use Modules\LaraPayease\Drivers\Fawaterk;
use Symfony\Component\HttpFoundation\Response;

class FawaterkPaymentController extends Controller
{
    use Currency;

    protected $driver;

    public function __construct()
    {
        $this->driver = new Fawaterk();
    }

    public function prepareCharge(Request $request)
    {
        $params = session('payment_data');

        if (!$params) {
            return redirect()->route('checkout')->with('error', 'لا يوجد بيانات دفع محفوظة.');
        }

        $response = $this->driver->chargeCustomer($params);

        if (isset($response['status']) && $response['status'] === Response::HTTP_BAD_REQUEST) {
            return redirect()->back()->with('error', $response['message'] ?? __('settings.fawaterk_missing_keys'));
        }

        if (isset($response['redirect_url'])) {
            return view('larapayease::iframe', [
                'payment_url' => $response['redirect_url'],
            ]);
        }

        return redirect()->route('checkout')->with('error', 'فشل في الاتصال بفواتيرك');
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


    public function showIframe($order)
    {
        $cartItems = collect(Cart::content())->map(function ($item) {
            return [
                'name'     => $item['name'],
                'price'    => (float) $item['price'],
                'quantity' => (int) $item['qty'],
            ];
        })->values()->toArray();

        $paymentData = session('payment_data');
        //  dd($paymentData['items']);

        if (!$paymentData) {
            return redirect()->route('checkout')->with('error', 'No payment data found');
        }


        session()->put('fawaterk_order_id', $paymentData['order_id']);
        session()->put('fawaterk_subscription_id', $paymentData['order_id']);

        return view('larapayease::iframe', [
            'envType' => setting('fawaterk_production_mode') == '1' ? 'test' : 'live',
            'hashKey'   => $this->driver->generateHashKey(),
            'apiKey'    => $this->driver->getKeys()['vendor_key'],
            'amount' => $paymentData['amount'],
            'currency' => 'EGP',
            'customer' => [
                'first_name' => $paymentData['first_name'],
                'last_name' => $paymentData['last_name'],
                'email' => $paymentData['email'],
                'phone' => $paymentData['mobile'],
                // 'address' => $paymentData['address']
            ],
            'cartItems' => $cartItems,
            'order_id' => $paymentData['order_id'],
            'user_id' => auth()->id(),
        ]);
    }


    private function updateStatusAndRedirect(string $status)

    {
        $paymentData = session('payment_data');
        if (!$paymentData || !isset($paymentData['order_id'])) {
            return redirect()->route('checkout')->with('error', 'Invalid or missing payment data.');
        }

        $orderId = $paymentData['order_id'];

        if (!$orderId) {
            return redirect()->route('checkout')->with('error', 'Invalid.');
        }

        $order = \App\Models\Order::find($orderId);

        if (!$order) {
            return redirect()->route('checkout')->with('error', 'Invalid.');
        }

        $order->fawaterk_status = $status;

        switch ($status) {
            case 'success':
                $order->status = 1;
                dispatch(new \App\Jobs\CompletePurchaseJob($order));
                break;
            case 'fail':
            case 'pending':
                $order->status = 0;
                break;
        }

        $order->save();

        session()->forget('order_id');
        session()->forget('payment_data');
        session()->forget('fawaterk_subscription_id');

        if ($status === 'success') {
            return redirect()->route('thank-you', ['id' => $order->id]);
        }

        if ($status === 'fail') {
            return redirect()->route('checkout')->with('error',  __('checkout')['payment_failed']);
        }

        if ($status === 'pending') {
            return redirect()->route('checkout')->with('warning', __('checkout')['payment_pending']);
        }

        return redirect()->route('checkout');
    }


    public function handleSuccess(Request $request)
    {
        return $this->updateStatusAndRedirect('success');
    }

    public function handleFail(Request $request)
    {
        return $this->updateStatusAndRedirect('fail');
    }

    public function handlePending(Request $request)
    {
        return $this->updateStatusAndRedirect('pending');
    }
}
