<?php

namespace App\Livewire\Frontend;

use Modules\LaraPayease\Facades\PaymentDriver;
use App\Livewire\Forms\Frontend\OrderForm;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use App\Facades\Cart;
use App\Services\ProfileService;
use App\Jobs\CompletePurchaseJob;
use App\Services\SiteService;
use Illuminate\Support\Str;
use App\Models\SlotBooking;
use App\Services\BillingService;
use App\Services\BookingService;
use App\Services\OrderService;
use App\Services\WalletService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Nwidart\Modules\Facades\Module;
use Livewire\WithFileUploads;

class Checkout extends Component
{
    use WithFileUploads;
    public OrderForm $form;

    public $user;
    public $methods = [];
    public $address;
    public $content;
    public $countries = [];
    public $payAmount, $discount, $subTotal;
    public $totalAmount = '';
    public $walletBalance = '';
    public $billingDetail;
    public $payment_methods = [];
    public $payment_methods_offline = [];
    public $allowImgFileExt = [];
    public $fileExt = '';
    public $allowImageSize = '';
    public $useWalletBalance = false;
    public $checkoutReady = true;
    public $subscriptions, $chosenSubscription, $invalidCartItem;
    public $orderDetail;
    public $coupon;
    public $available_payment_methods = [];
    public $isLoading = true;
    public $isUploading = false;

    private ?OrderService $orderService = null;
    private ?WalletService $walletService = null;
    private ?BillingService $billingService = null;
    private ?ProfileService $profileService = null;
    private ?SiteService $siteService = null;
    public function boot()
    {
        $this->user = Auth::user();
        $this->orderService = new OrderService();
        $this->siteService = new SiteService();
        $this->profileService = new ProfileService(Auth::user()?->id);
        $this->walletService = new WalletService();
        $this->billingService = new BillingService(Auth::user());
    }

    public function mount()
    {
        $this->dispatch('initSelect2', target: '.am-select2');
        $order_id = session('order_id') ?? '';
        if ($order_id) {
            $this->orderDetail = $this->orderService->getOrderDetail($order_id);
        } else {
            $this->billingDetail = $this->billingService->getBillingDetail();
            $this->address = $this->billingService->getUserAddress();
        }

        // Add Hesabe payment method if enabled
        if (setting('hesabe_enabled')) {
            $this->methods['hesabe'] = [
                'title' => 'Hesabe',
                'description' => 'Pay securely using Hesabe payment gateway',
                'icon' => 'hesabe-icon', // Optional: add an icon class if available
            ];
        }
        // Add fawaterk payment method if enabled
        if (setting('fawaterk_enabled')) {
            $this->methods['fawaterk'] = [
                'title' => 'fawaterk',
                'description' => 'Pay securely using fawaterk payment gateway',
                'icon' => 'fawaterk-icon', // Optional: add an icon class if available
            ];
        }

        $image_file_ext = setting('_general.allowed_image_extensions') ?? 'jpg,png';
        $image_file_size = (int) (setting('_general.max_image_size') ?? '5');
        $this->allowImageSize = !empty($image_file_size) ? $image_file_size : '5';
        $this->allowImgFileExt = !empty($image_file_ext) ? explode(',', $image_file_ext) : [];
        $this->fileExt = fileValidationText($this->allowImgFileExt);

        $gateways = $this->rearrangeArray(PaymentDriver::supportedGateways());
        $this->methods = array_merge($this->methods, $gateways);
        $this->walletBalance = $this->walletService->getWalletAmount(Auth::user()->id);
        $this->countries = $this->siteService->getCountries();
        if (Module::has('subscriptions') && Module::isEnabled('subscriptions')) {
            $this->subscriptions = (new \Modules\Subscriptions\Services\SubscriptionService())->getUserSubscription(userId: Auth::user()->id);
        }

        $this->payment_methods_offline = DB::table('offline_payments as op')
            ->where('status', 'active')
            ->select(['op.id', 'op.name', 'op.instructions', 'op.description'])
            ->get();

        if (!empty($this->orderDetail)) {
            $billingData = (object) [
                "first_name" => $this->orderDetail->first_name ?? '',
                "last_name" => $this->orderDetail->last_name ?? '',
                "company" => $this->orderDetail->company ?? '',
                "phone" => $this->orderDetail->phone ?? '',
                "payment_method" => $this->orderDetail->payment_method ?? '',
                "email" => $this->orderDetail->email ?? ''
            ];

            $address = (object) [
                "country_id" => $this->orderDetail->country ?? '',
                "state" => $this->orderDetail->state ?? '',
                "zipcode" => $this->orderDetail->postal_code ?? '',
                "city" => $this->orderDetail->city ?? ''
            ];

            $this->form->setInfo($billingData);
            $this->form->setUserAddress($address);
            $this->chosenSubscription = $this->orderDetail->subscription_id;
        } elseif (!empty($this->billingDetail) && !empty($this->address)) {

            $this->form->setInfo($this->billingDetail);
            $this->form->setUserAddress($this->address, false);
            $this->form->paymentMethod = setting('admin_settings.default_payment_method') ?? '';
        } else {
            $this->address = $this->profileService->getUserAddress();
            $profileData = (object) [
                "first_name" => $this->user->profile->first_name ?? '',
                "last_name" => $this->user->profile->last_name ?? '',
                "email" => $this->user->email ?? ''
            ];
            $state = $this->siteService->getState($this->address?->state_id);
            $addressData = (object) [
                "country_id" => $this->address?->country_id ?? '',
                "state" => $state->name ?? '',
                "zipcode" => $this->address?->zipcode ?? '',
                "city" => $this->address?->city ?? ''
            ];
            $this->form->setInfo($profileData);
            $this->form->setUserAddress($addressData);
            $this->form->paymentMethod = setting('admin_settings.default_payment_method') ?? '';
        }
        $this->prepareCartAmount();
        if (!empty($this->chosenSubscription)) {
            $this->updatedChosenSubscription($this->chosenSubscription);
        }
        $this->getavailablePaymentMethods();
    }

