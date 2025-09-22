<?php

namespace Modules\Subscriptions\Jobs;

use App\Jobs\SendDbNotificationJob;
use App\Jobs\SendNotificationJob;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Modules\Subscriptions\Models\UserSubscription;
use Illuminate\Support\Str;
use Modules\Subscriptions\Models\Subscription;

class SubscriptionRenewNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected UserSubscription $userSubscription;

    /**
     * Create a new job instance.
     */
    public function __construct(UserSubscription $userSubscription)
    {
        $this->userSubscription = $userSubscription;
    }

    /**
     * Execute the job.
     */
    public function handle(OrderService $orderService): void
    {
        $existingOrderItem = $this->userSubscription->orderItem;
        $existingOrder     = $this->userSubscription->order;

        $paymentMethodName = $existingOrder?->payment_method;
        $type = '';
        if (!empty($paymentMethodName)) {
            $isOfflinePayment = DB::table('offline_payments as op')->where('id', str_replace(['offline-', 'online-'], '', $paymentMethodName))->exists();
            $type = $isOfflinePayment ? 'offline' : 'online';
        }

        $newOrder = $orderService->createOrder([
            'user_id'                   => $this->userSubscription?->user_id,
            'first_name'                => $existingOrder?->first_name,
            'unique_payment_id'         => Str::uuid(),
            'amount'                    => $this->userSubscription?->subscription?->price,
            'currency'                  => setting('_general.currency') ?? '',
            'used_wallet_amt'           => 0,
            'last_name'                 => $existingOrder?->last_name,
            'company'                   => $existingOrder?->company,
            'email'                     => $existingOrder?->email,
            'phone'                     => $existingOrder?->phone,
            'country'                   => $existingOrder?->country,
            'state'                     => $existingOrder?->state,
            'city'                      => $existingOrder?->city,
            'postal_code'               => $existingOrder?->postal_code,
            'payment_method'            => $existingOrder?->payment_method,
            'payment_type_m' => empty($type) ? 'online' : $type,
            'description'               => $existingOrder?->description,
            'status'                    => 'pending',
        ]);

        $orderItems[] = [
            'order_id'       => $newOrder->id,
            'title'          => $existingOrderItem?->title,
            'quantity'       => 1,
            'options'        => $existingOrderItem?->options,
            'price'          => $this->userSubscription?->subscription?->price,
            'total'          => $this->userSubscription?->subscription?->price,
            'orderable_id'   => $this->userSubscription?->subscription_id,
            'orderable_type' => Subscription::class,
        ];
        
        $orderItems = $orderService->storeOrderItems($newOrder->id,$orderItems);

        $emailData = [
            'renewalLink'           =>  route('pay',['id' => $newOrder->unique_payment_id]),
            'userName'              => $this->userSubscription?->userProfile?->full_name,
            'subscriptionName'      => $this->userSubscription?->subscription?->name,
            'subscriptionExpiry'    => Carbon::parse($this->userSubscription?->expiry_date)->format(!empty(setting('_general.date_format')) ? setting('_general.date_format') : 'd-m-Y'),   
        ];

        dispatch(new SendNotificationJob('renewSubscription', $this->userSubscription?->user, $emailData));
        dispatch(new SendDbNotificationJob('renewSubscription', $this->userSubscription?->user, $emailData));
    }
}
