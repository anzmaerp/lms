<?php

use Illuminate\Support\Facades\Route;

use Modules\IPManager\Livewire\Pages\Admin\LoginHistory;
use Modules\IPManager\Livewire\Pages\Admin\IPRestriction;

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


Route::middleware(['locale', 'maintenance', 'enabled:ipmanager'])->as('ipmanager.')->group(function () {
    Route::middleware(['auth', 'verified', 'role:admin|sub_admin','permit-of:can-manage-ipmanager'])->name('admin.')->group(function () {
        Route::get('login-history', LoginHistory::class)->name('login-history');
        Route::get('ip-restriction', IPRestriction::class)->name('ip-restriction');
    });
});


