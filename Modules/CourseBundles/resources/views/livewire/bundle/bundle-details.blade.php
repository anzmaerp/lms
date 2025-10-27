<div class="cr-bundle-details-page" wire:init="loadData">
    <div class="cr-bundle-content">
        <div class="cr-bundle-details-wrapper">
            <section class="cr-bundle-details-banner">
                <img src="{{asset('modules/coursebundles/images/banner-shape1.png')}}" alt="Course preview image" class="cr-bundle-shap-image" />
                <img src="{{asset('modules/coursebundles/images/banner-shape2.png')}}" alt="Course preview image" class="cr-bundle-shap-image2" />
                @if(!empty(setting('_coursebundle.course_bundle_banner_image')))
                    <img src="{{!empty(setting('_coursebundle.course_bundle_banner_image')[0]['path']) ? url(Storage::url(setting('_coursebundle.course_bundle_banner_image')[0]['path'])) : asset('modules/coursebundles/images/cr-bundle/banner-shape3.png');}}" alt="Course preview image" class="cr-bundle-shap-image3" />
                @endif
                <div class="cr-bundle-details-area">
                    <div class="cr-bundle-details-info">
                        <ol class="am-breadcrumb">
                            <li><a href="/" navigate="true">{{ __('coursebundles::bundles.home') }}</a></li>
                            <li><em>/</em></li>
                            <li><a href="{{  route('coursebundles.tutor.bundles') }}" navigate="true">{{ __('coursebundles::bundles.course_bundle') }}</a></li>
                            <li><em>/</em></li>
                            <li class="active"><span>{{ __('coursebundles::bundles.bundle_detail') }}</span></li>
                        </ol>
                        @if (!empty($bundle?->title) || !empty($bundle?->short_description))
                            <div class="cr-title-box">
                                <h2>{{ $bundle?->title ?? '' }}</h1>
                                <p>{{ $bundle?->short_description ?? '' }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
            <section class="cr-bundle-details-sections">
                <div class="cr-bundle-details-area">
                    <div class="cr-bundle-detail_wrap">
                        <div class="cr-bundle-detail_description">
                            <h2>{{__('coursebundles::bundles.description') }}</h2>
                            @if (!empty($bundle?->description))
                                <div class="full-description">
                                    {!! $bundle?->description !!}
                                </div>
                            @endif  
                        </div>
                        @if($isLoading)
                            <div>
                                @include('coursebundles::skeletons.courses-bundle-detail', ['total' => 4])
                            </div>
                        @else
                            <div class="cr-bundle-detail_includecourses">
                                @if(!empty($bundleCourses) && $bundleCourses->isNotEmpty())
                                    <h2>{{__('coursebundles::bundles.include_courses') }}</h2>
                                    @foreach($bundleCourses as $course)
                            
                                        <a href="@if($viewCourse)  {{ route('courses.course-taking', ['slug' => $course?->slug]) }} @else {{ route('courses.course-detail', $course?->slug) }}@endif">
                                            <div class="cr-bundle-detail_courseitem">
                                                @if (!empty($course?->thumbnail?->path))
                                                    <figure>
                                                        <img src="{{ !empty($course?->thumbnail?->path) ? url(Storage::url($course?->thumbnail?->path)) : asset('demo-content/placeholders/placeholder.png') }}" alt="{{ $course?->title }}" class="cr-background-image" />
                                                    </figure>
                                                @endIf
                                                <div class="cr-bundle-course-detail">
                                                    <div class="cr-bundle-course-detail_content">
                                                        @if (!empty($course?->title)) <h4>{{ $course?->title }}</h4> @endif
                                                        <ul>
                                                            @if (!empty($course?->category?->name))
                                                                <li>
                                                                    <i class="am-icon-layer-01"></i>
                                                                    <span>{{ $course?->category?->name }}</span>
                                                                </li>
                                                            @endif
                                                            @if (!empty($course?->subcategory?->name))
                                                                <li>
                                                                    <i class="am-icon-bar-chart-04"></i>
                                                                    <span>{{ $course?->subcategory?->name }}</span>
                                                                </li>
                                                            @endif
                                                            @if (!empty($course?->curriculums))
                                                                <li>    
                                                                    <i class="am-icon-book-1"></i>
                                                                    <span>{{ $course?->curriculums->count() }} {{ __('coursebundles::bundles.lessons') }}</span>
                                                                </li>
                                                            @endif
                                                            @if (!empty($course?->language?->name))
                                                                <li>
                                                                    <i class="am-icon-dribbble-01"></i>
                                                                    <span>{{ $course?->language?->name }}</span>
                                                                </li>
                                                            @endif
                                                            @if (!empty($course?->ratings_count) || !empty($course?->ratings_avg_rating))
                                                                <li>
                                                                    <em><i class="am-icon-star-filled"></i>{{ number_format($course?->ratings_avg_rating, 1) }}</em>
                                                                    <span>({{ $course?->ratings_count }} {{ __('coursebundles::bundles.reviews') }})</span>
                                                                </li>
                                                            @endif
                                                            
                                                        </ul>
                                                        @if (!empty($course?->description))<span>{!! $course?->description !!}</span>@endif
                                                    </div>
                                                    @if (isPaidSystem() && !empty($course->pricing?->price) && !empty($course->pricing?->final_price) && $course->pricing?->price != 0.00 )
                                                        <div class="cr-bundle-course-detail_price">
                                                            <sub>{{ getCurrencySymbol() }}</sub>
                                                            <strong>{{ number_format($course->pricing?->final_price, 2) }}</strong>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                    @if (!$isLoading && $bundleCourses->links()->paginator->hasPages())
                                        <div class='am-pagination cr-bundle-pagination'>
                                            @if(!empty($perPageList))
                                                <div class="am-pagination-filter" wire:ignore>
                                                    <em>{{ __('coursebundles::bundles.show') }}</em>
                                                    <span class="am-select">
                                                        <select wire:model.live="perPage" x-init="$wire.dispatch('initSelect2', {target: '#per-page-select'});" class="am-select2" id="per-page-select" data-componentid="@this" data-live="true" data-searchable="false" data-wiremodel="perPage">
                                                            @if (!empty($perPage) && !in_array($perPage, $perPageList))
                                                                <option value="{{ $perPage }}">{{ $perPage }}</option>
                                                            @endif
                                                            @foreach ($perPageList as $option)
                                                                <option {{ $perPage == $option ? 'selected' : '' }} value="{{ $option }}">{{ $option }}</option>
                                                            @endforeach
                                                        </select>
                                                    </span>
                                                    <em>{{ __('coursebundles::bundles.listing_per_page') }}</em>
                                                </div>
                                            @endif
                                            {{ $bundleCourses->links('coursebundles::pagination.pagination') }}
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif    
                    </div>

                    <!-- === Sidebar Start === -->
                    <div class="cr-bundle-sidebar">
                        <div class="cr-bundle-card">
                            @if (!empty($bundle?->thumbnail?->path))
                                <figure class="cr-image-wrapper">
                                    <img src="{{ !empty($bundle?->thumbnail?->path) ? url(Storage::url($bundle?->thumbnail?->path)) : asset('demo-content/placeholders/placeholder.png') }}" alt="{{ $bundle?->title }}" class="cr-background-image" />
                                </figure>
                            @endIf
                            <div class="cr-bundle-details">
                                <div class="cr-price-section">
                                    <div class="cr-price-wrapper">
                                        @if(isPaidSystem())
                                            <div class="cr-price">
                                                <span class="cr-currency">{{getCurrencySymbol()}}</span>
                                                <span class="cr-amount">{{ $bundle?->final_price }}</span>
                                            </div>
                                            @if($bundle?->discount_percentage > 0)
                                                <del class="cr-discount">{{ $bundle?->price }}</del>
                                            @endif
                                        @else
                                            <span class="cr-amount">{{ __('coursebundles::bundles.free') }}</span>
                                        @endif
                                    </div>
                                    @if(isPaidSystem() && $bundle?->discount_percentage > 0)
                                        <div class="cr-discount-label">{{ $bundle?->discount_percentage }}{{ __('coursebundles::bundles.off') }}</div>
                                    @endIf
                                </div>
                                <div class="cr-bundle-includes">
                                    <h2 class="cr-includes-title">{{ __('coursebundles::bundles.course_bundle') }}:</h2>
                                    <ul class="cr-includes-list">
                                        <li class="cr-includes-item">
                                            <i class="am-icon-book-1"></i>
                                            <div class="cr-includes-text">
                                                @if(!empty($bundle?->courses_count))
                                                    <span class="cr-includes-value">{{ $bundle?->courses_count }}</span>
                                                @endIf
                                                <span class="cr-includes-label">{{ __('coursebundles::bundles.Courses') }}</span>
                                            </div>
                                        </li>
                                        <li class="cr-includes-item">
                                            <i class="am-icon-book"></i>
                                            <div class="cr-includes-text">
                                                @php
                                                    $curriculums_count = null;
                                                    $video_curriculums_count = null;
                                                    $video_curriculums_sum_content_length = null;
                                                    foreach ($bundle?->courses as $course) {
                                                        $curriculums_count += $course?->curriculums_count;
                                                        $video_curriculums_count += $course?->video_curriculums_count;
                                                        $video_curriculums_sum_content_length += $course?->video_curriculums_sum_content_length;
                                                    }
                                                @endphp
                                                <span class="cr-includes-value">{{ $curriculums_count }}</span>
                                                <span class="cr-includes-label">{{ __('coursebundles::bundles.articles') }}</span>
                                            </div>
                                        </li>
                                        <li class="cr-includes-item">
                                            <i class="am-icon-play"></i>
                                            <div class="cr-includes-text">
                                                <span class="cr-includes-value">{{ $video_curriculums_count }}</span>
                                                <span class="cr-includes-label">{{ __('coursebundles::bundles.videos') }}  {{ __('coursebundles::bundles.of') }} {{ getCourseDuration($video_curriculums_sum_content_length) }}</span>
                                            </div>
                                        </li>
                                        <li class="cr-includes-item">
                                            <i class="am-icon-time"></i>
                                            <div class="cr-includes-text">
                                                @if(!empty($bundle?->courses_sum_content_length))
                                                    @php
                                                        $hours = floor($bundle?->courses_sum_content_length / 60);
                                                        $minutes = $bundle?->courses_sum_content_length % 60;
                                                    @endphp
                                                    <span class="cr-includes-value">{{ $hours }} {{ $hours > 1 ? __('coursebundles::bundles.hrs') : __('coursebundles::bundles.hr') }} : {{ $minutes }} {{ $minutes > 1 ? __('coursebundles::bundles.mins') : __('coursebundles::bundles.min') }}</span>
                                                @endIf
                                                <span class="cr-includes-label">{{ __('coursebundles::bundles.duration') }}</span>
                                            </div>
                                        </li>
                                        <li class="cr-includes-item">
                                            <i class="am-icon-calender-day"></i>
                                            <div class="cr-includes-text">
                                                <span class="cr-includes-value">{{ $bundle?->created_at->format('M d, Y') }}</span>
                                                <span class="cr-includes-label">{{ __('coursebundles::bundles.created_at') }}</span>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            @if(!$viewCourse)
                                @if(isPaidSystem())
                                    <div class="cr-action-buttons">
                                        <a href="#" class="am-btn" wire:click.prevent="addToCart">
                                            {{ __('coursebundles::bundles.add_to_cart') }}
                                        </a>
                                    </div>
                                @else
                                    <div class="cr-action-buttons">
                                        <a href="javascript:void(0)" class="am-btn" wire:click="getFreeBundle" wire:loading.attr="disabled" wire:loading.class="am-btn_disable">
                                            {{ __('coursebundles::bundles.get_bundle') }}
                                        </a>
                                    </div>
                                @endif
                            @endif
                        </div>
                            @if ($bundle->instructors && $bundle->instructors->isNotEmpty())
                                @foreach ($bundle->instructors as $instructor)
                                    <div class="am-similar-user">
                                        <div class="am-tutordetail_user">
                                            {{-- Profile Image --}}
                                            <figure class="am-tutorvone_img">
                                                @if (!empty($instructor->profile?->image) && Storage::disk(getStorageDisk())->exists($instructor->profile?->image))
                                                    <img src="{{ url(Storage::url($instructor->profile->image)) }}" alt="{{ $instructor->profile?->short_name }}">
                                                @else
                                                    <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 34, 34) }}" alt="profile image" />
                                                @endif
                                            </figure>

                                            {{-- Name & Verification --}}
                                            <div class="am-tutordetail_user_name">
                                                <h3>
                                                    @if(!empty($instructor->profile?->full_name))  
                                                        <a href="javascript:void(0);">{{ $instructor->profile->full_name }}</a>
                                                    @endif

                                                    {{-- Verified Badge --}}
                                                    @if (!empty($instructor->profile?->verified_at))
                                                        <div class="am-custom-tooltip">
                                                            <span class="am-tooltip-text"><span>{{ __('courses::courses.verified') }}</span></span>
                                                            <i class="am-icon-user-check"></i>
                                                        </div>
                                                    @endif

                                                    {{-- Country Flag --}}
                                                    @if(!empty($instructor->address?->country?->short_code))
                                                        <div class="am-custom-tooltip">
                                                            <span class="am-tooltip-text">
                                                                <span>{{ ucfirst($instructor->address->country->name) }}</span>
                                                            </span>
                                                            <span class="flag flag-{{ strtolower($instructor->address->country->short_code) }}"></span>
                                                        </div>
                                                    @endif
                                                </h3>

                                                {{-- Tagline --}}
                                                @if(!empty($instructor->profile?->tagline))
                                                    <span>{{ $instructor->profile->tagline }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Reviews, Courses, Students, Languages --}}
                                        <ul class="am-tutorreviews-list">
                                            {{-- Rating --}}
                                            @if(!empty($instructor->reviews_avg_rating) || !empty($instructor->reviews_count))
                                                <li>
                                                    <div class="am-tutorreview-item">
                                                        <div class="am-tutorreview-item_icon"><i class="am-icon-star-filled"></i></div>
                                                        <span class="am-uniqespace">
                                                            @if(!empty($instructor->reviews_avg_rating))
                                                                {{ number_format($instructor->reviews_avg_rating, 1) }} /5.0
                                                            @endif
                                                            @if(!empty($instructor->reviews_count))
                                                                <em>({{ $instructor->reviews_count }} {{ __('coursebundles::bundles.reviews') }})</em>
                                                            @endif
                                                        </span>
                                                    </div>
                                                </li>
                                            @endif

                                            {{-- Active Students --}}
                                            @if(!empty($instructor->active_students))
                                                <li>
                                                    <div class="am-tutorreview-item">
                                                        <div class="am-tutorreview-item_icon"><i class="am-icon-user-group"></i></div>
                                                        <span>
                                                            {{ $instructor->active_students }}
                                                            <em>
                                                                {{ $instructor->active_students == 1 ? __('coursebundles::bundles.active_student') : __('coursebundles::bundles.active_students') }}
                                                            </em>
                                                        </span>
                                                    </div>
                                                </li>
                                            @endif

                                            {{-- Courses Count --}}
                                            @if(!empty($instructor->courses))
                                                <li>
                                                    <div class="am-tutorreview-item">
                                                        <div class="am-tutorreview-item_icon">
                                                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                                                <g opacity="0.7">
                                                                    <path d="M9 16.5C13.1421 16.5 16.5 13.1421 16.5 9C16.5 4.85786 13.1421 1.5 9 1.5C4.85786 1.5 1.5 4.85786 1.5 9C1.5 13.1421 4.85786 16.5 9 16.5Z" stroke="#585858" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                    <path d="M7.18201 7.23984C7.18201 6.4545 8.04578 5.97564 8.71184 6.39173L11.5295 8.15195C12.1564 8.54359 12.1564 9.45654 11.5295 9.84817L8.71184 11.6084C8.04578 12.0245 7.18201 11.5456 7.18201 10.7603V7.23984Z" stroke="#585858" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                                                </g>
                                                            </svg>
                                                        </div>
                                                        <span>{{ $instructor->courses->count() }} <em>{{ __('coursebundles::bundles.Courses') }}</em></span>
                                                    </div>
                                                </li>
                                            @endif

                                            {{-- Languages --}}
                                            @if(!empty($instructor->profile?->native_language) || !empty($instructor->languages))
                                                <li>
                                                    <div class="am-tutorreview-item">
                                                        <div class="am-tutorreview-item_icon"><i class="am-icon-megaphone-01"></i></div>
                                                        <span><em>{{ __('coursebundles::bundles.i_can_speak') }}</em></span>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="am-tutorreview-item">
                                                        <div class="wa-tags-list">
                                                            <ul>
                                                                @if(!empty($instructor->profile?->native_language))
                                                                    <li>
                                                                        <span>{{ ucfirst($instructor->profile->native_language) }}
                                                                            <em>{{ __('coursebundles::bundles.native') }}</em>
                                                                        </span>
                                                                    </li>
                                                                @endif

                                                                @foreach ($instructor->languages as $language)
                                                                    <li><span>{{ $language->name }}</span></li>
                                                                @endforeach

                                                                @if($instructor->languages->count() > 2)
                                                                    <li><span>+{{ $instructor->languages->count() - 2 }} {{ __('coursebundles::bundles.more') }}</span></li>
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </li>
                                            @endif
                                        </ul>

                                        {{-- Instructor Bio --}}
                                        @if(!empty($instructor->profile?->description))
                                            <p class="cr-instructor-bio">{{ Str::words(strip_tags($instructor->profile->description), 50) }}</p>
                                        @endif

                                        {{-- Footer (Social Links + Profile Link) --}}
                                        <div class="cr-profile-footer">
                                            @if(!empty($instructor->socialProfiles) && $instructor->socialProfiles->isNotEmpty())
                                                <ul class="cr-social-icons">
                                                    @php
                                                        $validProfiles = $instructor->socialProfiles->filter(fn($profile) => !empty($profile->url))->take(4);
                                                    @endphp
                                                    @foreach ($validProfiles as $socialProfile)
                                                        <li>
                                                            <a href="{{ $socialProfile->url }}" target="_blank">
                                                                <span class="am-tooltip-text"><span>{{ $socialProfile->type }}</span></span>
                                                                @if($socialProfile->type == 'Pinterest')
                                                                    <x-courses::icons.pinterest />
                                                                @else
                                                                    <i class="{{ $socialIcons[$socialProfile->type] ?? '' }}"></i>
                                                                @endif
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif

                                            <a href="{{ route('tutor-detail', ['slug' => $instructor->profile?->slug]) }}">
                                                <button class="cr-view-profile-btn">{{ __('courses::courses.view_profile') }}</button>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                    </div>
                </div>
            </section>
            @if($isLoading)
                <div class="cr-bundles_wrap cr-bundles_exclusive">
                    @include('coursebundles::skeletons.courses-bundle-listing', ['total' => 4])
                </div>
            @else
                @if($bundlesData->isNotEmpty())
                    <section class="cr-bundle-footer">
                        <div class="cr-bundle-footer_wrap">
                            <h2>{{ __('coursebundles::bundles.you_may_also_like') }}</h2>
                            <div class="cr-bundles_wrap cr-bundles_exclusive">
                                <ul>
                                    @foreach ($bundlesData as $relatedBundle)
                                        <li>
                                            <div class="cr-bundles_item">
                                                <figure>
                                                    <img src="{{ !empty($relatedBundle?->thumbnail?->path) ? url(Storage::url($relatedBundle?->thumbnail?->path)) : asset('demo-content/placeholders/placeholder.png') }}" alt="{{ $relatedBundle?->title }}" class="cr-background-image" />
                                                    @if($relatedBundle?->discount_percentage > 0)
                                                        <figcaption>
                                                            <span>{{ $relatedBundle?->discount_percentage }}{{ __('coursebundles::bundles.off') }}</span>
                                                        </figcaption>
                                                    @endif
                                                </figure>
        
                                                <div class="cr-bundles_item_content">
                                                    <div class="cr-bundles_user">
                                                        @if ($relatedBundle->instructors->isNotEmpty())
                                                            @foreach ($relatedBundle->instructors as $instructor)
                                                                <figure>
                                                                    @if (!empty($instructor->profile?->image) && Storage::disk(getStorageDisk())->exists($instructor->profile?->image))
                                                                        <img src="{{ resizedImage($instructor->profile?->image, 50, 50) }}" alt="{{ $instructor->profile?->full_name }}" />
                                                                    @else
                                                                        <img src="{{ setting('_general.default_avatar_for_user') 
                                                                            ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) 
                                                                            : resizedImage('placeholder.png', 50, 50) }}" 
                                                                            alt="default profile" />
                                                                    @endif
                                                                </figure>
                                                                @if(!empty($instructor->profile?->short_name))
                                                                    <span>{{ $instructor->profile?->short_name }}</span>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            {{-- fallback if bundle has no instructors --}}
                                                            <figure>
                                                                <img src="{{ setting('_general.default_avatar_for_user') 
                                                                    ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) 
                                                                    : resizedImage('placeholder.png', 50, 50) }}" alt="profile" />
                                                            </figure>
                                                            <span>{{ __('coursebundles::bundles.no_instructor_assigned') }}</span>
                                                        @endif

                                                        @if(!empty($relatedBundle?->courses_count))
                                                            <span>
                                                                <i class="am-icon-book-1"></i>
                                                                <em>{{ $relatedBundle?->courses_count }}</em>
                                                                {{ $relatedBundle?->courses_count == 1 ? __('coursebundles::bundles.Course') : __('coursebundles::bundles.Courses') }}
                                                            </span>
                                                        @endif
                                                    </div>

                                                    @if(!empty($relatedBundle?->title))
                                                        <div class="cr-bundles_coursetitle">
                                                            <a href="{{ route('coursebundles.bundle-details', ['slug' => $relatedBundle?->slug]) }}">
                                                                <h3>{{ $relatedBundle?->title }}</h3>
                                                            </a>
                                                        </div>
                                                    @endif

                                                    @if(!empty($relatedBundle?->short_description))
                                                        <p>{{ $relatedBundle?->short_description }}</p>
                                                    @endif

                                                    <div class="cr-bundle-price-container">
                                                        <div class="cr-bundle-price-info">
                                                            @if($relatedBundle?->discount_percentage > 0)
                                                                <span class="cr-bundle-original-price">
                                                                    {{ $relatedBundle?->price }}
                                                                    <svg width="38" height="11" viewBox="0 0 38 11" fill="none">
                                                                        <rect x="37" width="1" height="37.3271" transform="rotate(77.2617 37 0)" fill="#686868"/>
                                                                        <rect x="37.2188" y="0.975342" width="1" height="37.3271" transform="rotate(77.2617 37.2188 0.975342)" fill="#F7F7F8"/>
                                                                    </svg>
                                                                </span>
                                                            @endif

                                                            <div class="cr-bundle-discounted-price">
                                                                <span class="cr-bundle-price-amount">
                                                                    <sup>{{ $currency_symbol }}</sup>
                                                                    {{ $relatedBundle?->discount_percentage > 0 ? $relatedBundle?->final_price : $relatedBundle?->price }}
                                                                </span>
                                                            </div>
                                                        </div>

                                                        @if($relatedBundle?->courses_sum_content_length)
                                                            <div class="cr-bundle-duration-info">
                                                                <i class="am-icon-time"></i>
                                                                <span>{{ getCourseDuration($relatedBundle?->courses_sum_content_length) }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach        
                                </ul>
                            </div>
                        </div>
                    </section>
                @endIf
            @endIf    
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="{{ asset('modules/coursebundles/css/main.css') }}">
@vite([
    'public/css/videojs.css',
    'public/css/flags.css',
])
@endpush

@push('scripts')
<script src="{{ asset('js/video.min.js') }}"></script>

<script type="text/javascript" data-navigate-once>

     function openVideoModal(videoPath, type) {
        var videoModal = new bootstrap.Modal(document.getElementById('videoModal'));
        let player = null;
        let id = 'ar-video_' + Math.random().toString(36).substring(2, 15);
      
        const container = document.querySelector('.cr-offering-videojs');

        container.innerHTML = type === 'yt_link' || type === 'vm_link' 
        ? `<iframe id="${id}" class="cr-promotional-video" width="100%" height="400" src="${videoPath}" frameborder="0" allowfullscreen></iframe>`
        : `<video id="${id}" class="video-js vjs-default-skin d-none cr-promotional-video" width="100%" height="400" controls></video>`;

        if (type !== 'yt_link' && type !== 'vm_link') {
            setTimeout(() => {
                player = videojs(id);
                player.src({ type: 'video/mp4', src: videoPath });
                player.ready(function () {
                    player.play();
                    player.removeClass('d-none');
                });
            }, 100);
        }
        videoModal.show();
    };
    function initializeVideoPlayer(videoElement, courseId) {
        alert(videoElement);
        if (!videoElement.player) {
            let player = videojs(videoElement);
            videoElement.player = player;
            
            player.on('loadstart', function() {
                player.addClass('vjs-waiting');
                $(`#cr-card-skeleton-${courseId}`).remove();
                player.removeClass('d-none');
            });
            
            player.on('loadeddata', function() {
                player.removeClass('vjs-waiting');
                player.removeClass('d-none');
                $(`#cr-card-skeleton-${courseId}`)?.remove();
            });
            
            player.on('playing', function() {
                let players = document.querySelectorAll('.video-js');
                let current = document.getElementById(this.id());
                players.forEach((element) => {
                    if(current != element){
                        let otherPlayer = videojs(element);
                        if (!otherPlayer.paused()) {
                            otherPlayer.pause();
                        }
                    }
                });
            });
        }
    }
    document.addEventListener("DOMContentLoaded", (event) => {
        $(document).on('click','.toggle-description', function() {
            var parentContainer = $(this).closest('.am-addmore');

            parentContainer.find('.short-description').toggleClass('d-none');
            parentContainer.find('.full-description').toggleClass('d-none');
            if (parentContainer.find('.short-description').hasClass('d-none')) {
                $(this).text('{{ __('general.show_more') }}');
            } else {
                $(this).text('{{ __('general.show_less') }}');
            }
        });
    });
    
</script>
@endpush