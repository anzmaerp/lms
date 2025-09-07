<?php

namespace Modules\Subscriptions\Livewire\Admin;

use App\Models\Role;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Subscriptions\Services\SubscriptionService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Modules\Subscriptions\Livewire\Admin\PurchasedSubscriptionsExport;


class PurchasedSubscriptions extends Component
{
    use WithPagination;

    public $filters             = [];
    public $per_page_opt        = [];
    protected $paginationTheme  = 'bootstrap';

    protected $subscriptionService;

    public function boot(){
        $this->subscriptionService = new SubscriptionService();
    }

    public function mount(){
        $this->per_page_opt         = perPageOpt();
        $this->filters['perPage']   = setting('_general.per_page_record', 10);
        $this->filters['status']    = 'active';
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $this->dispatch('initSelect2', target: '.am-select2');
        $roles = $this->roles;
        $subscriptions = $this->subscriptionService->getPurchasedSubscriptions($this->filters);
        return view('subscriptions::livewire.admin.purchased-subscriptions', compact('roles', 'subscriptions'));
    }

    #[Computed]
    public function roles(){
        return Role::where('name', '!=', 'admin')->get()->pluck('name', 'id')->toArray();
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

public function printUsersExcel(): BinaryFileResponse
{
    return Excel::download(new PurchasedSubscriptionsExport($this->filters), 'purchased_subscriptions.xlsx');
}
}
