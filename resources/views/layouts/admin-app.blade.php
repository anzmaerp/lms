<!doctype html>
<html class="no-js" lang="{{ str_replace('_', '-', app()->getLocale()) }}"
    @if (!empty(setting('_general.enable_rtl')) || !empty(session()->get('rtl'))) dir="rtl" @endif>
@php
    $info = Auth::user();
    $siteTitle = setting('_general.site_name') ?: env('APP_NAME');
    $translatedLangs = getTranslatedLanguages();
    $selectedLang = app()->getLocale() ?? 'en';
    $fontSize = setting('_general.font_size') ?? 16;
    $fontWeight = setting('_general.font_weight') ?? 400;

@endphp

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title> {{ __('general.adminpanel_title') }} | {{ $siteTitle }}</title>
    <x-favicon />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+JP&display=swap" rel="stylesheet">
    <style>
        *:not(i) {
            font-family: 'IBM Plex Sans JP', sans-serif !important;

        }
header * {
    font-size: {{ $fontSize }}px !important;
    font-weight: {{ $fontWeight }} !important;
}
    </style>
    @vite(['public/css/bootstrap.min.css', 'public/admin/css/themify-icons.css', 'public/admin/css/fontawesome/all.min.css', 'public/css/select2.min.css', 'public/css/mCustomScrollbar.min.css', 'public/admin/css/feather-icons.css'])
    @stack('styles')
    <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}">
    @if (!empty(setting('_general.enable_rtl')) || !empty(session()->get('rtl')))
        <link rel="stylesheet" type="text/css" href="{{ asset('admin/css/rtl.css') }}"
        @if (\Nwidart\Modules\Facades\Module::has('forumwise') && \Nwidart\Modules\Facades\Module::isEnabled('forumwise')) <link rel="stylesheet" type="text/css" href="{{ asset('modules/forumwise/css/rtl.css') }}"> @endif
    @endif
    @livewireStyles
</head>

