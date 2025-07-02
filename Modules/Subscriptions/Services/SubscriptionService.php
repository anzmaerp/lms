<?php

namespace Modules\Subscriptions\Services;

use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\UserSubscription;

class SubscriptionService
{
    public function setSubscription($subscription, $subscriptionId = null){
        $subscriptionData = [
            'role_id'           => $subscription['role_id'],
            'name'              => $subscription['name'],
            'price'             => $subscription['price'],
            'period'            => $subscription['period'],
            'image'             => $subscription['image'] ?? null,
            'status'            => $subscription['status'],
            'credit_limits'     => $subscription['credit_limits'],
            'revenue_share'     => $subscription['revenue_share'],
            'auto_renew'        => $subscription['auto_renew'],
            'created_by'        => auth()->user()->id,
        ];
        if($subscriptionId){
            return Subscription::updateOrCreate(['id' => $subscriptionId], $subscriptionData);
        } else {
            return Subscription::create($subscriptionData);
        }
    }

    public function getSubscription($id){
        return Subscription::select('id', 'role_id', 'image', 'description', 'name', 'price', 'period', 'status', 'credit_limits', 'revenue_share', 'auto_renew', 'created_by', 'updated_at')->find($id);
    }


    public function getSubscriptions($filters = []){
        $subscriptions = Subscription::select('id', 'role_id', 'description', 'image', 'name', 'price', 'period', 'status', 'credit_limits', 'revenue_share', 'auto_renew', 'created_by', 'updated_at');

        if( !empty($filters['search']) ){
            $subscriptions = $subscriptions->where(function($query) use ($filters){
                $query->whereLike('name', '%'.$filters['search'].'%');
            });
        }
        
        if( !empty($filters['role_id']) ){
            $subscriptions = $subscriptions->where('role_id', $filters['role_id']);
        }

        if( !empty($filters['status']) && isset(Subscription::$statuses[$filters['status']]) ){
            $subscriptions = $subscriptions->where('status', Subscription::$statuses[$filters['status']]);
        }
        $subscriptions->orderBy('name', $filters['sortby'] ?? 'asc');

        if( !empty($filters['all']) ){
            return $subscriptions->get();
        }
        
        return $subscriptions->paginate($filters['perPage'] ?? 10);
    }

    public function addUserSubscription($subscriptionData){
        return UserSubscription::create($subscriptionData);
    }

    public function getPurchasedSubscriptions($filters = []){
        $purchasedSubscriptions = UserSubscription::select('id', 'user_id', 'order_item_id', 'subscription_id', 'status', 'expires_at','remaining_credits','credit_limits')
            ->withWhereHas('userProfile:profiles.id,user_id,first_name,last_name,image')
            ->withWhereHas('order:orders.id,transaction_id')
            ->withWhereHas('orderItem:id,title,options,price');
            if( !empty($filters['search']) ){
                $purchasedSubscriptions->where(function($query) use ($filters){
                    $query->whereHas('orderItem', function($query) use ($filters){
                        $query->select('order_items.id');
                        $query->where('title', 'like', '%'.$filters['search'].'%');
                    });
                    $query->orWhereHas('order', function($query) use ($filters){
                        $query->select('orders.id');
                        $query->where('transaction_id', 'like', '%'.$filters['search'].'%');
                    });
                });
            }
        if( !empty($filters['status']) && isset(UserSubscription::$statuses[$filters['status']]) ){
            $purchasedSubscriptions = $purchasedSubscriptions->where('status', UserSubscription::$statuses[$filters['status']]);
        }
        $purchasedSubscriptions->orderBy('created_at', $filters['sortby'] ?? 'desc');
        return $purchasedSubscriptions->paginate($filters['perPage'] ?? 10);
    }

    public function getUserSubscription($userId, $subscriptionId = null, $pluckIds = false){
        $userSubscriptions = UserSubscription::select('id', 'user_id', 'price','subscription_id','credit_limits', 'remaining_credits', 'revenue_share')
            ->where('user_id', $userId)
            ->when($subscriptionId, function($query) use ($subscriptionId){
                $query->where('subscription_id', $subscriptionId);
            })
            ->where('expires_at', '>', now())
            ->withWhereHas('subscription', function($query){
                $query->select('id','name', 'period');
            }); 
        $userSubscriptions->where('status', UserSubscription::$statuses['active']);
        if ($subscriptionId){
            return $userSubscriptions->first();
        } elseif($pluckIds){
            return $userSubscriptions->pluck('subscription_id')->toArray();
        } else {
            return $userSubscriptions->get();
        }
    }

    public function getSubscriptionTutorPayout($subscription, $for = 'sessions')
    {
        $revenueShare = $subscription->revenue_share;

        if(!empty($revenueShare['admin_share'])){
            $adminShare = $subscription->price * $revenueShare['admin_share'] / 100;
        }
        $revenuePool = $subscription->price - $adminShare;
        if(!empty($revenueShare['sessions_share']) && $for == 'sessions'){
            $sessionRevenue = $revenuePool * $revenueShare['sessions_share'] / 100;
            $tutorPayout = ($subscription->credit_limits['sessions'] ?? 0) > 0 ? $sessionRevenue/$subscription->credit_limits['sessions'] : 0;
        }
        if(!empty($revenueShare['courses_share']) && $for == 'courses'){
            $courseRevenue = $revenuePool * $revenueShare['courses_share'] / 100;
            $tutorPayout = ($subscription->credit_limits['courses'] ?? 0) > 0 ? $courseRevenue/$subscription->credit_limits['courses'] : 0;
        }
        return number_format($tutorPayout, 2);
    }

    public function updateUserSubscription($userId, $subscriptionId, $data){
        return UserSubscription::where('user_id', $userId)->where('subscription_id', $subscriptionId)->update($data);
    }
}