    public function getAvailablePaymentMethods()
    {
        $payment_methods = setting('admin_settings.payment_method');
        if (!is_array($payment_methods)) {
            $payment_methods = [];
        }

        // Check if Hesabe is enabled and add it to payment methods
        // if (setting('hesabe_enabled') == '1') {
        //     $payment_methods['hesabe'] = true;
        // }

        if (!empty($payment_methods)) {
            foreach ($payment_methods as $type => $value) {
                if (array_key_exists($type, $this->methods)) {
                    $this->available_payment_methods[$type] = $value;
                }
            }
        }
    }


    #[Layout('layouts.guest')]
    public function render()
    {
        $this->form->walletBalance = $this->walletBalance;
        $this->form->useWalletBalance = $this->useWalletBalance;
        if ($this->content->count() == 0) {
            redirect()->route('find-tutors');
        }

        return view('livewire.frontend.checkout');
    }

    protected function prepareCartAmount()
    {
        $this->content = Cart::content();
        $this->subTotal = Cart::subtotal();
        $this->discount = Cart::discount();
        $this->totalAmount = Cart::total();
        $this->form->totalAmount = $this->totalAmount;
        $this->payAmount = $this->totalAmount;
    }

    public function updatedUseWalletBalance($value)
    {
        if ($value) {
            $this->payAmount = $this->totalAmount - $this->walletBalance;
        } else {
            $this->payAmount = $this->totalAmount;
        }
    }

    public function updatedForm($value, $key)
    {
        if ($key == 'paymentMethod') {
            $this->chosenSubscription = null;
            $this->checkoutReady = true;
            $this->payAmount = $this->totalAmount;
        }
    }

    public function updatedChosenSubscription($value)
    {
        $this->useWalletBalance = false;
        $subscriptionDiscount = 0;
        $this->form->paymentMethod = 'subscription';
        if (Module::has('subscriptions') && Module::isEnabled('subscriptions') && !empty($value)) {
            $choosedSubscription = $this->subscriptions->where('subscription_id', '=', $value)->first();
            if (!empty($choosedSubscription)) {
                foreach ($this->content as $item) {
                    if (
                        $item['cartable_type'] == SlotBooking::class &&
                        (
                            setting('_lernen.subscription_sessions_allowed') == 'all' ||
                            (
                                setting('_lernen.subscription_sessions_allowed') == 'tutor' &&
                                ($item['options']['allowed_for_subscriptions'] ?? 0) == 1
                            )
                        ) &&
                        ($choosedSubscription?->remaining_credits['sessions'] ?? 0) > 0
                    ) {
                        $subscriptionDiscount += $item['price'];
                        $this->checkoutReady = true;
                        if (!empty($item['options']['discount_code'])) {
                            $this->removeCoupon($item['options']['discount_code']);
                        }
                    } elseif (
                        Module::has('courses') && Module::isEnabled('courses') && $item['cartable_type'] == \Modules\Courses\Models\Course::class &&
                        ($choosedSubscription?->remaining_credits['courses'] ?? 0) > 0
                    ) {
                        $this->checkoutReady = true;
                        $subscriptionDiscount += $item['price'];
                        if (!empty($item['options']['discount_code'])) {
                            $this->removeCoupon($item['options']['discount_code']);
                        }
                    } else {
                        $this->checkoutReady = false;
                        $this->invalidCartItem = $item;
                        break;
                    }
                }
            }
        }
        if ($subscriptionDiscount <= $this->payAmount) {
            $this->payAmount = $this->payAmount - $subscriptionDiscount;
        } else {
            $this->payAmount = 0;
        }
    }

