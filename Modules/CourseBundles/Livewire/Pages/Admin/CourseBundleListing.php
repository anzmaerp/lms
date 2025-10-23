<?php

namespace Modules\CourseBundles\Livewire\Pages\Admin;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\CourseBundles\Casts\BundleStatusCast;
use Modules\CourseBundles\Models\Bundle;
use Modules\CourseBundles\Services\BundleService;

class CourseBundleListing extends Component
{
    use WithPagination;

    public $user;
    public $isLoading = true;
    public $perPage = 10;
    public $statuses = [];
    public $currency_symbol;
    protected $bundleService;

    public $filters = [
        'keyword' => '',
        'min_price' => null,
        'max_price' => null,
        'statuses' => [Bundle::STATUS_PUBLISHED]
    ];

    public function boot(BundleService $bundleService)
    {
        $this->bundleService = $bundleService;
        $this->user = Auth::user();
    }

    public function mount()
    {
        $this->perPage = setting('_general.per_page_record') ?? 10;
        $this->statuses = BundleStatusCast::$statusMap;
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $bundles = $this->bundleService->getBundles(
            with: ['thumbnail:mediable_id,mediable_type,type,path', 'instructor.profile'],
            withCount: ['courses'],
            withSum: ['courses' => 'content_length'],
            filters: $this->filters,
            perPage: $this->perPage
        );
        $currency = setting('_general.currency');
        $currency_detail = !empty($currency) ? currencyList($currency) : array();

        if (!empty($currency_detail['symbol'])) {
            $this->currency_symbol = $currency_detail['symbol'];
        }
        $currency_symbol = $this->currency_symbol;

        return view('coursebundles::livewire.tutor.bundle-listing.bundle-listing', compact('bundles', 'currency_symbol'));
    }

    public function loadData()
    {
        $this->isLoading = false;
    }

    #[On('delete-course-bundle')]
    public function deleteCourseBundle($params)
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        if (!empty($params['id'])) {
            $bundle = $this->bundleService->getPurchasedBundles($params['id']);
            if (!empty($bundle) && $bundle->isNotEmpty()) {
                $this->dispatch('showAlertMessage', type: 'error', title: __('general.error_title'), message: __('coursebundles::bundles.purchased_bundles'));
                return;
            }
            $bundle = $this->bundleService->deleteBundle($params['id']);
        }
        if ($bundle) {
            $this->dispatch(
                'showAlertMessage',
                type: 'success',
                title: __('general.success_title'),
                message: __('general.delete_record')
            );
        }
    }
}
