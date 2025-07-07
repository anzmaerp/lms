<?php

namespace Modules\IPManager\Livewire\Pages\Admin;

use Modules\IPManager\Services\UserLogsService;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendNotificationJob;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Illuminate\Support\Facades\File;
use Modules\IPManager\Services\IPRestrictionService;
use Livewire\WithPagination;

class LoginHistory extends Component
{
    use WithPagination;

    public $search              = '';
    public $sortby              = 'desc';
    public $ipAddress           = '';
    public $status              = '';
    public $reason              = '';
    public $userId;
    public $logId;
    public $user;
    public $underReviewStatus   = '';
    private ?IPRestrictionService $ipRestrictionService = null;
    private ?UserLogsService $userLogsService = null;

    public $filters = [
        'keyword'       => '',
        'status'        => '',
        'start_date'    => '',
        'end_date'      => '',
        'sort'          => 'desc'

    ];

    public function boot()
    {
        $this->user = Auth::user();
        $this->userLogsService = new UserLogsService();
        $this->ipRestrictionService = new IPRestrictionService();
    }

    public function mount()
    {

        $this->dispatch('initSelect2', target: '.am-select2');
    }
    public function openModal($ip_address,$logId,$userId){
        $this->ipAddress = $ip_address;
        $this->userId = $userId;
        $this->logId = $logId;
        $this->reason = '';
        $this->dispatch('toggleModel', id: 'tb-add-ip-restriction', action: 'show');
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $userLogs = $this->userLogsService->getUserLogs(with: ['user.profile'], filters: $this->filters);

        return view('ipmanager::livewire.admin.login-history', [
            'userLogs' => $userLogs
        ]);
    }

    #[On('delete-user-log')]
    public function deleteUserLog($params)
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        $userLog = $this->userLogsService->getUserLogById($params['id']);
        if (!empty($params['id']) && !empty($userLog) && !empty($userLog->session_id)) {
            $this->deleteUserSessions($userLog->session_id, $userLog->user_id);
            $userLog = $this->userLogsService->deleteUserLog($params['id']);
    
            if ($userLog) {
                $this->dispatch(
                    'showAlertMessage',
                    type: 'success',
                    title: __('general.success_title'),
                    message: __('general.delete_record')
                );
            }
        }
    }

    public function blockIp(){
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }
        $this->validate([
            'reason' => 'required',
        ]);
        $blockedIP = null;
        $ipExists = $this->ipRestrictionService->checkIPExists($this->ipAddress);
        if($ipExists){
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.error_title'), message: __('ipmanager::ipmanager.ip_exists'));
            $this->dispatch('toggleModel', id: 'tb-add-ip-restriction', action: 'hide');
            return;
        }
        if(!empty($this->ipAddress)){
            $data = [
                'type'          => 'specific_ip',
                'reason'        => $this->reason,
                'ip_start'      => $this->ipAddress,
            ];
            $blockedIP = $this->ipRestrictionService->updateOrCreateIPRestriction(null, $data); 
            $this->userLogsService->updateUserLog($this->userId);
            $userLog = $this->userLogsService->getUserLogById($this->logId);
            $this->deleteUserSessions($userLog->session_id, $userLog->user_id); 
        }
        $this->dispatch('toggleModel', id: 'tb-add-ip-restriction', action: 'hide');
        if($blockedIP){
            $this->dispatch(
                'showAlertMessage',
                type: 'success',
                title: __('general.success_title') ,
                message: __('ipmanager::ipmanager.block_ip_success')
            );
        }
    }

    #[On('end-session-user-log')]
    public function endSessionUserLog($params)
    {    
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        
        $userLog = $this->userLogsService->getUserLogById($params['id']);

        if (!empty($params['id']) && !empty($userLog) && !empty($userLog->session_id)) {
            $this->deleteUserSessions($userLog->session_id, $userLog->user_id);
        }
    

        $userLog = $this->userLogsService->endSessionUserLog($params['id']);
    
        if ($userLog) {
            $this->dispatch(
                'showAlertMessage',
                type: 'success',
                title: __('general.success_title'),
                message: __('ipmanager::ipmanager.end_session_success')
            );
        }
    }
    
    private function deleteUserSessions($sessionId, $user_id)
    {

        if ($sessionId) {
            $sessionFile = storage_path('framework/sessions/' . $sessionId);
            if (File::exists($sessionFile)) {
                File::delete($sessionFile);
            } 
        }

        if (Auth::id() == $user_id) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();
        }

    }

}
