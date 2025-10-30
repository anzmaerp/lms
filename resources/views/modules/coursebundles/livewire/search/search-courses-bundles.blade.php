<div class="cr-bundles cr-bundles_exclusive" wire:init="loadData">
    <div class="cr-bundles_banner">
        <div class="cr-bundles_banner_content">
            <ol class="am-breadcrumb">
                <li><a href="/" navigate="true">{{ __('courses::courses.home') }}</a></li>
                <li><em>/</em></li>
                <li class="active"><span>{{ __('coursebundles::bundles.course_bundle') }}</span></li>
            </ol>
            @if(!empty(setting('_coursebundle.course_bundle_heading'))) <h2>{{ setting('_coursebundle.course_bundle_heading') }}</h2> @endif
            @if(!empty(setting('_coursebundle.course_bundle_description'))) <p>{{ setting('_coursebundle.course_bundle_description') }}</p> @endif
            <form class="fw-themeform cr-searchform">
                <fieldset>
                    <div class="form-group">
                        <i class="am-icon-search-02"></i>
                        <input type="text" class="form-control" placeholder="{{ __('coursebundles::bundles.search_by_keywords') }}" wire:model.live.debounce.250ms="filters.keyword">
                        <div class="cr-customdropdown">
                            <a href="javascript:void(0);" id="cr-customdropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ __("general.price") }}
                                <i class="am-icon-chevron-down"></i>
                            </a>
                            <div style="min-width:330px" wire:ignore id="panelsStayOpen-collapseFive" class="cr-customdropdown_list dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <div class="accordion-body">
                                    <div class="cr-radio-options">
                                        <div class="at-form-group cr-price-range">
                                            <div id="sliderrange" class="cr-slider-range"></div>
                                            <div class="cr-price-inputs">
                                                <div class="cr-price-input">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                        <path opacity="0.4" d="M4 10.6666C4 12.1393 5.19391 13.3333 6.66667 13.3333H9.67545C10.9593 13.3333 12 12.2925 12 11.0087C12 10.0081 11.3597 9.11983 10.4105 8.80343L5.58947 7.19641C4.64025 6.88 4 5.9917 4 4.99114C4 3.70732 5.04074 2.66659 6.32455 2.66659H9.33333C10.8061 2.66659 12 3.86049 12 5.33325M8 1.33325L8 14.6666" stroke="#585858" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    <input type="number" id="cr_min_price" value="100" aria-label="Minimum Price" min="0" max="1000" step="1">
                                                </div>
                                                <span class="cr-option-count">-</span>
                                                <div class="cr-price-input">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                        <path opacity="0.4" d="M4 10.6666C4 12.1393 5.19391 13.3333 6.66667 13.3333H9.67545C10.9593 13.3333 12 12.2925 12 11.0087C12 10.0081 11.3597 9.11983 10.4105 8.80343L5.58947 7.19641C4.64025 6.88 4 5.9917 4 4.99114C4 3.70732 5.04074 2.66659 6.32455 2.66659H9.33333C10.8061 2.66659 12 3.86049 12 5.33325M8 1.33325L8 14.6666" stroke="#585858" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                    <input type="number" id="cr_max_price" value="100" aria-label="Minimum Price" min="0" max="1000" step="1">
                                                </div>
                                            </div>  
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button id="searchButton" type="button" class="am-btn">{{ __('coursebundles::bundles.search') }}</button>
                    </div>
                </fieldset>
            </form>
        </div>
        <img class="cr-bundles-banner-img2" src='{{asset("modules/coursebundles/images/banner-shape1.png")}}' alt="image">
        <img class="cr-bundles-banner-img1" src='{{asset("modules/coursebundles/images/banner-shape2.png")}}' alt="image">
    </div>

    @if($isLoading)
        <div>
            @include('coursebundles::skeletons.search-course-bundle', ['total' => $perPage])
        </div>
    @else
        <div class="d-none tutors-skeleton" wire:target="filters, loadData,perPage" wire:loading.class.remove="d-none">
            @include('coursebundles::skeletons.search-course-bundle', ['total' => $perPage])
        </div>
        @if($bundles->isNotEmpty())
            <div class="cr-bundles_wrap" wire:loading.class="d-none" wire:target="filters, loadData,perPage">
                <div class="am-title">
                    <h2>{{ __('coursebundles::bundles.course_bundles') }}</h2>
                </div>
                <ul>
                    @foreach ($bundles as $bundle)
                        <li>
                            <div class="cr-bundles_item">
                                <figure>
                                    <img src="{{ !empty($bundle->thumbnail->path) ? url(Storage::url($bundle->thumbnail->path)) : asset('demo-content/placeholders/placeholder.png') }}" alt="{{ $bundle->title }}" class="cr-background-image" />
                                    {{-- <figcaption>
                                        <span class="am-quizstatus">
                                            {{ __('coursebundles::bundles.'.$bundle?->status)}}
                                        </span>
                                    </figcaption> --}}
                                    @if(isPaidSystem() && $bundle?->discount_percentage > 0)
                                        <figcaption>
                                            <span>{{ $bundle?->discount_percentage }}% OFF</span>
                                        </figcaption>
                                    @endif
                                </figure>
                                <div class="cr-bundles_item_content">


                                        <div class="cr-bundles_user">
                                            <figure>
                                                @if (!empty($bundle->instructor?->profile?->image) && Storage::disk(getStorageDisk())->exists($bundle->instructor?->profile?->image))
                                                    <img src="{{ resizedImage($bundle->instructor?->profile?->image,50,50) }}" alt="{{ $bundle->instructor?->profile?->image }}" />
                                                @else
                                                    <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 50, 50) }}" alt="{{ $bundle->instructor?->profile?->image }}" />
                                                @endif
                                            </figure>
                                            <span>{{ $bundle->instructor?->profile?->short_name }}</span>
                                            <span>
                                                <i class="am-icon-book-1"></i>
                                                <em>{{ $bundle?->courses_count }}</em>
                                                {{ $bundle?->courses_count == 1 ? __('coursebundles::bundles.Course') : __('coursebundles::bundles.Courses') }}
                                            </span>
                                        </div>
                                    @if(!empty($bundle?->title))
                                        <div class="cr-bundles_coursetitle">
                                            <a href="{{ route('coursebundles.bundle-details', ['slug' => $bundle?->slug]) }}">
                                                <h3>{{ $bundle?->title }}</h3>
                                            </a>
                                        </div>
                                    @endif
                                    <p>{{ $bundle?->short_description }}</p>
                                    <div class="cr-bundle-price-container">
                                        <div class="cr-bundle-price-info">
                                            @if(isPaidSystem())
                                            @if($bundle?->discount_percentage > 0)
                                                <span class="cr-bundle-original-price">
                                                    {{ $bundle?->price }}
                                                    <svg width="38" height="11" viewBox="0 0 38 11" fill="none">
                                                        <rect x="37" width="1" height="37.3271" transform="rotate(77.2617 37 0)" fill="#686868"/>
                                                        <rect x="37.2188" y="0.975342" width="1" height="37.3271" transform="rotate(77.2617 37.2188 0.975342)" fill="#F7F7F8"/>
                                                    </svg>
                                                </span>
                                            @endif
                                            <div class="cr-bundle-discounted-price">
                                                <span class="cr-bundle-price-amount"><sup>{{ $currency_symbol }}</sup>{{ $bundle?->discount_percentage > 0 ? $bundle?->final_price : $bundle?->price }}</span>
                                            </div>
                                            @else
                                                <div class="cr-bundle-discounted-price">
                                                    <span class="cr-bundle-price-amount">{{ __('coursebundles::bundles.free') }}</span>
                                                </div>
                                            @endIf
                                        </div>
                                        @if($bundle?->courses_sum_content_length)
                                            <div class="cr-bundle-duration-info">
                                                <i class="am-icon-time"></i>
                                                <span>
                                                    {{ getCourseDuration($bundle?->courses_sum_content_length)}}
                                                </span>
                                            </div>
                                        @endif
                                    </div>
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
                                    <select wire:model.live="perPage" x-init="$wire.dispatch('initSelect2', {target: '#per-page-select'});" class="am-select2" id="per-page-select" data-componentid="@this" data-live="true" data-searchable="false" data-wiremodel="perPage">
                                        @if (!empty($perPage) && !in_array($perPage, $parPageList))
                                            <option value="{{ $perPage }}">{{ $perPage }}</option>
                                        @endif
                                        @foreach ($parPageList as $option)
                                            <option {{ $perPage == $option ? 'selected' : '' }} value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                <em>{{ __('coursebundles::bundles.listing_per_page') }}</em>
                            </div>
                        @endif
                        {{ $bundles->links('coursebundles::pagination.pagination') }}
                    </div>
                @endif
            </div>
        @else
            <div class="cr-bundle-emptycase">
                <div class="cr-bundle-emptycase_wrap">
                    <figure>
                        <img src="{{asset ('modules/coursebundles/images/no-record.png')}}" alt="img description">
                    </figure>
                    <div class="cr-bundle-emptycase_content">
                        <h3>{{__('coursebundles::bundles.no_bundles_found') }}</h3>
                        <p>{{__('coursebundles::bundles.empty_view_description') }}</p>
                    </div>
                </div>
            </div>
        @endIf
    @endIf
</div>
@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/coursebundles/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('modules/coursebundles/css/nouislider.min.css') }}">
@endpush
@push('scripts')
    <script defer src="{{ asset('modules/courses/js/nouislider.min.js')}}"></script>
    <script>
        var component = '';
        var slider = '';

        document.addEventListener('livewire:navigated', function() {
            component = @this;
        },{ once: true });

        document.addEventListener('loadPageJs', (event) => {
            setTimeout(function() {
                initPriceRange();
            }, 50);
        });

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

                slider.noUiSlider.on('update', function (values, handle) {
                    var value = values[handle];
                    if (handle) {
                        maxPriceInput.value = Math.round(value);
                    } else {
                        minPriceInput.value = Math.round(value);
                    }
                });

                slider.noUiSlider.on('change', function (values, handle) {
                    var minValue = Math.round(values[0]);
                    var maxValue = Math.round(values[1]);
                    component.set('filters.min_price', minValue);
                    component.set('filters.max_price', maxValue);
                });

                minPriceInput.addEventListener('change', function () {
                    slider.noUiSlider.set([this.value, null]);
                });

                maxPriceInput.addEventListener('change', function () {
                    slider.noUiSlider.set([null, this.value]);
                });
            }
        }
    </script>
@endpush