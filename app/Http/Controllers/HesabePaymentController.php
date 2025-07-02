<?php

namespace App\Http\Controllers;

use App\Jobs\CompletePurchaseJob;
use App\Models\Order;
use App\Services\HesabePaymentService;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HesabePaymentController extends Controller
{
    protected $hesabeService;
    protected $orderService;

    public function __construct(HesabePaymentService $hesabeService, OrderService $orderService)
    {
        $this->hesabeService = $hesabeService;
        $this->orderService = $orderService;
    }

    /**
     * Process payment
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(Request $request)
    {
        $paymentData = session('payment_data');

        if (!$paymentData) {
            return redirect()->route('checkout')->with('error', 'No payment data found');
        }

        $response = $this->hesabeService->createPayment($paymentData);

        if (!$response['success']) {
            return redirect()->route('checkout')->with('error', $response['message'] ?? 'Payment processing failed');
        }

        if (empty($response['payment_url'])) {
            return redirect()->route('checkout')->with('error', 'Invalid payment gateway response');
        }

        // Store payment token for later verification
        $order = Order::find($paymentData['order_id']);
        if ($order) {
            $order->update([
                'transaction_id' => $response['data']['response']['paymentToken'] ?? null,
            ]);
        }

        // Redirect to Hesabe payment page
        return redirect($response['payment_url']);
    }

    /**
     * Handle payment callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function callback(Request $request)
    {
        try {
            $paymentToken = $request->input('paymentToken');

            if (empty($paymentToken)) {
                return redirect()->route('checkout')->with('error', 'Invalid payment response');
            }

            $response = $this->hesabeService->checkPaymentStatus($paymentToken);

            if (!$response['success']) {
                return redirect()->route('checkout')->with('error', $response['message'] ?? 'Payment verification failed');
            }

            $paymentData = $response['data'] ?? [];
            $status = $paymentData['response']['status'] ?? null;
            $orderId = $paymentData['response']['variable1'] ?? null;

            if (empty($orderId)) {
                return redirect()->route('checkout')->with('error', 'Order ID not found in payment response');
            }

            $order = Order::where('id', $orderId)
                ->where('transaction_id', $paymentToken)
                ->first();

            if (!$order) {
                return redirect()->route('checkout')->with('error', 'Order not found');
            }

            // Check if payment was successful (status 1 = success)
            if ($status == 1) {
                // Update order status
                $order->update(['status' => 'complete']);

                // Process the order
                session()->forget('order_id');
                session()->forget('payment_data');

                dispatch(new CompletePurchaseJob($order));

                return redirect()->route('thank-you', ['id' => $order->id]);
            } else {
                // Payment failed
                return redirect()->route('checkout')->with('error', 'Payment was not completed. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('Hesabe callback exception: ' . $e->getMessage());
            return redirect()->route('checkout')->with('error', 'An error occurred during payment processing');
        }
    }
}