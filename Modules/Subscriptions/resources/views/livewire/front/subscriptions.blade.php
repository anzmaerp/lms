<div class="row justify-content-center">
        <head>

        @php
            $googleFont = setting('_general.google_font') ?? 'Roboto';
        @endphp

        @if (!empty($googleFont))
            <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $googleFont) }}&display=swap"
                rel="stylesheet">
            <style> * {
                    font-family: '{{ $googleFont }}', sans-serif !important;
                }
            </style>
        @endif
    </head>

    @if(!empty($subscriptions))
        @foreach($subscriptions as $subscription)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="am-pricing-card @if(in_array($subscription->id, $purchasedSubscriptions) || in_array($subscription->id, $cartItemIds)) am-active @endif">
                    <div class="am-plan-header">
                        <div class="am-plan-icon-wrapper">
                            <div class="am-plan-icon">
                                @if(!empty($subscription->image) && Storage::disk(getStorageDisk())->exists($subscription->image))
                                    <img src="{{ resizedImage($subscription->image, 88, 88) }}" alt="{{ $subscription->image }}">
                                @else
                                    <img src="{{ resizedImage('placeholder.png', 22, 22) }}" alt="{{ $subscription->image }}">
                                @endif    
                            </div>
                        </div>
                        <h2 class="am-plan-title">{{ $subscription->name }}</h2>
                        <p class="am-plan-description">
                            {{ $subscription->description }}
                        </p>
                        <div class="am-price-container">
                            <span class="am-price-amount">{{ formatAmount($subscription->price) }}</span>
                            <span class="am-price-period">/ {{  __('subscriptions::subscription.'. $subscription->period) }}</span>
                        </div>
                    </div>
                    <div class="am-features-section">
                        <div class="am-features-container">
                            <h3 class="am-features-title">{{ __('subscriptions::subscription.features') }}</h3>
                            <ul class="am-features-list">
                                @if(!empty($subscription->credit_limits['sessions']))    
                                    <li class="am-feature-item">
                                        <i class="am-icon-check-circle03"></i>
                                        <span class="am-feature-label">{{ __('subscriptions::subscription.sessions_quota') }}</span>
                                        <span class="am-feature-value">{{ $subscription->credit_limits['sessions'] }}</span>
                                    </li>
                                @endif
                                @if(\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses') && !empty($subscription->credit_limits['courses']))    
                                    <li class="am-feature-item">
                                        <i class="am-icon-check-circle03"></i>
                                        <span class="am-feature-label">{{ __('subscriptions::subscription.courses_quota') }}</span>
                                        <span class="am-feature-value">{{ $subscription->credit_limits['courses'] }}</span>
                                    </li>
                                @endif
                                <li class="am-feature-item">
                                    <i class="am-icon-check-circle03"></i>
                                    <span class="am-feature-label">{{ __('subscriptions::subscription.auto_renew') }}</span>
                                    <span class="am-feature-value"> {{ __('subscriptions::subscription.'. $subscription->auto_renew) }}</span>
                                </li>
                            </ul>
                        </div>
                        @if(in_array($subscription->id, $purchasedSubscriptions) || in_array($subscription->id, $cartItemIds))
                            <button class="am-white-btn am-buy-button">
                                {{ __('subscriptions::subscription.active_subscription') }}
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                                    <path d="M3 9.75L6.75 13.5L15 4.5" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        @else    
                            <button class="am-white-btn am-buy-button" @class(['am-btn_disabled' => !empty($purchasedSubscriptions)]) wire:click="addToCart({{ $subscription->id }})">{{ __('subscriptions::subscription.buy_now') }}</button>
                        @endif    
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</div>