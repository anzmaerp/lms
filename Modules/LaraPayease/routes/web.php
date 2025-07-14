<?php

use Illuminate\Support\Facades\Route;
use Modules\LaraPayease\Http\Controllers\HesabePaymentController;
use Modules\LaraPayease\Http\Controllers\PaymobPaymentController;
use Modules\LaraPayease\Http\Controllers\StripePaymentController;
use Modules\LaraPayease\Http\Controllers\FawaterkPaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('payease/stripe', [StripePaymentController::class, 'prepareCharge'])->name('payease.stripe');

// Paymob Routes
Route::post('payease/paymob', [PaymobPaymentController::class, 'prepareCharge'])->name('payease.paymob');
Route::get('payease/paymob/callback', [PaymobPaymentController::class, 'handleCallback'])->name('payease.paymob.callback');

// Hesabe Routes
Route::post('payease/hesabe', [HesabePaymentController::class, 'prepareCharge'])->name('payease.hesabe');
Route::get('payease/hesabe/callback', [HesabePaymentController::class, 'handleCallback'])->name('payease.hesabe.callback');
// Hesabe Routes
Route::prefix('payment/fawaterk')->group(function () {
    Route::get('/process', [FawaterkPaymentController::class, 'process'])->name('payment.fawaterk.process');
    Route::get('/callback', [FawaterkPaymentController::class, 'callback'])->name('payment.fawaterk.callback');
});

// Add the Hesabe controller routes
Route::group(['prefix' => 'larapayease/hesabe'], function () {
    Route::get('checkout', 'HesabeController@checkout')->name('larapayease.hesabe.checkout');
    Route::get('direct', 'HesabeController@directPayment')->name('larapayease.hesabe.direct');
});
// Add the Hesabe controller routes
// Route::group(['prefix' => 'larapayease/fawaterk'], function () {
//     Route::get('checkout', 'FawaterkController@checkout')->name('larapayease.fawaterk.checkout');
//     Route::get('direct', 'FawaterkController@directPayment')->name('larapayease.fawaterk.direct');
// });