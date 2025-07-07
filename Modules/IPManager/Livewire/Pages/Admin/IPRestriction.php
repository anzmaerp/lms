<?php

namespace Modules\IPManager\Livewire\Pages\Admin;

use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Modules\IPManager\Services\IPRestrictionService;
use Modules\IPManager\Http\Requests\IPRestrictionRequest;
use Modules\IPManager\Models\IpRestriction as IPRestrictionModel;
use Livewire\Component;
use App\Models\Country;
use Livewire\Attributes\On;
use Livewire\Attributes\Layout;


class IPRestriction extends Component
{
    use WithPagination;
    public $user;
    public $countries;
    public $id;
    public $ip_address;
    public $ip_range;
    public $reason;
    public $country;
    public $type = 'specific_ip';
    private ?IPRestrictionService $restrictionService = null;
    private ?IPRestrictionRequest $ipRestrictionRequest = null;

    public $filters = [
        'keyword'       => '',
        'sort'          => 'desc'

    ];

    public function boot()
    {
        $this->user = Auth::user();
        $this->restrictionService = new IPRestrictionService();
        $this->ipRestrictionRequest = new IPRestrictionRequest();
    }

    public function mount()
    {
        $this->countries    = Country::get(['id','name']);
    }

    public function updatedType()
    {
        $this->ip_address = null;
        $this->ip_range = null;
        $this->country = null;
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $this->dispatch('initSelect2', target: '.am-select2');
        $ipRestrictions = $this->restrictionService->getIPRestrictions($this->filters);
        return view('ipmanager::livewire.admin.ip-restriction', [
            'ipRestrictions' => $ipRestrictions,
        ]);
    }

    public function resetInputfields(){
        $this->type = 'specific_ip';
        $this->id = null;
        $this->ip_address = null;
        $this->ip_range = null;
        $this->country = null;
        $this->reason = null;
    }

    public function showModal(){
        $this->resetErrorBag();
        $this->resetInputfields();
        $this->dispatch('toggleModel', id: 'tb-add-ip-restriction', action: 'show');
    }

    #[On('delete-ip-restriction')]
    public function deleteIPRestriction($params){
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }
        $ipRestriction = null;
        if(!empty($params['id'])){
            $ipRestriction = $this->restrictionService->deleteIPRestriction($params['id']);  
        }
        if($ipRestriction){
            $this->dispatch(
                'showAlertMessage',
                type: 'success',
                title: __('general.success_title') ,
                message: __('general.delete_record')
            );
        }
    }


    public function updateOrCreateIPRestriction(){
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }
        $this->validate($this->ipRestrictionRequest->rules($this->type, $this->id), $this->ipRestrictionRequest->messages());
        $ipRestriction = null;
        $data = [
            'type'          => $this->type,
            'reason'        => $this->reason,
        ];
        if($this->type == 'specific_ip'){
            $data['ip_start'] = $this->ip_address;
        } elseif($this->type == 'ip_range'){
            [$ip_start, $ip_end] = explode('-', $this->ip_range);
            $data['ip_start'] = $ip_start;
            $data['ip_end'] = $ip_end;
        } elseif($this->type == 'country'){
            $data['country'] = $this->country;
        }
        $ipRestriction = $this->restrictionService->updateOrCreateIPRestriction($this->id,$data);  
        $this->dispatch('toggleModel', id: 'tb-add-ip-restriction', action: 'hide');
        if($ipRestriction){
            $this->dispatch('showAlertMessage',type: 'success',title: __('general.success_title') ,message: $this->id ? __('ipmanager::ipmanager.update_record') : __('ipmanager::ipmanager.create_record'));
        }
        $this->resetInputfields();

    }

    public function editRestriction($id){
        $ipRestriction = $this->restrictionService->getIPRestrictionById($id); 
        $this->id = $id;
        $this->type = $ipRestriction->type;
        $this->reason = $ipRestriction->reason;
        if($this->type == 'specific_ip'){
            $this->ip_address = $ipRestriction->ip_start;
        } elseif($this->type == 'ip_range'){
            $this->ip_range = $ipRestriction->ip_start . '-' . $ipRestriction->ip_end;
        } elseif($this->type == 'country'){
            $this->country = $ipRestriction->country;
        }
        $this->dispatch('toggleModel', id: 'tb-add-ip-restriction', action: 'show');
    }
}