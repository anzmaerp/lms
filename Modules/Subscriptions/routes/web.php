<?php

use Modules\Subscriptions\Livewire\Admin\PurchasedSubscriptions;
use Modules\Subscriptions\Livewire\Admin\SubscriptionList;
use Modules\Subscriptions\Livewire\Front\Subscriptions;
use Illuminate\Support\Facades\Route;

Route::middleware(['web','auth'])->group(function() {
    Route::middleware(['role:admin|sub_admin','permit-of:can-manage-subscriptions'])->prefix('admin/subscriptions')->name('admin.subscriptions.')->group(function() {
        Route::get('/', SubscriptionList::class)->name('index');
        Route::get('purchased', PurchasedSubscriptions::class)->name('purchased');
    });

    Route::middleware(['verified', 'maintenance', 'role:student|tutor'])->prefix('subscriptions')->name('subscriptions.')->group(function() {
        Route::get('/', Subscriptions::class)->name('index');
    });
});



