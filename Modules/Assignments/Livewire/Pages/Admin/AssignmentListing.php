<?php

namespace Modules\Assignments\Livewire\Pages\Admin;

use App\Jobs\SendNotificationJob;
use Illuminate\Support\Facades\Auth;
use Modules\Assignments\Services\AssignmentService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class AssignmentListing extends Component
{
    use WithPagination;

    public $search = '';
    public $sortby = 'desc';
    public $status = '';
    public $user;
    public $underReviewStatus = '';
    private ?AssignmentService $assignmentService = null;

    public $filters = [
        'keyword'       => '',
        'status'        => '',
        'sort'          => 'desc'
    ];

    public $statuses = [
        'active',
        'need_revision',
        'under_review'
    ];


    public function boot()
    {
        $this->user = Auth::user();
        $this->assignmentService = new AssignmentService();
    }

    public function mount()
    {
        $this->filters['statuses'] = [
            'under_review',
            'need_revision',
            'active'
        ];
        $this->dispatch('initSelect2', target: '.am-select2');
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        // return view('courses::livewire.admin.course-listing', [
        //     'courses' => $courses
        // ]);
    }
}