    public function rearrangeArray($array)
    {
        return array_map(function ($details) {
            if (isset($details['keys'])) {
                $details = array_merge($details, $details['keys']);
                unset($details['keys']);
            }
            if (isset($details['ipn_url_type'])) {
                unset($details['ipn_url_type']);
            }
            return $details;
        }, $array);
    }

    public function updateInfo()
    {

        try {
            if ($this->checkoutReady == false) {
                $this->dispatch('showAlertMessage', type: 'error', message: __('subscriptions::subscription.not_applicable_to_cart_item', ['name' => $this->invalidCartItem['name'] ?? '']));
                return;
            }
            DB::beginTransaction();
            $orderItems = [];
            $data = $this->form->updateInfo();
            if (Module::has('subscriptions') && Module::isEnabled('subscriptions') && !empty($this->chosenSubscription)) {
                $data['subscription_id'] = $this->chosenSubscription;
            }
            if (!empty($this->orderDetail)) {
                $orderDetail = $this->orderService->updateOrder($this->orderDetail, $data);
            } else {
                $orderDetail = $this->orderService->createOrder($data);
            }
            session(['order_id' => $orderDetail->id]);

            foreach ($this->content as $item) {
                $orderItemData = [
                    'order_id' => $orderDetail->id,
                    'title' => $item['name'],
                    'quantity' => $item['qty'],
                    'options' => $item['options'],
                    'price' => $item['price'],
                    'total' => (float) $item['qty'] * (float) $item['price'],
                    'orderable_id' => $item['cartable_id'],
                    'orderable_type' => $item['cartable_type'],
                ];
                if (Module::has('kupondeal') && Module::isEnabled('kupondeal') && !empty($item['discount_amount'])) {
                    $orderItemData['discount_amount'] = $item['discount_amount'];
                    $orderItemData['total'] = $orderItemData['total'] - $item['discount_amount'];
                }
                $orderItems[] = $orderItemData;
            }

            $this->orderService->storeOrderItems($orderDetail->id, $orderItems);
            if ($this->useWalletBalance) {
                if ($this->walletBalance >= $this->totalAmount) {
                    $this->walletService->deductFunds(Auth::user()->id, $this->totalAmount, 'deduct_booking', $orderDetail->id);
                } else {
                    $this->walletService->deductFunds(Auth::user()->id, $this->walletBalance, 'deduct_booking', $orderDetail->id);
                }
            }
            if (Module::has('subscriptions') && Module::isEnabled('subscriptions') && !empty($this->chosenSubscription)) {
                $this->orderService->updateOrder($orderDetail, ['subscription_id' => $this->chosenSubscription]);
            }
            $realPaymentName = str_replace(['offline-', 'online-'], '', $this->form->paymentMethod);
            if (($this->useWalletBalance && ($this->walletBalance >= $this->totalAmount)) || $this->payAmount == 0 || str_starts_with($this->form->paymentMethod, 'offline-')) {
                $orderDetail = $this->orderService->updateOrder($orderDetail, ['status' => 'complete']);
                DB::commit();
                session()->forget('order_id');
                dispatch(new CompletePurchaseJob($orderDetail));
                Cart::clear();
                redirect()->route('thank-you', ['id' => $orderDetail->id]);
            } else {
                DB::commit();
                $ipnUrl = PaymentDriver::getIpnUrl($realPaymentName);
                session([
                    'payment_data' => [
                        'amount' => $this->payAmount,
                        'title' => setting('_general.site_name') ?? env('APP_NAME', 'Lernen') . ' Purchase',
                        'description' => setting('_general.site_name') ?? env('APP_NAME', 'Lernen') . ' Purchase Order Confirmation for reference #' . $orderDetail->id,
                        'ipn_url' => !empty($ipnUrl) ? route($ipnUrl, ['payment_method' => $realPaymentName]) : url('/'),
                        'order_id' => $orderDetail->id,
                        'track' => Str::random(36),
                        'cancel_url' => route('checkout'),
                        'success_url' => route('thank-you', ['id' => $orderDetail->id]),
                        'email' => $orderDetail->email,
                        'first_name' => $orderDetail->first_name,
                        'last_name' => $orderDetail->last_name,
                        'mobile' => $orderDetail->phone,
                        'payment_type' => '0', // means indirect payment
                    ]
                ]);
                return redirect()->route('payment.process', ['gateway' => $realPaymentName]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function removeCart($id, $type)
    {
        if ($type == 'App\Models\SlotBooking') {
            (new BookingService($this->user))->removeReservedBooking($id);
        }
        Cart::remove($id, $type);
        $this->prepareCartAmount();
    }

    public function removeCoupon($couponCode)
    {
        $response = \Modules\KuponDeal\Facades\KuponDeal::removeCoupon($couponCode);
        $this->dispatch('showAlertMessage', type: $response['status'], message: $response['message']);
        $this->prepareCartAmount();
        $this->dispatch('cart-updated', cart_data: Cart::content(), discount: formatAmount(Cart::discount(), true), total: formatAmount(Cart::total(), true), subTotal: formatAmount(Cart::subtotal(), true));
    }

    public function applyCoupon()
    {
        $this->validate([
            'coupon' => 'required|string|max:30',
        ]);

        if (!empty($this->chosenSubscription)) {
            $this->dispatch('showAlertMessage', type: 'error', message: __('subscriptions::subscription.cupon_not_applicable_with_subscription'));
            $this->reset('coupon');
            $this->prepareCartAmount();
            return;
        }

        if (Module::has('kupondeal') && Module::isEnabled('kupondeal')) {

            $conditionCopoun = \Modules\KuponDeal\Facades\KuponDeal::getCouponConditions($this->coupon);

            $order = $this->orderService->getUserOrderDetail();

            $couponConditions = true;

            $conditionsRaw = $conditionCopoun?->conditions ?? '';
            $conditions = is_string($conditionsRaw)
                ? json_decode($conditionsRaw, true)
                : $conditionsRaw;
            if (!is_array($conditions) && !is_object($conditions)) {
                $conditions = [];
            }

            if (!empty($conditions) && (is_array($conditions) || is_object($conditions))) {
                foreach ($conditions as $condition => $value) {
                    if ($condition == \Modules\KuponDeal\Models\Coupon::CONDITION_FIRST_ORDER && !empty($order)) {
                        $couponConditions = false;
                        break;
                    } elseif ($condition == \Modules\KuponDeal\Models\Coupon::CONDITION_MINIMUM_ORDER && Cart::total() < $value) {
                        $couponConditions = false;
                        break;
                    }
                }
            } elseif (empty($conditions)) {
                $couponConditions = true;
            } else {
                $couponConditions = false;
                $response = [
                    'status' => 'error',
                    'message' => __('general.invalid_coupon_conditions'),
                ];
                $this->dispatch('showAlertMessage', type: $response['status'], message: $response['message']);
                $this->reset('coupon');
                $this->prepareCartAmount();
                return;
            }

            if ($couponConditions) {
                try {
                    $couponModel = \Modules\KuponDeal\Models\Coupon::where('code', $this->coupon)->first();

                    if (!$couponModel) {
                        $response = [
                            'status' => 'error',
                            'message' => __('general.invalid_coupon'),
                        ];
                        $this->dispatch('showAlertMessage', type: $response['status'], message: $response['message']);
                        $this->reset('coupon');
                        $this->prepareCartAmount();
                        return;
                    }

                    $cartItems = Cart::content();

                    $couponableRaw = $couponModel->couponable_id;

                    $couponIds = [];
                    if (!empty($couponableRaw)) {
                        if (is_string($couponableRaw)) {
                            $decoded = json_decode($couponableRaw, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $couponIds = array_map('intval', $decoded); 
                            } elseif (is_numeric($couponableRaw)) {
                                $couponIds = [(int)$couponableRaw];
                            }
                        } elseif (is_numeric($couponableRaw)) {
                            $couponIds = [(int)$couponableRaw];
                        }
                    }

                    $applicable = false;
                    foreach ($cartItems as $item) {
                        $cartableId = (int)($item['cartable_id'] ?? 0);
                        if (empty($couponIds) || in_array($cartableId, $couponIds)) {
                            $applicable = true;
                            break;
                        }
                    }

                    if (!$applicable) {
                        $response = [
                            'status' => 'error',
                            'message' => __('kupondeal::kupondeal.coupon_not_applicable'),
                        ];

                    } else {
                        $response = \Modules\KuponDeal\Facades\KuponDeal::applyCoupon($this->coupon, $couponIds);
                    }
                } catch (\Exception $e) {
                    $response = [
                        'status' => 'error',
                        'message' => __('kupondeal::kupondeal.coupon_apply_failed') . ' (' . $e->getMessage() . ')',
                    ];
                }
            } else {
                $response = [
                    'status' => 'error',
                    'message' => __('kupondeal::kupondeal.coupon_apply_failed'),
                ];
            }

            $this->dispatch('showAlertMessage', type: $response['status'], message: $response['message']);
        } else {
            $this->dispatch('showAlertMessage', type: 'error', message: __('kupondeal::kupondeal.kupondeal_not_active'));
        }

        $this->reset('coupon');
        $this->prepareCartAmount();
    }

    public function removeMedia($type)
    {
        match ($type) {
            'payment_file_path' => $this->form->removePhoto()
        };
    }
}
