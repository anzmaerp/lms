@props(['page' => null])
@php
    $headerVariations = setting('_front_page_settings.header_variation_for_pages');
    $headerVariation = '';
    if (!empty($headerVariations)) {
        foreach ($headerVariations as $key => $variation) {
            if ($variation['page_id'] == $page?->id) {
                $headerVariation = $variation['header_variation'];
                break;
            }
        }
    }

    $googleFont = setting('_general.google_font') ?? 'Roboto';
    $fontSize = setting('__general.font_size') ?? 16;
    $fontWeight = setting('__general.font_weight') ?? 400;
    $searchBox = setting('_general.search_box');
    $theme_pri_color = setting('_theme.theme_pri_color');
    $theme_sec_color = setting('_theme.theme_sec_color');
@endphp
<div>



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

            header .am-navigation a,
            header .navbar-nav a {
                color: {{ $theme_pri_color ?? '#000' }} !important;
                background-color: transparent !important;
            }

            .am-sidebar-menu a {
                display: block !important;
                padding: 10px 15px;
                color: {{ $theme_pri_color ?? '#000' }} !important;
                background-color: transparent !important;
                transition: all 0.3s ease;
                border-radius: 5px;
            }

            header .am-navigation a,
            header .am-sidebar-menu a,
            header .navbar-nav a {
                color: {{ $theme_pri_color ?? '#000' }} !important;
                transition: all 0.3s ease;
            }

            .am-sidebar-menu a:hover,
            .am-navigation a:hover,
            .navbar-nav a:hover {
                background-color: {{ $theme_sec_color ?? '#f00' }} !important;
            }
        </style>
    @endif

    @if ($headerVariation == 'am-header_four')
        <header class="am-header_four">

            <div class="container">
                <div class="row m-2">
                    <div class="col-12">
                        <div class="am-header_two_wrap am-header-bg">
                            <strong class="am-logo">
                                <x-application-logo />
                            </strong>
                            <nav class="am-navigation am-navigation-four navbar-expand-xxl">

                                <div class="am-navbar-toggler">
                                    <div class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#tenavbar"
                                        aria-expanded="false" aria-label="Toggle navigation" role="button">
                                    </div>
                                    <input type="checkbox" id="checkbox">
                                    <label for="checkbox" class="toggler-menu">
                                        <span class="menu-bars" id="menu-bar1"></span>
                                        <span class="menu-bars" id="menu-bar2"></span>
                                        <span class="menu-bars" id="menu-bar3"></span>
                                    </label>
                                </div>
                                <ul id="tenavbar" class="collapse navbar-collapse">
                                    @if (!empty(getMenu('header')))
                                        @foreach (getMenu('header') as $item)
                                            <x-menu-item :menu="$item" />
                                        @endforeach
                                    @endif
                                </ul>
                            </nav>
                            @auth
                                <x-frontend.user-menu />
                            @endauth
                            @guest
                                <div class="am-loginbtns">
                                    <x-multi-currency />
                                    <x-multi-lingual />
                                    <a href="{{ route('login') }}" class="am-white-btn">{{ __('general.login') }}</a>
                                    <a href="{{ route('register') }}" class="am-btn">{{ __('general.get_started') }}</a>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </header>
    @elseif ($headerVariation == 'am-header_seven')
        <header class="am-header-six">
            <div class="container">
                <div class="row m-2">
                    <div class="col-12">
                        <div class="am-header_two_wrap">
                            <strong class="am-logo">
                                <x-application-logo />
                            </strong>
                            <div class="am-loginbtns">
                                @guest
                                    <a href="{{ route('login') }}" class="am-btn">{{ __('general.login') }}</a>
                                @endguest
                                <button type="button" class="navbar-toggler am-menubtn" data-bs-toggle="offcanvas"
                                    data-bs-target="#offcanvasNavbar"
                                    aria-controls="offcanvasNavbar">{{ __('general.menu') }}
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                            viewBox="0 0 17 17" fill="none">
                                            <path
                                                d="M2.93359 14.0283H14.9336M8.26693 8.695H14.9336M2.93359 3.36166H14.9336"
                                                stroke="white" stroke-width="1.33333" stroke-linecap="round"
                                                stroke-linejoin="round" />
                                        </svg>
                                    </span>
                                </button>
                                @auth
                                    <x-frontend.user-menu :multiLang="false" />
                                @endauth
                                <div class="am-sidebar-menu offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar"
                                    aria-labelledby="offcanvasNavbarLabel">
                                    <div class="offcanvas-header">
                                        <strong class="am-logo">
                                            <a href="#">
                                                <img src="{{ asset('demo-content/logo-white.svg') }}" alt="">
                                            </a>
                                        </strong>
                                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                            aria-label="Close">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none">
                                                <path d="M6 18L18 6M6 6L18 18" stroke="white" stroke-width="1.5"
                                                    stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="offcanvas-body">
                                        <ul class="navbar-nav flex-grow-1">
                                            @if (!empty(getMenu('header')))
                                                @foreach (getMenu('header') as $item)
                                                    <x-menu-item :menu="$item" :enableToggle="true" />
                                                @endforeach
                                            @endif
                                            @guest
                                                <x-multi-currency />
                                                <x-multi-lingual />
                                            @endguest
                                        </ul>
                                        @guest
                                            <div class="am-btns">
                                                <a href="{{ route('login') }}"
                                                    class="am-btn am-joinnow-btn">{{ __('general.login') }}</a>
                                                <a href="{{ route('register') }}"
                                                    class="am-btn">{{ __('general.get_started') }}</a>
                                            </div>
                                        @endguest
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    @else
        <header @class([
            'am-header_two',
            $headerVariation,
            'am-header-bg' =>
                (empty($page) &&
                    !in_array(request()->route()->getName(), [
                        'find-tutors',
                        'tutor-detail',
                    ])) ||
                in_array($page?->slug, [
                    'about-us',
                    'how-it-works',
                    'faq',
                    'terms-condition',
                    'privacy-policy',
                ]),
        ])>
            <div class="row m-2">
                <div class="col-12">
                    <div class="am-header_two_wrap">
                        <strong class="am-logo">
                            <x-application-logo />
                        </strong>


                        <nav class="am-navigation navbar-expand-xl">
                            <div class="am-navbar-toggler">
                                <div class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#tenavbar"
                                    aria-expanded="false" aria-label="Toggle navigation" role="button">
                                </div>
                                <input type="checkbox" id="checkbox">
                                <label for="checkbox" class="toggler-menu">
                                    <span class="menu-bars" id="menu-bar1"></span>
                                    <span class="menu-bars" id="menu-bar2"></span>
                                    <span class="menu-bars" id="menu-bar3"></span>
                                </label>
                            </div>
                            <ul id="tenavbar" class="collapse navbar-collapse">
                                @if (!empty(getMenu('header')))
                                    @foreach (getMenu('header') as $item)
                                        <x-menu-item :menu="$item" />
                                    @endforeach
                                @endif
                            </ul>
                        </nav>
                        @if($searchBox)
                        <div class="navbar-search-area" style="margin-inline-start: auto; max-width: 600px;">
                            <form method="GET" action="{{  route('search')  }}">
                                <div
                                    style="display: flex; align-items: center; border-radius: 28px; overflow: hidden; border: 1px solid #ddd; height: 44px; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.08);">
                                    <input type="search" name="filters[keyword]"
                                        placeholder="{{ __('settings')['search_tutor_course'] }}"
                                        style="flex: 1; font-size: 15px; padding: 0 16px; border: none; outline: none; background: transparent; color: #333;">

                                    <select name="type"
                                        style="width: 100px; font-size: 13px; height: 100%; border: none; background: #f9f9f9; border-left: 1px solid #eee; padding: 0 10px; outline: none; color: {{ request('category') == 'courses' ? $theme_pri_color ?? '#007bff' : '#333' }}; cursor: pointer;">
                                        <option value="tutors" style="color: {{ $theme_pri_color ?? '#007bff' }};">{{__('courses::courses.lecture')}}</option>
                                        <option value="courses" style="color: {{ $theme_pri_color ?? '#007bff' }};">{{__('courses::courses.courses')}}</option>
                                    </select>

                                    <button type="submit"
                                        style="background: {{ $theme_pri_color ?? '#007bff' }}; color: #fff; border: none; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s ease;"
                                        title="Search">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                            fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                            <path
                                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85zm-5.242 1.656a5.5 5.5 0 1 1 0-11 5.5 5.5 0 0 1 0 11z" />
                                        </svg>
                                    </button>
                                </div>
                            </form>
                        </div>
                        @endif

                        @auth
                            <x-frontend.user-menu />
                        @endauth
                        @guest
                            <div class="am-loginbtns">
                                <x-multi-currency />
                                <x-multi-lingual />
                                <a href="{{ route('login') }}" class="am-btn">{{ __('general.login') }}</a>
                                <a href="{{ route('register') }}"
                                    class="am-white-btn">{{ __('general.get_started') }}</a>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>

        </header>
    @endif
</div>
