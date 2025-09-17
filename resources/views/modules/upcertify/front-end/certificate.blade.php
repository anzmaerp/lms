@extends('upcertify::layouts.app')
@php
    use Carbon\Carbon;
    use Endroid\QrCode\Builder\Builder;
    use Endroid\QrCode\Writer\PngWriter;

    $qrBuilder = new Builder(
        writer: new PngWriter(),
        data: str_replace('/printPdf', '/printView', url()->full()),
        size: 120,
        margin: 0,
    );

    $qrCode = $qrBuilder->build();
    $qrCodeBase64 = base64_encode($qrCode->getString());
@endphp

@push(config('upcertify.style_stack'))
    @if(config('upcertify.livewire_styles'))
        @livewireStyles()
    @endif
    <link rel="stylesheet" href="{{ asset('modules/upcertify/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/upcertify/css/fonts.css') }}">

    @if(!empty($body['fontFamilies']))
        @php
            $fontSet = $body['fontFamilies'];
            $families = collect($fontSet)->map(function ($font) {
                return "family=" . urlencode($font) . ":ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900";
            })->join('&');
            $linkHref = "https://fonts.googleapis.com/css2?{$families}&display=swap";
        @endphp

        <link id="uc-custom-fonts" type="text/css" rel="stylesheet" href="{{ $linkHref }}">
    @endif
@endpush

@section(config('upcertify.content_yeild'))
    @php
        $certificateUrl = url()->current();
        $emailSubject = rawurlencode('Check Out My Certificate');
        $emailBody = rawurlencode("I wanted to share my recent achievement with you. You can view my certificate using the following link: $certificateUrl");
        $emailShareLink = "mailto:?subject={$emailSubject}&body={$emailBody}";
    @endphp
    <div class="uc-certificateprint">
        <x-upcertify::body :body="$body" :isPreview="true" />

        <div class="uc-certificateprint_footer">
            <div class="uc-shareoptions">
                <a href="javascript:void(0)" class="uc-share-btn">{{ __('upcertify::upcertify.share') }}</a>
                <ul class="uc-shareoptions_list">
                    <li><a href="{{ $emailShareLink }}">{{ __('upcertify::upcertify.share_via_email') }}</a></li>
                    <li><a href="javascript:void(0);" onclick="copyLink()">{{ __('upcertify::upcertify.copy_link') }}</a>
                    </li>
                </ul>
            </div>
            <a href="{{ route('upcertify.download', $uid) }}" class="uc-btn uc-download-btn">
                {{ __('upcertify::upcertify.download') }}
                <i><x-upcertify::icons.download /></i>
            </a>
        </div>
    </div>
@endsection

@push(config('upcertify.script_stack'))
    <script defer src="{{ asset('modules/upcertify/js/jquery.min.js') }}"></script>
    <script>
        window.qrCodeBase64 = "data:image/png;base64,{{ $qrCodeBase64 }}";

        window.addEventListener('DOMContentLoaded', function () {
            let bodyContainer = document.getElementById("uc-canvas-boundry");
            if (bodyContainer && window.qrCodeBase64) {
                let qrWrapper = document.createElement("div");
                qrWrapper.style.position = "absolute";
                qrWrapper.style.top = "60px";
                qrWrapper.style.left = "60px";

                let img = document.createElement("img");
                img.src = window.qrCodeBase64;
                img.alt = "QR Code";
                img.style.width = "120px";
                img.style.height = "120px";
                img.style.border = "1px solid #ccc";
                img.style.padding = "4px";
                img.style.borderRadius = "8px";

                qrWrapper.appendChild(img);
                bodyContainer.appendChild(qrWrapper);
            }

            jQuery(document).on('click', '.uc-share-btn', function (e) {
                e.stopPropagation();
                jQuery('.uc-shareoptions_list').slideToggle();
            });

            jQuery(document).on('click', function (e) {
                if (!jQuery(e.target).closest('.uc-shareoptions').length) {
                    jQuery('.uc-shareoptions_list').slideUp();
                }
            });
        });
        function copyLink() {
            navigator.clipboard.writeText(window.location.href);
            showToast('success', 'Link copied to clipboard');
        }
    </script>
@endpush