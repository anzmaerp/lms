<?php

namespace App\Providers;

use App\Services\CartService;
use App\Services\DbNotificationService;
use App\View\Composers\AdminComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
        $this->app->singleton('cart', function ($app) {
            return new CartService();
        });

        $this->app->singleton('db-notification', function () {
            return new DbNotificationService();
        });

        // Adding fallback/override for the getGatewayObject function
        // This ensures Hesabe payments work correctly regardless of helpers.php issues
        if (!function_exists('getGatewayObject')) {
            $this->app->bind('getGatewayObject', function ($app, $params) {
                $gateway = $params[0] ?? null;
                
                
                // Special handling for Hesabe payment gateway
                if ($gateway === 'hesabe') {
                    return new \App\Services\HesabePaymentService();
                }
                // Special handling for Hesabe payment gateway
                if ($gateway === 'fawaterk') {
                    return new \App\Services\FawaterkPaymentService();
                }
                // Original implementation (simplified)
                $data = setting('admin_settings.payment_method');
                $settings = $data[$gateway] ?? null;
                
                if (!empty(getCurrentCurrency())) {
                    $settings['currency'] = getCurrentCurrency()['code'];
                }
                
                $gateways = \Modules\LaraPayease\Facades\PaymentDriver::supportedGateways();
                
                if (!empty($data)) {
                    $mode = !empty($settings['enable_test_mode']) ? 'test' : 'live';
                    $keys = array_intersect_key($settings, $gateways[$gateway]['keys']);
                    
                    if ($gateway == 'payfast') {
                        $keys['webhook_url'] = route('payfast.webhook');
                    }
                    
                    $gatewayObj = \Modules\LaraPayease\Facades\PaymentDriver::{$gateway}();
                    $gatewayObj->setKeys($keys);
                    $gatewayObj->setCurrency($settings['currency'] ?? 'USD');
                    $gatewayObj->setExchangeRate($settings['exchange_rate'] ?? '');
                    $gatewayObj->setMode($mode);
                    
                    return $gatewayObj;
                }
                
                return '';
            });
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });
        View::composer('*', AdminComposer::class);

        Gate::before(function ($user, $ability) {
            if ($user->role == 'admin') {
                return true;
            }
        });
        
        if (Schema::hasTable(config('optionbuilder.db_prefix').'settings')) {
            $this->app->setLocale(getLocaleToSet());
        }
    }
}

// Helper function that wraps the app binding
if (!function_exists('getGatewayObject')) {
    function getGatewayObject($gateway) {
        return app()->call('getGatewayObject', [$gateway]);
    }
}