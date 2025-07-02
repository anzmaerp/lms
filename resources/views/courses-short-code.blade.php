@extends('layouts.frontend-app')
@section('content')
<section class="am-feedback am-courses-block am-courses-block-three"> 
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="am-feedback-two_wrap">
                    <div class="am-section_title am-section_title_center am-section_title_one">
                        <span>Explore Our Courses</span>  
                        <h2>Achieve More with Expert-Guided Courses</h2>                                  
                        <p>Discover top-quality courses designed to enhance your skills, boost your career, and help you achieve your learning goals. Learn from industry experts at your own pace.</p>
                    </div>
                    <div class="am-testimonial-section">
                        <div class="splide" id="demo-slider">
                            <div class="splide__track">
                                <ul class="splide__list">
                                    <!-- <li class="splide__slide">
                                        <div class="cr-card">
                                            <div class="cr-instructor-info">
                                                <div class="cr-instructor-details">
                                                    <a href="http://127.0.0.1:8000/tutor/antony-clara" target="_blank" class="cr-instructor-name">
                                                        <img src="/storage/profile_images/antony-clara.jpg" alt="Antony C" class="cr-instructor-avatar">
                                                        Antony C
                                                    </a>
                                                </div>
                                                <button wire:click="likeCourse(12)" class="cr-bookmark-button" aria-label="courses::courses.like_this_course">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                                                        <g opacity="1">
                                                            <path opacity="1" d="M7.9987 14C8.66536 14 14.6654 10.6668 14.6654 6.00029C14.6654 3.66704 12.6654 2.02937 10.6654 2.00043C9.66537 1.98596 8.66536 2.33377 7.9987 3.33373C7.33203 2.33377 6.31473 2.00043 5.33203 2.00043C3.33203 2.00043 1.33203 3.66704 1.33203 6.00029C1.33203 10.6668 7.33204 14 7.9987 14Z" stroke="#F63C3C" stroke-width="1.125" stroke-linecap="round" stroke-linejoin="round"></path>
                                                        </g>
                                                    </svg>
                                                </button>
                                            </div>
                                            <figure class="cr-image-wrapper">
                                                <img height="200" width="360" src="http://127.0.0.1:8000/storage/courses/0mKqkYHFaLJgpNsNlpZ0GcEle9bT0x.png" alt="Time Management Mastery: Boost Your Productivity" class="cr-background-image">
                                                <figcaption>
                                                    <i class="am-icon-book-1"></i>
                                                    <span>15 Lessons</span>
                                                </figcaption>
                                            </figure>
                                            <div class="cr-course-card">
                                                <div class="cr-course-header">
                                                    <div class="cr-course-category">
                                                        In: <a href="http://127.0.0.1:8000/search-courses?searchCategories%5B0%5D=1" class="cr-category-link">Web Development</a>
                                                    </div>
                                                    <h2 class="cr-course-title">
                                                        <a href="http://127.0.0.1:8000/course/time-management-mastery-boost-your-productivity">Time Management Mastery: Boost Your Productivity</a>
                                                    </h2>
                                                </div>
                                                <div class="cr-course-features">
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-bar-chart-04"></i>
                                                        <span>Level</span>
                                                        <em>Intermediate</em>
                                                    </div>
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-dribbble-01"></i>
                                                        <span>Language</span>
                                                        <em>English</em>
                                                    </div>
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-time"></i>
                                                        <span>Duration</span>
                                                        <em>1h 40m</em>
                                                    </div>
                                                </div>
                                                <div class="cr-price-info">
                                                    <span class="cr-original-price">
                                                        $250
                                                        <svg width="38" height="11" viewBox="0 0 38 11" fill="none">
                                                            <rect x="37" width="1" height="37.3271" transform="rotate(77.2617 37 0)" fill="#686868"></rect>
                                                            <rect x="37.2188" y="0.975342" width="1" height="37.3271" transform="rotate(77.2617 37.2188 0.975342)" fill="#F7F7F8"></rect>
                                                        </svg>
                                                    </span>
                                                    <div class="cr-discounted-price">
                                                        <span class="cr-price-amount"><sup>$</sup>99</span>
                                                    </div>
                                                </div>
                                                <button class="am-btn">Join Now</button>
                                            </div>
                                        </div>
                                    </li> -->
                                    <!-- <li class="splide__slide">
                                        <div class="cr-card">
                                            <figure class="cr-image-wrapper">
                                                <img height="200" width="360" src="http://127.0.0.1:8000/storage/courses/0mKqkYHFaLJgpNsNlpZ0GcEle9bT0x.png" alt="Time Management Mastery: Boost Your Productivity" class="cr-background-image">
                                                <figcaption>
                                                    <span>Web Development</span>
                                                </figcaption>
                                            </figure>
                                            <div class="cr-course-card">
                                                <div class="cr-course-header">
                                                    <h2 class="cr-course-title">
                                                        <a href="http://127.0.0.1:8000/course/time-management-mastery-boost-your-productivity">Time Management Mastery: Boost Your Productivity</a>
                                                    </h2>
                                                </div>
                                                <div class="cr-instructor-details">
                                                    <a href="http://127.0.0.1:8000/tutor/antony-clara" target="_blank" class="cr-instructor-name">
                                                        <img src="/storage/profile_images/antony-clara.jpg" alt="Antony C" class="cr-instructor-avatar">
                                                        Antony C
                                                    </a>
                                                </div>
                                                <div class="cr-course-features">
                                                    <div class="cr-info-item">
                                                        <span><em>15</em>Lessons</span>
                                                    </div>
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-dribbble-01"></i>
                                                        <span>French</span>
                                                    </div>
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-time"></i>
                                                        <span><em>1</em>h<em>40</em>m</span>
                                                    </div>
                                                </div>
                                                <div class="cr-price-wrap">
                                                    <div class="cr-price-info">
                                                        <span class="cr-original-price">
                                                            $250
                                                            <svg width="38" height="11" viewBox="0 0 38 11" fill="none">
                                                                <rect x="37" width="1" height="37.3271" transform="rotate(77.2617 37 0)" fill="#686868"></rect>
                                                                <rect x="37.2188" y="0.975342" width="1" height="37.3271" transform="rotate(77.2617 37.2188 0.975342)" fill="#F7F7F8"></rect>
                                                            </svg>
                                                        </span>
                                                        <div class="cr-discounted-price">
                                                            <span class="cr-price-amount"><sup>$</sup>99</span>
                                                        </div>
                                                    </div>
                                                    <button class="am-btn">Join Now</button>
                                                </div>
                                            </div>
                                        </div>
                                    </li> -->
                                    <li class="splide__slide">
                                        <div class="cr-card">
                                            <figure class="cr-image-wrapper">
                                                <img height="200" width="360" src="http://127.0.0.1:8000/storage/courses/0mKqkYHFaLJgpNsNlpZ0GcEle9bT0x.png" alt="Time Management Mastery: Boost Your Productivity" class="cr-background-image">
                                            </figure>
                                            <div class="cr-course-card">
                                                <img src="/storage/profile_images/antony-clara.jpg" alt="Antony C" class="cr-instructor-avatar">
                                                <div class="cr-user-detail">
                                                    <h4>James Kong</h4>
                                                    <span>(Author)</span>
                                                    <div class="cr-price-info">
                                                        <span class="cr-original-price">
                                                            $250
                                                            <svg width="38" height="11" viewBox="0 0 38 11" fill="none">
                                                                <rect x="37" width="1" height="37.3271" transform="rotate(77.2617 37 0)" fill="#686868"></rect>
                                                                <rect x="37.2188" y="0.975342" width="1" height="37.3271" transform="rotate(77.2617 37.2188 0.975342)" fill="#F7F7F8"></rect>
                                                            </svg>
                                                        </span>
                                                        <div class="cr-discounted-price">
                                                            <span class="cr-price-amount"><sup>$</sup>99</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="cr-course-header">
                                                    <h2 class="cr-course-title">
                                                        <a href="http://127.0.0.1:8000/course/time-management-mastery-boost-your-productivity">Financial Literacy: Smart Money Management</a>
                                                    </h2>
                                                    <div class="cr-course-category">
                                                        In: <a href="http://127.0.0.1:8000/search-courses?searchCategories%5B0%5D=1" class="cr-category-link">Advanced Python Programming</a>
                                                    </div>
                                                </div>
                                                <div class="cr-course-features">
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-time"></i>
                                                        <span>Duration</span>
                                                        <em>1h 40m</em>
                                                    </div>
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-book-1"></i>
                                                        <span>Lessons</span>
                                                        <em>10</em>
                                                    </div>
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-bar-chart-04"></i>
                                                        <span>Level</span>
                                                        <em>Intermediate</em>
                                                    </div>
                                                    <div class="cr-info-item">
                                                        <i class="am-icon-dribbble-01"></i>
                                                        <span>Language</span>
                                                        <em>English</em>
                                                    </div>
                                                </div>
                                                <div class="cr-price-wrap">
                                                    <button class="am-btn">Join Now</button>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <img src="{{asset ('demo-content/home-v2/guide-pattran.png')}}" alt="Background shape image"> 
</section>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/css/splide.min.css" />
@endpush

@pushOnce('scripts')
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/js/splide.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (document.querySelector('#demo-slider')) {
                initClientsFeedbackSlider();
            }
        });

        document.addEventListener('loadSectionJs', function (event) {
            if (event.detail.sectionId === 'clients-feedback') {
                if (document.querySelector('#demo-slider')) {
                    initClientsFeedbackSlider();
                }
            }
        });

        function initClientsFeedbackSlider() {
            new Splide('#demo-slider', {
                gap: '16px',
                perPage: 4,
                perMove: 1,
                focus: 1,
                pagination: true,
                type: 'loop',
                direction: document.documentElement.dir === 'rtl' ? 'rtl' : 'ltr', 
                breakpoints: {
                    1024: {
                        perPage: 2,
                    },
                    768: {
                        perPage: 1,
                    },
                },
            }).mount();
        }
    </script>
@endPushOnce