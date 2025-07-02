<?php

use Modules\KuponDeal\Livewire\KuponList\KuponList;
use Illuminate\Support\Facades\Route;


Route::middleware(['locale', 'maintenance', 'auth', 'verified', 'enabled:kupondeal'])->as('kupondeal.')->group(function () {
    Route::middleware('role:tutor|admin')->group(function () {
        Route::get('/coupon-list', KuponList::class)->name('coupon-list');
    });
});