<div class="kd-allcoupons" wire:init='initData'>
    <div class="kd-title_wrap">
        <div class="kd-title">
            <h2>{{ __('kupondeal::kupondeal.all_coupons') }}</h2>
            <p>{{ __('kupondeal::kupondeal.all_coupons_description') }}</p>
        </div>
        <a href="javascript:void(0);" class="kd-btn kd-create-coupon" wire:click='openModal'
            wire:loading.class='kd-btn_disable' wire:target="openModal">
            {{ __('kupondeal::kupondeal.create_coupon') }}
            <i class="am-icon-plus-02" wire:loading.remove wire:target="openModal"></i>
        </a>
    </div>
    <div class="kd-allcoupons_filter">
        <div class="kd-allcoupons_search">
            <input type="text" wire:model.live.debounce.500ms='keyword'
                placeholder="{{ __('kupondeal::kupondeal.search_by_keyword') }}" class="form-control">
            <i class="am-icon-search-02"></i>
        </div>
        <div class="kd-allcoupons_tab">
            <a href="javascript:void(0);" @class(['kd-active' => $active_tab == 'active'])
                wire:click='changeTab("active")'>{{ __('kupondeal::kupondeal.active') }}</a>
            <a href="javascript:void(0);" @class(['kd-active' => $active_tab == 'inactive'])
                wire:click='changeTab("inactive")'>{{ __('kupondeal::kupondeal.inactive') }}</a>
        </div>
    </div>
    <div class="kd-allcoupons_wrap">
        <ul class="kd-allcoupons_list" wire:loading wire:target='keyword, initData, isLoading, active_tab, changeTab'
            wire:loading.grid>
            @include('kupondeal::livewire.kupon-list.skeleton')
        </ul>
        @if (!$isLoading)
            @if ($coupons->count() > 0)
                <ul wire:loading.remove wire:target='keyword, initData, isLoading, active_tab, changeTab'
                    class="kd-allcoupons_list">
                    @foreach ($coupons as $coupon)
                        <li>
                            <div class="kd-coupon_item">
                                {{-- Header --}}
                                <div class="kd-coupon_header" style="background: {{ $coupon->color }}">
                                    <div class="kd-coupon_header_discount">
                                        <span>
                                            @if ($coupon->discount_type == 'percentage')
                                                {{ $coupon->discount_value }}<sup>%</sup>
                                            @else
                                                {!! formatAmount($coupon->discount_value, true) !!}
                                            @endif
                                            <em>{{ __('kupondeal::kupondeal.off') }}</em>
                                        </span>
                                    </div>

                                    {{-- Titles --}}
                                    <div class="kd-coupon_titles_wrap">
                                        @php $ids = json_decode($coupon->couponable_id, true); @endphp

                                        @if ($coupon->couponable_type === Modules\Courses\Models\Course::class)
                                            @foreach ($ids as $id)
                                                @php $course = Modules\Courses\Models\Course::find($id); @endphp
                                                @if ($course)
                                                    <div class="kd-coupon_item_title">
                                                        <div class="kd-coupon_item_title_content">
                                                            <em>{{ $course->title }}</em>
                                                            <span
                                                                class="am-tooltip-text"><span>{{ $course->title }}</span></span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @else
                                            @foreach ($ids as $id)
                                                @php
                                                    $subjectGroup = \App\Models\UserSubjectGroupSubject::with(
                                                        'subject',
                                                    )->find($id);
                                                @endphp
                                                @if ($subjectGroup?->subject)
                                                    <div class="kd-coupon_item_title">
                                                        <div class="kd-coupon_item_title_content">
                                                            <em>{{ $subjectGroup->subject->name }}</em>
                                                            <span
                                                                class="am-tooltip-text"><span>{{ $subjectGroup->subject->name }}</span></span>
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        @endif
                                    </div>

                                    \ <div class="kd-itemdropdown">
                                        <a href="javascript:void(0);" id="kd-itemdropdown" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="am-icon-ellipsis-horizontal-02"></i>
                                        </a>
                                        <ul class="kd-itemdropdown_list dropdown-menu"
                                            aria-labelledby="dropdownMenuLink">
                                            <li>
                                                <a href="javascript:void(0);"
                                                    wire:click='editCoupon({{ $coupon->id }})'>
                                                    <i class="am-icon-pencil-02"></i>
                                                    {{ __('kupondeal::kupondeal.edit') }}
                                                </a>
                                            </li>
                                            <li>
                                                <a href="javascript:void(0);"
                                                    @click="$wire.dispatch('showConfirm', { id : {{ $coupon->id }}, action : 'delete-coupon' })">
                                                    <i class="am-icon-trash-02"></i>
                                                    {{ __('kupondeal::kupondeal.delete') }}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="kd-coupon_shape" style="border-color: {{ $coupon->color }};"></div>
                                </div>

                                <div class="kd-coupon_body"
                                    style="background-color: {{ addColorOpacity($coupon->color) }}">
                                    <div class="kd-coupon_body_content">
                                        <em>{{ __('kupondeal::kupondeal.promo_code') }}</em>
                                        <h3 x-data="{ copied: false, textToCopy: '{{ $coupon->code }}' }">
                                            {{ $coupon->code }}
                                            <a href="javascript:void(0);"
                                                @click="navigator.clipboard.writeText(textToCopy).then(() => { copied = true; setTimeout(() => copied = false, 2000) }).catch(() => {})">
                                                <i class="am-icon-copy-01"></i>
                                            </a>
                                            <template x-if="copied">
                                                <span x-show="copied" x-transition>{{ __('general.copied') }}</span>
                                            </template>
                                        </h3>
                                        @if ($coupon->description)
                                            <span>
                                                {{ __('kupondeal::kupondeal.description') }}:
                                                {{ $coupon->description }}
                                            </span>
                                        @endif
                                        <span>
                                            {{ __('kupondeal::kupondeal.valid_until') }}:
                                            {{ date('d M Y', strtotime($coupon->expiry_date)) }}
                                        </span>
                                        <span>{{ __('kupondeal::kupondeal.created_by') }}:
                                            {{ $coupon->user->profile?->first_name }}
                                            {{ $coupon->user->profile?->last_name }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                {{ $coupons->links('kupondeal::pagination.pagination') }}
            @else
                <div wire:loading.remove wire:target='keyword, initData, isLoading, active_tab, changeTab'
                    class="kd-couponsempty">
                    <div class="kd-couponsempty_container">
                        <figure>
                            <img src="{{ asset('modules/kupondeal/images/empty-coupon.png') }}" width="148"
                                height="122" alt="empty-view">
                        </figure>
                        <h3>{{ __('kupondeal::kupondeal.no_coupons_found') }}</h3>
                        <p>{{ __('kupondeal::kupondeal.no_coupons_found_desc') }}</p>
                    </div>
                </div>
            @endif
        @endif
    </div>
    @include('kupondeal::livewire.kupon-list.card')
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/kupondeal/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/kupondeal/css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/kupondeal/css/jquery.colorpicker.bygiro.css') }}">
    @vite(['public/css/flatpicker.css', 'public/css/flatpicker-month-year-plugin.css'])
@endpush


@push('scripts')
    <script defer src="{{ asset('js/flatpicker.js') }}"></script>
    <script defer src="{{ asset('js/flatpicker-month-year-plugin.js') }}"></script>
    <script defer src="{{ asset('modules/kupondeal/js/jquery.colorpicker.bygiro.js') }}"></script>
    <script>
        window.addEventListener('onEditCoupon', (event) => {
            let {
                discount_type,
                discount_value,
                expiry_date,
                couponable_id,
                couponable_type,
                color,
                condition_type,
                optionList
            } = event.detail;
            Livewire.dispatch('initSelect2', {
                target: '.am-select2',
                timeOut: 0
            });
            initializeDatePicker();
            jQuery('#discount_type').val(discount_type).trigger('change');
            jQuery('#discount_value').val(discount_value);
            jQuery('#condition_type').val(condition_type);
            jQuery('#expiry_date').val(expiry_date);
            jQuery('#couponable_type').val(couponable_type).trigger('change');
            jQuery('.kd-colorpicker input').val(color);
            $('.myColorPicker-preview').css('background-color', color);
            jQuery('#couponable_id').attr('disabled', true);
            initOptionList(optionList);
            initColorPicker(color)
            $('#kd-create-coupon').modal('show');
        });

        window.addEventListener('createCoupon', (event) => {
            let {
                color = '#000000'
            } = event.detail;
            Livewire.dispatch('initSelect2', {
                target: '.am-select2',
                timeOut: 0
            });
            jQuery('#discount_type').val(null).trigger('change');
            jQuery('#discount_value').val('');
            jQuery('#expiry_date').val('');
            jQuery('#couponable_id').val('').trigger('change');
            jQuery('#couponable_id').attr('disabled', true);
            jQuery('#couponable_type').val('').trigger('change');
            initializeDatePicker();
            initColorPicker(color)
            $('#kd-create-coupon').modal('show');
        });

        function initColorPicker(color) {
            setTimeout(() => {
                let $colorPicker = $('.myColorPicker').colorPickerByGiro({
                    preview: '.myColorPicker-preview',
                    text: {
                        close: 'Confirm',
                        none: 'None',
                    },
                    options: {
                        defaultColor: color
                    },
                });

                $('.myColorPicker-preview').css('background-color', color);
                jQuery('.kd-colorpicker input').val(color).trigger('change');
                setTimeout(() => {
                    $('.myColorPicker').find('.colorPicker--preview').css('background-color', color);
                    $('.myColorPicker').find('.colorPicker input').val(color);
                }, 50);
            }, 100);
        }

        window.addEventListener('couponableValuesUpdated', (event) => {
            let {
                options,
                reset
            } = event.detail;
            initOptionList(options);
            if (reset) {
                jQuery('#couponable_id').val('').trigger('change');
            }
        });

        function initOptionList(options) {
            let $select = jQuery('#couponable_id');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy').empty();
            }
            $select.select2({
                data: [{
                    id: '', // the value of the option
                    text: 'Select an option'
                }, ...options],
                theme: 'default',
                disabled: false
            });
        }

        jQuery(document).on('click', '.colorPicker input, .colorPicker--preview', function(evt) {
            let _this = $(this);
            jQuery('.cp-cont').find('.cpBG').not(_this.parents('.cp-cont').find('.cpBG')).removeClass('in').css(
                'display', 'none');
        });

        jQuery(document).on('contextmenu', '.colorPicker input', function(evt) {
            let _this = $(this);
            jQuery('.cp-cont').find('.cpBG').not(_this.parents('.cp-cont').find('.cpBG')).removeClass('in').css(
                'display', 'none');
        });

        jQuery(document).on('change', '.kd-colorpicker input', function() {
            let getcolor = jQuery(this).val();
            let setcolor = jQuery('.colorPicker--preview')
            jQuery(setcolor).css("background-color", getcolor);
            @this.set('form.color', getcolor);

        });

        jQuery(document).on('click', 'body', function(e) {
            if ($(e.target).closest('.cp-cont').length) {
                return;
            }
            jQuery('.cp-cont')
                .find('.cpBG')
                .removeClass('in')
                .css('display', 'none');
        });
    </script>
@endpush
