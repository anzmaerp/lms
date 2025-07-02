<?php

use Livewire\Volt\Component;
use Diglactic\Breadcrumbs\Breadcrumbs;

new class extends Component
{

};
?>

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
<header class="am-header">
    {{ Breadcrumbs::render() }}
    <form class="am-header_form">
        <fieldset>
            <div class="form-group" @click="$dispatch('toggle-spotlight')">
                <i class="am-icon-search-02"></i>
                <input type="text" class="form-control" placeholder="{{ __('general.quick_search') }}">
                <span>{{ __('general.ctrl_k') }}</span>
            </div>
        </fieldset>
    </form>
    <div class="am-header_user">
        <x-frontend.user-menu />
    </div>
</header>