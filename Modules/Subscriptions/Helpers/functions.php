<?php 

if (!function_exists('getSubscriptionExpiry')) {
    function getSubscriptionExpiry($subscription) {
        return match ($subscription->period) {
            'monthly'  => now()->addMonth(),
            '6_months' => now()->addMonths(6),
            'yearly'   => now()->addYear()
        };
    }
}