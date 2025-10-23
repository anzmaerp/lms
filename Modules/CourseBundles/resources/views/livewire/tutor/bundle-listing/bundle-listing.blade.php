<div class="cr-bundles" wire:init="loadData">
    <div class="am-title_wrap">
        <div class="am-title">
            <h2>{{ __('coursebundles::bundles.course_bundles') }}</h2>
            <p>{{ __('coursebundles::bundles.course_bundles_desc') }}</p>
        </div>
        @if ($bundles->isNotEmpty() || !empty($filters))
            <div class="cr-bundlesearch_header">
                <div class="cr-bundles_search">
                    <input wire:model.live.debounce.500ms="filters.keyword" type="text" class="form-control"
                        placeholder="{{ __('coursebundles::bundles.search_by_keyword') }}">
                    <i class="am-icon-search-02"></i>
                </div>
                <div class="am-slots_wrap">
                    <ul class="am-category-slots">
                        <li>
                            <button {{ ($filters['status'] ?? '') == '' ? 'class=active' : '' }}
                                wire:click="filterStatus('')">
                                {{ __('coursebundles::bundles.all') }}
                            </button>
                        </li>
                        @foreach ($statuses as $status => $key)
                            <li>
                                <button {{ ($filters['status'] ?? '') == $key ? 'class=active' : '' }}
                                    wire:click="filterStatus('{{ $key }}')">
                                    {{ __('coursebundles::bundles.' . $status) }}
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="am-_btn_wrap">
                    <a href="{{ route('coursebundles.tutor.create-course-bundle') }}" class="am-btn">
                        {{ __('coursebundles::bundles.create_a_new_bundle') }}
                        <i class="am-icon-plus-02"></i>
                    </a>
                </div>
            </div>
        @endIf
    </div>
    @if ($isLoading)
        <div>
            @include('coursebundles::skeletons.courses-bundle-listing', ['total' => $perPage])
        </div>
    @else
        <div class="d-none tutors-skeleton" wire:target="filters, loadData,filterStatus,perPage"
            wire:loading.class.remove="d-none">
            @include('coursebundles::skeletons.courses-bundle-listing', ['total' => $perPage])
        </div>
        <div class="cr-bundles_wrap" wire:loading.class="d-none" wire:target="filters, loadData,filterStatus,perPage">
            @if ($bundles->isNotEmpty())
                <ul>
                    @foreach ($bundles as $bundle)
                        <li>
                            <div class="cr-bundles_item">
                                <figure>
                                    <img src="{{ !empty($bundle?->thumbnail?->path) ? url(Storage::url($bundle?->thumbnail?->path)) : asset('demo-content/placeholders/placeholder.png') }}"
                                        alt="{{ $bundle?->title }}" />
                                    <figcaption>
                                        <span class="cr-status">
                                            <span style="background-color: {{ $bundle->status_color ?? '#008000' }};"
                                                class="cr-dot active"></span>
                                            {{ __('coursebundles::bundles.' . $bundle->status) }}
                                        </span>
                                    </figcaption>
                                </figure>
                                <div class="cr-bundles_item_content">
                                    <div class="cr-bundles_coursename">
                                        <div class="cr-bundles_coursetitle">
                                            <a href="{{ route('coursebundles.bundle-details', ['slug' => $bundle->slug]) }}">
                                                <h3>{{ $bundle?->title ?? '' }}</h3>
                                            </a>
                                        </div>

                                        <div class="mt-2 am-itemdropdown">
                                            <a href="javascript:void(0);" id="am-itemdropdown" data-bs-toggle="dropdown"
                                                aria-haspopup="true" aria-expanded="false">
                                                <i class="am-icon-ellipsis-vertical-02"></i>
                                            </a>
                                            <ul class="am-itemdropdown_list dropdown-menu"
                                                aria-labelledby="dropdownMenuLink">
                                                <li>
                                                    <a
                                                        href="{{ route('coursebundles.bundle-details', ['slug' => $bundle->slug]) }}">
                                                        <i class="am-icon-eye-open-01"></i>
                                                        {{ __('coursebundles::bundles.view_bundle') }}
                                                    </a>
                                                </li>


                                                @if ($bundle?->status == 'draft')
                                                    <li>
                                                        <a
                                                            href="{{ route('coursebundles.tutor.edit-course-bundle', ['id' => $bundle->id]) }}">
                                                            <i class="am-icon-pencil-02"></i>
                                                            {{ __('coursebundles::bundles.edit_bundle') }}
                                                        </a>
                                                    </li>
                                                    <li @click="$wire.dispatch('showConfirm', { id: {{ $bundle->id }}, action: 'delete-bundle'})">
                                                        <a href="javascript:void(0);">
                                                            <i class="am-icon-trash-02"></i>
                                                            {{ __('coursebundles::bundles.delete_bundle') }}
                                                        </a>
                                                    </li>
                                                @endif

                                                @if ($bundle?->status == 'archived' || $bundle?->status == 'draft')
                                                    <li wire:click="openPublishModal({{ $bundle->id }})">
                                                        <a href="javascript:void(0);">
                                                            <i class="am-icon-check-circle03"></i>
                                                            {{ __('coursebundles::bundles.publish_bundle') }}
                                                        </a>
                                                    </li>
                                                @endif

                                                @if ($bundle?->status == 'published')
                                                    <li
                                                        @click="$wire.dispatch('showConfirm', { id: {{ $bundle->id }}, action: 'archive-bundle' })">
                                                        <a href="#">
                                                            <i class="am-icon-archive-01"></i>
                                                            {{ __('coursebundles::bundles.archive_bundle') }}
                                                        </a>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>

                                    <ul class="cr-bundles_item_footer">

                                        <li>
                                            <span>
                                                <i class="am-icon-book-1"></i>
                                                {{ __('coursebundles::bundles.Courses') }}
                                            </span>
                                            <em>{{ $bundle?->courses_count }}</em>
                                        </li>
                                        <li>
                                            <span>
                                                <i class="am-icon-calender-day"></i>
                                                {{ __('coursebundles::bundles.created_at') }}
                                            </span>
                                            <em>{{ \Carbon\Carbon::parse($bundle?->created_at)->format('M d, Y') }}</em>
                                        </li>

                                        @if ($bundle?->createdBy?->profile)
                                            <li>
                                                <span>
                                                    <i class="am-icon-book-1"></i>
                                                    {{ __('coursebundles::bundles.created_by') }}
                                                </span>
                                                <em>
                                                    {{ $bundle->createdBy->profile->first_name }}
                                                    {{ $bundle->createdBy->profile->last_name }}
                                                </em>
                                            </li>
                                        @endif
                                    </ul>
                                </div>

                                <div class="cr-bundle-price-container">
                                    <div class="cr-bundle-price-info">
                                        @if (isPaidSystem())
                                            @if ($bundle?->discount_percentage > 0)
                                                <span class="cr-bundle-original-price">
                                                    {{ $bundle?->price }}
                                                    <svg width="38" height="11" viewBox="0 0 38 11"
                                                        fill="none">
                                                        <rect x="37" width="1" height="37.3271"
                                                            transform="rotate(77.2617 37 0)" fill="#686868" />
                                                        <rect x="37.2188" y="0.975342" width="1" height="37.3271"
                                                            transform="rotate(77.2617 37.2188 0.975342)"
                                                            fill="#F7F7F8" />
                                                    </svg>
                                                </span>
                                            @endif
                                            <div class="cr-bundle-discounted-price">
                                                <span class="cr-bundle-price-amount"><sup>{{ $currency_symbol}}</sup>{{ $bundle?->discount_percentage > 0 ? $bundle?->final_price : $bundle?->price }}</span>
                                            </div>
                                        @else
                                            <div class="cr-bundle-discounted-price">
                                                <span
                                                    class="cr-bundle-price-amount">{{ __('coursebundles::bundles.free') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    @if (!empty($bundle?->courses_sum_content_length))
                                        <div class="cr-bundle-duration-info">
                                            <i class="am-icon-time"></i>
                                            <span>
                                                {{ getCourseDuration($bundle?->courses_sum_content_length) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if (!$isLoading && $bundles->links()->paginator->hasPages())

                    <div class='am-pagination cr-bundle-pagination'>
                        @if (!empty($parPageList))
                            <div class="am-pagination-filter" wire:ignore>
                                <em>{{ __('coursebundles::bundles.show') }}</em>
                                <span class="am-select">
                                    <select wire:model.live="perPage" x-init="$wire.dispatch('initSelect2', { target: '#per-page-select' });" class="am-select2"
                                        id="per-page-select" data-componentid="@this" data-live="true"
                                        data-searchable="false" data-wiremodel="perPage">
                                        @if (!empty($perPage) && !in_array($perPage, $parPageList))
                                            <option value="{{ $perPage }}">{{ $perPage }}</option>
                                        @endif
                                        @foreach ($parPageList as $option)
                                            <option {{ $perPage == $option ? 'selected' : '' }}
                                                value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                <em>{{ __('coursebundles::bundles.listing_per_page') }}</em>
                            </div>
                        @endif
                        {{ $bundles->links('coursebundles::pagination.pagination') }}
                    </div>
                @endif
            @else
                <div class="cr-bundle-emptycase">
                    <div class="cr-bundle-emptycase_wrap">
                        <figure>
                            <img src="{{ asset('modules/coursebundles/images/no-record.png') }}"
                                alt="img description">
                        </figure>
                        <div class="cr-bundle-emptycase_content">
                            <h3>{{ __('coursebundles::bundles.no_courses_bundles') }}</h3>
                            <p>{{ __('coursebundles::bundles.no_courses_bundles_desc') }}</p>
                        </div>
                        <div class="cr-bundle-emptycase_btns">
                            <a href="{{ route('coursebundles.tutor.create-course-bundle') }}" class="am-btn">
                                {{ __('coursebundles::bundles.create_a_new_bundle') }}
                                <i class="am-icon-plus-02"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
    <div class="modal fade cr-confirm-popup" id="course_completed_popup" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="am-modal-body">
                    <span data-bs-dismiss="modal" class="am-closepopup">
                        <i class="am-icon-multiply-01"></i>
                    </span>
                    <div class="am-deletepopup_icon warning-icon">
                        <span>
                            <i class="am-icon-exclamation-01"></i>
                        </span>
                    </div>
                    <div class="cr-confirm-popup_title">
                        <h3>{{ __('coursebundles::bundles.publish_bundle_confirmation_title') }}</h3>
                        <p>{{ __('coursebundles::bundles.publish_bundle_confirmation_desc') }}</p>
                    </div>
                    <div class="cr-confirm-popup_btns">
                        <a href="{{ route('coursebundles.tutor.edit-course-bundle', ['id' => $bundleId]) }}"
                            class="am-white-btn cr-btnsmall">{{ __('coursebundles::bundles.edit_bundle') }}</a>
                        <a wire:loading.class="am-btn_disable" wire:target="publishBundle" wire:click="publishBundle"
                            class="am-btn cr-btnsmall">{{ __('coursebundles::bundles.publish') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/coursebundles/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/coursebundles/css/nouislider.min.css') }}">
@endpush
@push('scripts')
    <script defer src="{{ asset('modules/courses/js/nouislider.min.js') }}"></script>
    <script type="text/javascript" data-navigate-once>
        var component = '';
        var slider = '';
        document.addEventListener('livewire:navigated', function() {
            component = @this;
        }, {
            once: true
        });

        document.addEventListener('loadPageJs', (event) => {
            setTimeout(function() {
                initPriceRange();
            }, 50);
        });

        document.addEventListener('resetFilters', (event) => {
            resetFilters();
        });

        function resetFilters() {
            slider.noUiSlider.reset();
            $('#category-select').val('').trigger('change.select2');
            $('#sort-select').val('desc').trigger('change.select2');
            $('#status-select').val('').trigger('change.select2');
        }

        function initPriceRange() {
            slider = document.getElementById('sliderrange');
            if (slider) {
                noUiSlider.create(slider, {
                    start: [0, 1000],
                    connect: true,
                    range: {
                        'min': 0,
                        'max': 1000
                    }
                });

                var minPriceInput = document.getElementById('cr_min_price');
                var maxPriceInput = document.getElementById('cr_max_price');

                slider.noUiSlider.on('update', function(values, handle) {
                    var value = values[handle];
                    if (handle) {
                        maxPriceInput.value = Math.round(value);
                    } else {
                        minPriceInput.value = Math.round(value);
                    }
                });

                slider.noUiSlider.on('change', function(values, handle) {
                    var minValue = Math.round(values[0]);
                    var maxValue = Math.round(values[1]);
                    component.set('filters.min_price', minValue);
                    component.set('filters.max_price', maxValue);
                });

                minPriceInput.addEventListener('change', function() {
                    slider.noUiSlider.set([this.value, null]);
                });

                maxPriceInput.addEventListener('change', function() {
                    slider.noUiSlider.set([null, this.value]);
                });
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            jQuery(document).ready(function() {
                jQuery('.cr-price-dropdown').on('click', function(event) {
                    event.stopPropagation();
                    jQuery('.cr-price-range').toggle();
                });

                jQuery('.cr-price-range *').on('click', function(event) {
                    event.stopPropagation();
                });


                jQuery(document).on('click', function(event) {
                    if (!jQuery(event.target).closest('.cr-price-range').length && !jQuery(event
                            .target).hasClass('cr-price-dropdown')) {
                        jQuery('.cr-price-range').hide();
                    }
                });

                $(document).on('change', '#cr_min_price, #cr_max_price', function(event) {

                    let minValue = $('#cr_min_price').val();
                    let maxValue = $('#cr_max_price').val();
                    @this.set('filters.min_price', minValue);
                    @this.set('filters.max_price', maxValue);
                });
            });
        });
    </script>
@endpush
