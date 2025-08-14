<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @if (!empty(setting('_general.enable_rtl')) || !empty(session()->get('rtl'))) dir="rtl" @endif>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $googleFont = setting('_general.google_font') ?? 'Roboto';
        $fontSize = setting('_general.font_size') ?? 16;
        $fontWeight = setting('_general.font_weight') ?? 400;
        $theme_pri_color = setting('_theme.theme_pri_color');
        $theme_sec_color = setting('_theme.theme_sec_color');

    @endphp

    @if (!empty($googleFont))
        <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $googleFont) }}&display=swap"
            rel="stylesheet">
        <style>
            *:not(i) {
                font-family: '{{ $googleFont }}', sans-serif !important;
            }

            header * {
                font-size: {{ $fontSize }}px !important;
                font-weight: {{ $fontWeight }} !important;
            }
        </style>
    @endif



    <x-meta-content :pageTitle="$pageTitle ?? null" :page="$page ?? null" :pageDescription="$pageDescription ?? null" :pageKeywords="$pageKeywords ?? null" :metaImage="$metaImage ?? null" />

    @vite(['public/css/bootstrap.min.css', 'public/css/fonts.css', 'public/css/icomoon/style.css', 'public/css/select2.min.css', 'public/css/splide.min.css'])
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    @if (!empty($page) && $page->slug == 'home-two')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-two.css') }}">
    @elseif(!empty($page) && $page->slug == 'home-three')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-three.css') }}">
    @elseif(!empty($page) && $page->slug == 'home-four')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-four.css') }}">
    @elseif(!empty($page) && $page->slug == 'home-five')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-five.css') }}">
    @elseif(!empty($page) && $page->slug == 'home-six')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-six.css') }}">
    @elseif(!empty($page) && $page->slug == 'home-seven')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-seven.css') }}">
    @elseif(!empty($page) && $page->slug == 'home-eight')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-eight.css') }}">
    @elseif(!empty($page) && $page->slug == 'home-nine')
        <link rel="stylesheet" type="text/css" href="{{ asset('css/colors-variation/home-nine.css') }}">
    @endif
    <x-favicon />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}">
    
    @stack('styles')
    @if (!empty(setting('_general.enable_rtl')) || !empty(session()->get('rtl')))
        <link rel="stylesheet" type="text/css" href="{{ asset('css/rtl.css') }}">
    @endif

    @if (!empty(setting('_scripts_styles.header_scripts')))
        {!! setting('_scripts_styles.header_scripts') !!}
    @endif

    @if (!empty(setting('_scripts_styles.custom_styles')))
        <style>
            /* {!! setting('_scripts_styles.custom_styles') !!} */
            {!! html_entity_decode(setting('_scripts_styles.custom_styles')) !!}
        </style>
    @endif
    {{-- <style>
        .whatsapp-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 100;
            width: 60px;
            height: 60px;
            background-color: #25d366;
            border-radius: 50%;
            text-align: center;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease;
        }

        .whatsapp-float:hover {
            transform: scale(1.1);
        }

        .whatsapp-float img {
            width: 35px;
            height: 35px;
        }
    </style> --}}




</head>

<body class="am-bodywrap @if (!empty(setting('_general.enable_rtl')) || !empty(session()->get('rtl'))) am-rtl @endif">

    <x-front.header :page="$page ?? null" />
    {{-- <main class="am-main"> --}}
    <main>
        @yield('content')
        {{ $slot ?? '' }}
    </main>
    <x-popups />
    <x-front.footer :page="$page ?? null" />
    @if (session('impersonated_name'))
        <div class="am-impersonation-bar">
            <span>{{ __('general.impersonating') }} <strong>{{ session('impersonated_name') }}</strong></span>
            <a href="{{ route('exit-impersonate') }}" class="am-btn">{{ __('general.exit') }}</a>
        </div>
    @endif
    @livewireScripts()
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script defer src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script defer src="{{ asset('js/select2.min.js') }}"></script>
    <script defer src="{{ asset('js/splide.min.js') }}"></script>
    <script defer src="{{ asset('js/main.js') }}"></script>
    @stack('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            Livewire.on('remove-cart', (event) => {
                const currentRoute = '{{ request()->route()->getName() }}';

                const {
                    index,
                    cartable_id,
                    cartable_type
                } = event.params;
                if (currentRoute != 'tutor-detail') {
                    fetch('/remove-cart', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                index,
                                cartable_id,
                                cartable_type
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const event = new CustomEvent('cart-updated', {
                                    detail: {
                                        cart_data: data.cart_data,
                                        total: data.total,
                                        subTotal: data.subTotal,
                                        discount: data.discount,
                                        toggle_cart: data.toggle_cart
                                    }
                                });
                                window.dispatchEvent(event);
                            } else {
                                console.error('Failed to update cart:', data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                        });
                }
            });
        });
    </script>

    @if (!empty(setting('_scripts_styles.footer_scripts')))
        {!! setting('_scripts_styles.footer_scripts') !!}
    @endif
    {{-- <a href="https://wa.me/966581806122" class="whatsapp-float" target="_blank">
        <img src="https://cdn-icons-png.flaticon.com/512/733/733585.png" alt="WhatsApp" />
    </a> --}}

</body>

</html>