<body class="tb-bodycolor @if (!empty(setting('_general.enable_rtl')) || !empty(session()->get('rtl'))) am-rtl @endif">
    <div class="tb-mainwrapper">
        <livewire:admin.sidebar />
        <div class="tb-subwrapper">
            <header class="am-header">
                {{ Breadcrumbs::render() }}
                <div class="gap-4 tb-dropdoenwrap d-flex align-items-center justify-content-end">
                    <div class="dropdown notifications language">
                        <button
                            class="p-0 border-0 btn btn-light dropdown-toggle d-flex align-items-center justify-content-center"
                            type="button" id="langDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                            style="width: 32px; height: 32px; border-radius: 6px;">
                            <i class="ti-world fs-6 text-secondary"></i>
                        </button>

                        <div class="p-0 border-0 shadow-sm dropdown-menu dropdown-menu-end"
                            aria-labelledby="langDropdown" style="min-width: 180px;">
                            <div class="px-3 py-2 border-bottom bg-light">
                                <span
                                    class="text-center fw-semibold fs-7 text-muted d-block">{{ __('Choose Language') }}</span>
                            </div>

                            <div class="px-2 py-1" style="max-height: 200px; overflow-y: auto;">
                                @foreach ($translatedLangs as $locale => $langName)
                                    <form action="{{ route('switch-lang') }}" method="POST" class="w-100">
                                        @csrf
                                        <input type="hidden" name="am-locale" value="{{ $locale }}">
                                        <button type="submit"
                                            class="dropdown-item w-100 d-flex align-items-center gap-2 py-2 px-2 text-start border-0 bg-transparent {{ $selectedLang == $locale ? 'disabled text-muted' : '' }}">
                                            <img src="{{ getLangFlag($locale) }}" alt="{{ $langName }}"
                                                style="width: 18px; height: 12px;">
                                            <span
                                                class="flex-grow-1 text-secondary fw-medium fs-7">{{ $langName }}</span>
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gap-4 tb-dropdoenwrap d-flex align-items-center justify-content-end">
                    <div class="px-3 tb-dropdoenwrap d-flex align-items-center justify-content-between w-100">
                        <div class="tb-logowrapper tb-icontoggler">
                            @if (!empty($info))
                                <div class="tb-adminheadwrap">
                                    <strong class="tb-adminhead__img" id="adminImage">
                                        @if (!empty($info->profile?->image) && Storage::disk(getStorageDisk())->exists($info->profile?->image))
                                            <img src="{{ resizedImage($info->profile?->image, 34, 34) }}"
                                                alt="{{ $info->profile?->short_name }}" />
                                        @else
                                            <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 34, 34) }}"
                                                alt="{{ $info->profile?->image }}">
                                        @endif
                                    </strong>
                                </div>
                            @endif
                            <ul class="tb-dropdownlist">
                                <li>
                                    <div class="tb-dropdownmenu-inner">
                                        <strong class="tb-adminhead__img" id="adminImage">
                                            @if (!empty($info->profile?->image) && Storage::disk(getStorageDisk())->exists($info->profile?->image))
                                                <img src="{{ resizedImage($info->profile?->image, 34, 34) }}"
                                                    alt="{{ $info->profile?->image }}" />
                                            @else
                                                <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 34, 34) }}"
                                                    alt="{{ $info->profile?->image }}">
                                            @endif
                                        </strong>
                                        <div class="tb-adminuserinfo">
                                            <h6>{{ $info->profile?->full_name }}</h6>
                                            <span>{{ __('general.active_status') }}</span>
                                        </div>
                                    </div>
                                </li>
                                <li>
                                    <a href="{{ route('admin.profile') }}">
                                        <i class="icon-user"></i> {{ __('sidebar.profile') }}
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ url('/') }}" target="_blank">
                                        <i class="ti-new-window"></i> {{ __('general.view_site') }}
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('admin.clear-cache') }}">
                                        <i class="ti-brush"></i> {{ __('sidebar.clear-cache') }}
                                    </a>
                                </li>

                                <li>
                                    <a href="{{ route('logout') }}">
                                        <i class="ti-power-off"></i> {{ __('sidebar.logout') }}
                                    </a>
                                </li>
                            </ul>
                        </div>

                    </div>

            </header>

            <div class="tb-adminwrapper">
                <div class="tb-db-dashboard_box">
                    <div class="tb-db-dashboard_box_wrap">
                        <div class="tb-db-dashboard_box_wrap_inner">
                            <div class="tb-menumanagement_wrap">
                                @yield('content')
                                @if (!empty($slot))
                                    {{ $slot }}
                                @endif
                                <x-admin.footer />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <x-admin.popups />
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            jQuery(document).on("click", '.update-section-settings', function(event) {
                let _this = jQuery(this);
                let smtpSettingsUrl =
                    "{{ !isDemoSite() && Route::has('admin.update-smtp-settings') ? route('admin.update-smtp-settings') : null }}";
                let broadcastingSettingsUrl =
                    "{{ !isDemoSite() && Route::has('admin.update-broadcasting-settings') ? route('admin.update-broadcasting-settings') : null }}";
                let pusherSettingsUrl =
                    "{{ !isDemoSite() && Route::has('admin.update-pusher-settings') ? route('admin.update-pusher-settings') : null }}";
                let reverbSettingsUrl =
                    "{{ !isDemoSite() && Route::has('admin.update-reverb-settings') ? route('admin.update-reverb-settings') : null }}";

                if (smtpSettingsUrl && $('#smtp_setting').hasClass('active')) {
                    setTimeout(function() {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr(
                                    'content')
                            }
                        });
                        $.ajax({
                            url: smtpSettingsUrl,
                            method: 'post',
                            success: function(data) {
                                _this.find('.spinner-border').addClass('d-none');
                                showAlert({
                                    message: data.message,
                                    type: data.success ? 'success' : 'error',
                                    title: data.success ?
                                        @json(__('admin/general.success')) :
                                        @json(__('admin/general.error')),
                                    autoclose: 4000,
                                });
                            }
                        });
                    }, 1000);
                }

                if (broadcastingSettingsUrl && $('#_broadcasting_setting').hasClass('active')) {
                    setTimeout(function() {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr(
                                    'content')
                            }
                        });
                        $.ajax({
                            url: broadcastingSettingsUrl,
                            method: 'post',
                            success: function(data) {
                                _this.find('.spinner-border').addClass('d-none');
                                showAlert({
                                    message: data.message,
                                    type: data.success ? 'success' : 'error',
                                    title: data.success ?
                                        @json(__('admin/general.success')) :
                                        @json(__('admin/general.error')),
                                    autoclose: 4000,
                                });
                            }
                        });
                    }, 1000);
                }

                if (pusherSettingsUrl && $('#_broadcasting_setting').hasClass('active')) {
                    setTimeout(function() {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr(
                                    'content')
                            }
                        });
                        $.ajax({
                            url: pusherSettingsUrl,
                            method: 'post',
                            success: function(data) {
                                _this.find('.spinner-border').addClass('d-none');
                                showAlert({
                                    message: data.message,
                                    type: data.success ? 'success' : 'error',
                                    title: data.success ?
                                        @json(__('admin/general.success')) :
                                        @json(__('admin/general.error')),
                                    autoclose: 4000,
                                });
                            }
                        });
                    }, 1000);
                }

                if (reverbSettingsUrl && $('#_broadcasting_setting').hasClass('active')) {
                    setTimeout(function() {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr(
                                    'content')
                            }
                        });
                        $.ajax({
                            url: reverbSettingsUrl,
                            method: 'post',
                            success: function(data) {
                                _this.find('.spinner-border').addClass('d-none');
                                showAlert({
                                    message: data.message,
                                    type: data.success ? 'success' : 'error',
                                    title: data.success ?
                                        @json(__('admin/general.success')) :
                                        @json(__('admin/general.error')),
                                    autoclose: 4000,
                                });
                            }
                        });
                    }, 1000);
                }

                let s3SettignsUrl =
                    `{{ !isDemoSite() &&
                    \Nwidart\Modules\Facades\Module::has('s3konnect') &&
                    \Nwidart\Modules\Facades\Module::isEnabled('s3konnect') &&
                    Route::has('s3konnect.settings')
                        ? route('s3konnect.settings')
                        : null }}`;
                if (jQuery('#_storage').hasClass('active') && s3SettignsUrl) {
                    setTimeout(function() {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr(
                                    'content')
                            }
                        });
                        $.ajax({
                            url: s3SettignsUrl,
                            method: 'post',
                            success: function(data) {
                                _this.find('.spinner-border').addClass('d-none');
                                showAlert({
                                    message: data.message,
                                    type: data.success ? 'success' : 'error',
                                    title: data.success ?
                                        @json(__('s3konnect::s3konnect.success')) :
                                        @json(__('s3konnect::s3konnect.error')),
                                    autoclose: 4000,
                                    redirectUrl: data.success ? location.href :
                                        false,
                                });
                            }
                        });
                    }, 1000);
                }

                if (jQuery('#social_login-tab').hasClass('active')) {
                    setTimeout(function() {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr(
                                    'content')
                            }
                        });
                        $.ajax({
                            url: `{{ route('admin.update-social-login-settings') }}`,
                            method: 'post',
                            success: function(data) {
                                _this.find('.spinner-border').addClass('d-none');
                                showAlert({
                                    title: data.success ?
                                        @json(__('admin/general.success_title')) :
                                        @json(__('admin/general.error_title')),
                                    message: data.message,
                                    type: data.success ? 'success' : 'error',
                                    autoclose: 4000,
                                    redirectUrl: data.success ? location.href :
                                        false,
                                });
                            }
                        });
                    }, 1000);
                }

                if (jQuery(this).attr('data-form') != '_theme-form') {
                    return false;
                }

                upadateSassStyle(_this);

            });
            jQuery(document).on("click", '.btn.btn-danger', function(event) {
                if ($('#_theme').hasClass('active')) {
                    setTimeout(function() {
                        upadateSassStyle(jQuery('.reset-section-settings'), redirect = location
                            .href, );
                    }, 300);
                }
            });
        });

        function upadateSassStyle(_this, redirect = false) {
            setTimeout(function() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: "{{ url('admin/update-sass-style') }}",
                    method: 'post',
                    success: function(data) {
                        _this.find('.spinner-border').addClass('d-none');
                        showAlert({
                            message: data.message,
                            type: data.success ? 'success' : 'error',
                            title: data.success ? @json(__('admin/general.success')) :
                                @json(__('admin/general.error')),
                            autoclose: 4000,
                            redirectUrl: redirect,
                        });
                    }
                });
            }, 300);
        }
    </script>
    @livewireScripts
    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script defer src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script defer src="{{ asset('js/select2.min.js') }} "></script>
    <script defer src="{{ asset('js/mCustomScrollbar.min.js') }}"></script>
    <script defer src="{{ asset('js/main.js') }}"></script>
    <script defer src="{{ asset('js/admin-app.js') }}"></script>
    @if (session('success'))
        <script>
            setTimeout(function() {
                showAlert({
                    message: "{{ session('success') }}",
                    type: 'success',
                    title: @json(__('admin/general.success')),
                    autoclose: 4000,
                });
            }, 100);
        </script>
    @endif
    @stack('scripts')
</body>

</html>
