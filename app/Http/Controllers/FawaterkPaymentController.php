<?php

namespace App\Http\Controllers;

use App\Jobs\CompletePurchaseJob;
use App\Models\Order;
use App\Services\FawaterkPaymentService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\LaraPayease\Drivers\Fawaterk;

class FawaterkPaymentController extends Controller
{
    protected $fawaterkService;
    protected $orderService;

    protected $fawaterk;

    public function __construct(Fawaterk $fawaterk)
    {
        $this->fawaterk = $fawaterk;
    }

    public function process(Request $request)
    {
        $paymentData = session('payment_data');

        if (!$paymentData) {
            return redirect()->route('checkout')->with('error', 'No payment data found');
        }

        $orderId = 'FAW_' . time() . '_' . \Str::random(6);

        session()->put('fawaterk_order_id', $orderId);
        session()->put('fawaterk_subscription_id', $paymentData['order_id']);

        return view('larapayease::iframe', [
            'envType' => setting('fawaterk_production_mode') == '1' ? 'live' : 'test',
            'hashKey' => $this->fawaterk->generateHashKey($this->fawaterk->getKeys()),
            'amount' => $paymentData['amount'],
            'currency' => 'EGP',
            'customer' => [
                'first_name' => $paymentData['first_name'] ?? 'Customer',
                'last_name' => $paymentData['last_name'] ?? 'Name',
                'email' => $paymentData['email'] ?? 'customer@example.com',
                'phone' => $paymentData['phone'] ?? '01000000000',
                'address' => $paymentData['address'] ?? 'Cairo, Egypt'
            ],
            'cartItems' => $paymentData['items'], 
            'order_id' => $orderId,
            'user_id' => auth()->id(),
        ]);
       dd($paymentData['items']);
    }
    /**
     * Handle success/fail/pending callback from Fawaterk
     */
    public function callback(Request $request)
    {
        try {
            $transactionId = $request->input('transaction_id');
            $status = $request->input('status');
            $orderId = session('fawaterk_subscription_id');

            if (empty($transactionId) || empty($status) || empty($orderId)) {
                return redirect()->route('checkout')->with('error', 'Invalid payment response');
            }

            $order = Order::where('id', $orderId)
                ->where('transaction_id', $transactionId)
                ->first();

            if (!$order) {
                return redirect()->route('checkout')->with('error', 'Order not found');
            }

            if ($status === 'success') {
                $order->update(['status' => 'complete']);

                session()->forget('order_id');
                session()->forget('payment_data');
                session()->forget('fawaterk_subscription_id');

                dispatch(new CompletePurchaseJob($order));

                return redirect()->route('thank-you', ['id' => $order->id]);
            } elseif ($status === 'fail') {
                return redirect()->route('checkout')->with('error', 'Payment failed. Please try again.');
            } elseif ($status === 'pending') {
                return redirect()->route('checkout')->with('warning', 'Payment is still pending.');
            } else {
                return redirect()->route('checkout')->with('error', 'Unknown payment status.');
            }
        } catch (\Exception $e) {
            Log::error('Fawaterk callback exception: ' . $e->getMessage());
            return redirect()->route('checkout')->with('error', 'An error occurred during payment processing');
        }
    }
}
