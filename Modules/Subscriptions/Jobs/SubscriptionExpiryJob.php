<?php

namespace Modules\Subscriptions\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Modules\Subscriptions\Models\UserSubscription;
use Modules\Subscriptions\Services\SubscriptionService;

class SubscriptionExpiryJob implements ShouldQueue
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
    public function handle(SubscriptionService $service): void
    {
        $service->updateUserSubscription($this->userSubscription?->user_id, $this->userSubscription?->subscription_id, ['status' => UserSubscription::$statuses['expired']]);
    }
}
