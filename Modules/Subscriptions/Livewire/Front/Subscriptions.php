<?php

namespace Modules\Subscriptions\Livewire\Front;

use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Services\SubscriptionService;
use App\Facades\Cart;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Subscriptions extends Component
{
    public $subscriptions, $purchasedSubscriptions;  
    public $roleId;
    protected $subscriptionService;

    public function boot(SubscriptionService $subscriptionService){
        $this->subscriptionService = $subscriptionService;
    }

    public function mount($role = null){
        $this->roleId = getRoleByName($role);
        $this->subscriptions = $this->subscriptionService->getSubscriptions(['role_id' => $this->roleId, 'status' => 'active', 'all' => true]);
        if (auth()->check()) {
            $this->purchasedSubscriptions = $this->subscriptionService->getUserSubscription(userId:auth()->user()->id, pluckIds: true);
        } else {
            $this->purchasedSubscriptions = [];
        }
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        $cartItemIds = array_column(array_column(Cart::content()?->toArray() ?? [], 'options'), 'subscription_id');
        return view('subscriptions::livewire.front.subscriptions', compact('cartItemIds'));
    }

    public function addToCart($id)
    {
        \Illuminate\Support\Facades\Log::info('addToCart called', [
            'user_id' => auth()->check() ? auth()->user()->id : 'Guest',
            'role' => auth()->check() ? auth()->user()->role : 'None',
            'subscription_id' => $id
        ]);

        try {
            $subscription = $this->subscriptionService->getSubscription($id);
            
            if (auth()->guest() || auth()->user()->role == 'admin') {
                \Illuminate\Support\Facades\Log::info('addToCart: User is guest or admin');
                $this->dispatch('showAlertMessage', type: 'error', message: __('general.not_allowed'));
                return;
            }

            if (!empty($subscription) && $subscription->status == 'active') {
                if ($subscription->role_id != getRoleByName(auth()->user()->role)) {
                    $this->dispatch('showAlertMessage', type: 'error', message: __('subscriptions::subscription.invalid_role_for_subscription'));
                    return;
                }

                if (in_array($subscription->id, $this->purchasedSubscriptions) || !empty($this->purchasedSubscriptions)) {
                    $this->dispatch('showAlertMessage', type: 'error', message: __('subscriptions::subscription.subscription_already_purchased'));
                    return;
                }

                Cart::add(
                    cartableId: $subscription->id, 
                    cartableType: Subscription::class, 
                    name: $subscription->name, 
                    qty: 1, 
                    price: $subscription->price, 
                    options: [
                        'name'              => $subscription->name,
                        'subscription_id'   => $subscription->id,
                        'price'             => $subscription->price,
                        'revenue_share'     => $subscription->revenue_share,
                        'credit_limits'     => $subscription->credit_limits,
                        'auto_renew'        => $subscription->auto_renew,
                        'period'            => __('subscriptions::subscription.'.$subscription->period),
                        'role_id'           => $subscription->role_id,
                        'image'             => $subscription->image,    
                    ]
                );
                \Illuminate\Support\Facades\Log::info('Cart updated', ['cart' => Cart::content()]);
                $this->dispatch('cart-updated', cart_data: Cart::content(), discount: formatAmount(Cart::discount(), true), total: formatAmount(Cart::total(), true), subTotal: formatAmount(Cart::subtotal(), true));
                return redirect()->route('checkout');
            } else {
                $this->dispatch('showAlertMessage', type: 'error', message: __('subscriptions::subscription.subscription_not_available'));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('addToCart failed', ['error' => $e->getMessage(), 'subscription_id' => $id]);
            $this->dispatch('showAlertMessage', type: 'error', message: __('general.error_occurred'));
        }
    }

}