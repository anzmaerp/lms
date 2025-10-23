<?php

namespace Modules\CourseBundles\Livewire\Pages\Search;

use App\Models\Language;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\CourseBundles\Models\Bundle;
use Modules\CourseBundles\Services\BundleService;

class SearchCoursesBundles extends Component
{
    use WithPagination;

    protected $bundleService;
    public $isLoading = true;
    public $perPage;
    public $parPageList = [10, 25, 50, 100];

    public $filters = [
        'keyword' => '',
        'min_price' => null,
        'max_price' => null,
        'statuses' => [Bundle::STATUS_PUBLISHED]
    ];

    public function mount()
    {
        $this->perPage = setting('_general.per_page_record') ?? 10;
    }

    public function boot(BundleService $bundleService)
    {
        $this->bundleService = $bundleService;
    }

    #[Layout('layouts.frontend-app')]
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

        return view('coursebundles::livewire.search.search-courses-bundles', compact('bundles', 'currency_symbol'));
    }

    public function loadData()
    {
        $this->isLoading = false;
        $this->dispatch('loadPageJs');
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }
}
