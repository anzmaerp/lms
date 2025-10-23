<?php

namespace Modules\CourseBundles\Livewire\Pages\Bundle;

use App\Facades\Cart;
use App\Services\BookingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\CourseBundles\Models\Bundle;
use Modules\CourseBundles\Services\BundleService;

class BundleDetails extends Component
{
    use WithPagination;

    public string $slug;

    public $isBuyable = true;

    public $viewCourse = false;

    public $role;

    public $isLoading = true;

    public $perPage;

    public $perPageList = [10, 25, 50, 100];

    public $filters = [
        'statuses' => [Bundle::STATUS_PUBLISHED]
    ];

    public $socialIcons = [
        'Facebook' => 'am-icon-facebook-1',
        'X/Twitter' => 'am-icon-twitter-02',
        'LinkedIn' => 'am-icon-linkedin-02',
        'Instagram' => 'am-icon-instagram',
        'Pinterest' => 'am-icon-pinterest',
        'YouTube' => 'am-icon-youtube',
        'TikTok' => 'am-icon-tiktok-02',
        'WhatsApp' => 'am-icon-whatsapp',
    ];

    #[Layout('layouts.frontend-app')]
    public function mount($slug)
    {
        $this->role = auth()?->user()?->role;
        $this->slug = $slug;
        $this->perPage = setting('_general.per_page_record') ?? 10;

        if ($this->role == 'student') {
            $bundleAddedToStudent = (new BundleService())->getPurchasedBundles(
                bundleId: $this->bundle->id,
                studentId: Auth::id(),
                tutorId: $this->bundle->instructor_id
            );
            $this->viewCourse = !empty($bundleAddedToStudent);
        }
    }

    #[Computed(persist: true)]
    public function bundle()
    {
        return (new BundleService())->getBundle(
            slug: $this->slug,
            relations: [
                'instructor' => fn($q) => $q
                    ->withCount(['bookingSlots as active_students' => fn($query) => $query->whereStatus('active')])
                    ->withCount('reviews')
                    ->withAvg('reviews', 'rating'),
                'thumbnail:mediable_id,mediable_type,type,path',
                'instructor.profile',
                'courses',
                'courses.category',
                'courses.subcategory',
                'courses.language',
                'courses.instructor.profile',
                'courses.pricing',
                'courses.curriculums',
                'courses' => fn($q) => $q->withCount('curriculums', 'videoCurriculums')->withSum('videoCurriculums', 'content_length')
            ],
            withAvg: ['reviews:rating'],
            withCount: [
                'courses'
            ],
            withSum: ['courses' => 'content_length'],
        );
    }

    #[Computed(persist: false)]
    public function bundleCourses()
    {
        return (new BundleService())->getBundleCourses(
            slug: $this->slug,
            relations: [
                'courses',
                'courses.category',
                'courses.subcategory',
                'courses.language',
                'courses.instructor.profile',
                'courses.pricing',
                'courses.curriculums',
            ],
            perPage: $this->perPage
        );
    }

    #[Computed(persist: false)]
    public function relatedBundles()
    {
        $excludedId = $this->bundle->id ?? null;
        return (new BundleService())->getAllBundles(
            with: ['thumbnail:mediable_id,mediable_type,type,path', 'instructor.profile'],
            withCount: ['courses'],
            withSum: ['courses' => 'content_length'],
            filters: $this->filters,
            perPage: 4,
            excluded: [$excludedId],
        );
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $bundle = $this->bundle;
        if (empty($bundle)) {
            abort(404);
        }
        $bundleCourses = $this->bundleCourses;
        $bundlesData = $this->relatedBundles;
        $currency = setting('_general.currency');
        $currency_detail = !empty($currency) ? currencyList($currency) : array();

        if (!empty($currency_detail['symbol'])) {
            $this->currency_symbol = $currency_detail['symbol'];
        }
        $currency_symbol = $this->currency_symbol;

        return view('coursebundles::livewire.bundle.bundle-details', compact('bundle', 'bundlesData', 'bundleCourses', 'currency_symbol'));
    }

    public function loadData()
    {
        $this->isLoading = false;
    }

    public function addToCart()
    {
        if (!auth()?->check()) {
            $this->dispatch(
                'showAlertMessage',
                type: 'error',
                message: __('general.login_error')
            );
            return;
        }

        if (!auth()?->user()?->role == 'student') {
            $this->dispatch(
                'showAlertMessage',
                type: 'error',
                message: __('coursebundles::bundles.only_student_can_add_to_cart')
            );
            return;
        }

        $data = [
            'id' => $this->bundle->id,
            'title' => $this->bundle->title,
            'price' => $this->bundle?->final_price ?? 0,
            'slug' => $this->bundle->slug,
            'image' => $this->bundle->thumbnail?->path,
        ];

        Cart::add(
            cartableId: $data['id'],
            cartableType: Bundle::class,
            name: $data['title'],
            qty: 1,
            price: $this->bundle?->final_price ?? 0,
            options: $data
        );
        $this->dispatch('cart-updated', cart_data: Cart::content(), discount: formatAmount(Cart::discount(), true), total: formatAmount(Cart::total(), true), subTotal: formatAmount(Cart::subtotal(), true), toggle_cart: 'open');
    }

    public function getFreeBundle()
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        // if (!auth()?->check()) {
        //     $this->dispatch(
        //         'showAlertMessage',
        //         type: 'error',
        //         message: __('courses::courses.login_required')
        //     );
        //     return;
        // }
        if (!auth()->check()) {
            session()->put('url.intended', request()->fullUrl());

            $this->dispatch(
                'showAlertMessage',
                type: 'error',
                message: __('courses::courses.login_required')
            );

            return redirect('/login');
        }

        if (!auth()?->user()?->role == 'student') {
            $this->dispatch(
                'showAlertMessage',
                type: 'error',
                message: __('courses::courses.not_allowed')
            );
            return;
        }

        $response = (new BookingService())->getFreeBundle($this->bundle->id);

        if (empty($response['success'])) {
            return $this->dispatch(
                'showAlertMessage',
                type: 'error',
                message: __($response['message'])
            );
        }

        return redirect()->route('courses.course-list');
    }
}
