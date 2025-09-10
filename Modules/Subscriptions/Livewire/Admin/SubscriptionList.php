<?php

namespace Modules\Subscriptions\Livewire\Admin;

use Modules\Subscriptions\Services\SubscriptionService;
use App\Models\Role;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class SubscriptionList extends Component
{
    use WithPagination, WithFileUploads;

    public $subscription, $periods, $edit_id, $old_image;
    public $subscriptionDetail;
    public $allowImageExt       = [];
    public $allowImageSize      = '';
    public $editMode            = false;
    public $filters             = [];
    public $per_page_opt        = [];
    protected $paginationTheme  = 'bootstrap';
    
    protected $subscriptionService;
    public function boot(){
        $this->subscriptionService = new SubscriptionService();
    }

    public function mount(){
        $this->per_page_opt         = perPageOpt();
        $image_file_ext             = setting('_general.image_file_ext');
        $image_file_size            = setting('_general.image_file_size');
        $this->allowImageSize       = (int) !empty( $image_file_size ) ? $image_file_size : '3';
        $this->allowImageExt        = !empty( $image_file_ext ) ?  explode(',', $image_file_ext)  : ['jpg','png'];
        $this->filters['perPage']   = setting('_general.per_page_record', 10);
        $this->initSubscription();
        $this->periods = ['monthly' => __('subscriptions::subscription.monthly'), 'yearly' => __('subscriptions::subscription.yearly'), '6_months' => __('subscriptions::subscription.6_months')];
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $this->dispatch('initSelect2', target: '.am-select2');
        $roles = $this->roles;
        $subscriptions = $this->subscriptionService->getSubscriptions($this->filters);
        return view('subscriptions::livewire.admin.subscription-list', compact('roles', 'subscriptions'));
    }

    #[Computed]
    public function roles(){
        return Role::whereNotIn('name', ['admin','sub_admin'])->get()->pluck('name', 'id')->toArray();
    }

    public function preview($id){
        $this->subscriptionDetail = $this->subscriptionService->getSubscription($id);
        $this->dispatch('showSubscription');
    }

    public function edit($id){
        $this->edit_id = $id;
        $this->subscription = $this->subscriptionService->getSubscription($id)->toArray();
        $this->old_image = $this->subscription['image'] ?? null;
        $this->editMode = true;
        $this->resetErrorBag();
    }

    public function setSubscription(){
        if(isDemoSite()){
            $this->dispatch('showAlertMessage', type: 'error', message: __('general.demo_restriction'));
            return;
        }
        [$rules, $messages] = $this->getRulesAndMessages();
        if (is_string($this->subscription['image'])) {
            unset($rules['subscription.image']);
        }
        $this->validate($rules, $messages);
        $totalRevenuePool = 0;
        foreach($this->subscription['revenue_share'] as $key => $value){
            if ($key == 'admin_share') {
                continue;
            }
            $totalRevenuePool += $value;
        }
        if($totalRevenuePool > 100){
            $this->dispatch('showAlertMessage', type: 'error', message: __('subscriptions::subscription.revenue_pool_max'));
            return;
        }

        if (empty($this->edit_id)) {
            $this->subscription['status'] = 'active';
        }

        if( !empty($this->subscription['image']) &&   method_exists($this->subscription['image'],'temporaryUrl')){
            $this->subscription['image'] = Str::replace('public/', '' , $this->subscription['image']->store('subscriptions', getStorageDisk()));
        } elseif(!empty($this->old_image)) {
            $this->subscription['image'] = !empty($this->old_image) ? $this->old_image : null;
        }

        $response = $this->subscriptionService->setSubscription($this->subscription, $this->edit_id);
        if($response){
            if($this->edit_id){
                $this->dispatch('showAlertMessage', type: 'success', message: __('subscriptions::subscription.updated_msg'));
            } else {
                $this->dispatch('showAlertMessage', type: 'success', message: __('subscriptions::subscription.added_msg'));
            }
        } else {
            $this->dispatch('showAlertMessage', type: 'error', message: __('subscriptions::subscription.error_msg'));
        }
        $this->initSubscription();
    }

    public function removeImage()
    {
        $this->subscription['image'] = null;
        $this->old_image             = null;
    }

    public function updatedSubscription($value, $key)
    {
       if($key == 'role_id'){
           $this->initSubscription($value);
           $this->resetErrorBag();
       }
    }

    protected function initSubscription($roleId = 3){ // 3 is student role id
        $this->subscription = ['role_id' => $roleId, 'period' => '', 'status' => 'active', 'credit_limits' => [], 'revenue_share' => [], 'auto_renew' => 'no', 'image' => null];
        $this->getLimitOptions();
        $this->getRevenuePool();
        $this->editMode = false;
        $this->edit_id = null;
        $this->old_image = null;
    }

    protected function getRulesAndMessages(){
        $rules = [
            'subscription.name'           => 'required',
            'subscription.price'          => 'required|numeric',
            'subscription.image'          => 'required|image|mimes:'.join(',', $this->allowImageExt).'|max:'.$this->allowImageSize*1024,
            'subscription.period'         => 'required|in:monthly,yearly,6_months',
            'subscription.credit_limits'  => 'required|array',
        ];

        $messages = [
            'subscription.name.required'           => __('subscriptions::subscription.name_required'),
            'subscription.image.required'          => __('subscriptions::subscription.image_required'),
            'subscription.image.image'             => __('subscriptions::subscription.valid_image'),
            'subscription.image.mimes'             => __('subscriptions::subscription.vallid_extension', ['ext' => join(',', $this->allowImageExt)]),
            'subscription.image.max'               => __('subscriptions::subscription.image_max', ['size' => $this->allowImageSize*1024]),
            'subscription.price.required'          => __('subscriptions::subscription.price_required'),
            'subscription.period.required'         => __('subscriptions::subscription.period_required'),
            'subscription.price.numeric'           => __('subscriptions::subscription.price_numeric'),
        ];
        foreach($this->subscription['credit_limits'] as $key => $value){
            $rules["subscription.credit_limits.{$key}"]             = 'required|numeric';
            $messages["subscription.credit_limits.{$key}.required"] = __('subscriptions::subscription.'.$key.'_required');
            $messages["subscription.credit_limits.{$key}.numeric"]  = __('subscriptions::subscription.'.$key.'_numeric');
        }
        foreach($this->subscription['revenue_share'] as $key => $value){
            $rules["subscription.revenue_share.{$key}"] = 'required|numeric|max:100';
            $messages["subscription.revenue_share.{$key}.required"] = __('subscriptions::subscription.'.$key.'_required');
            $messages["subscription.revenue_share.{$key}.numeric"]  = __('subscriptions::subscription.'.$key.'_numeric');
            $messages["subscription.revenue_share.{$key}.max"]      = __('subscriptions::subscription.'.$key.'_max');
        }
        return [$rules, $messages];
    }

    protected function getLimitOptions(){
        $limits = [];
        if(($this->roles[$this->subscription['role_id']] ?? '') == 'student'){
            $limits = ['sessions' => ''];
            if( Module::has('courses') && Module::isEnabled('courses')){
                $limits['courses'] = '';
            }
        } else {
            $limits = ['sessions' => ''];
            if( Module::has('courses') && Module::isEnabled('courses')){
                $limits['courses'] = '';
            }
        }
        return $this->subscription['credit_limits'] = $limits;
    }

    protected function getRevenuePool(){
        $revenuePool = [];
        if(($this->roles[$this->subscription['role_id']] ?? '') == 'student'){
            $revenuePool = ['admin_share' => '', 'sessions_share' => ''];
            if( Module::has('courses') && Module::isEnabled('courses')){
                $revenuePool['courses_share'] = '';
            }
        }
        return $this->subscription['revenue_share'] = $revenuePool;
    }

    public function updatedFilters()
    {
        $this->resetPage();
    }

    public function printUsersExcel()
{
    if (isDemoSite()) {
        $this->dispatch('showAlertMessage', type: 'error', message: __('general.demo_restriction'));
        return;
    }

    return Excel::download(new SubscriptionsExport, 'subscriptions.xlsx');
}
}
