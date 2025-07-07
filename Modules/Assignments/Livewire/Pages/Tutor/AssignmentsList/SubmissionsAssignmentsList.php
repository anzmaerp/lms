<?php

namespace Modules\Assignments\Livewire\Pages\Tutor\AssignmentsList;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Modules\Assignments\Services\AssignemntsService;
use Modules\Assignments\Models\AssignmentSubmission;

class SubmissionsAssignmentsList extends Component
{
    use WithPagination;

    public $assignmentId;
    protected AssignemntsService $assignmentsService;
    public $user;
    public $showClearFilters        = false;
    public $isLoading = true;
    public $perPage     = 10;
    public $parPageList = [10, 20, 30, 40, 50];
    
    public $statuses  =  [  
        AssignmentSubmission::IN_REVIEW  => AssignmentSubmission::RESULT_IN_REVIEW, 
        AssignmentSubmission::PASS       => AssignmentSubmission::RESULT_PASSED,
        AssignmentSubmission::FAIL       => AssignmentSubmission::RESULT_FAILED
    ];

    public $filters   = [
        'keyword'    => null,
        'sort_by'    => 'desc',
        'status'     => null,
    ];

    public function boot(AssignemntsService $assignmentsService)
    {
        $this->user = Auth::user();
        $this->assignmentsService = $assignmentsService;
    }

    public function mount($assignmentId = null)
    {
        $this->assignmentId = $assignmentId;
        $this->showClearFilters = false;
        $this->perPage = !empty(setting('_general.per_page_record')) ? setting('_general.per_page_record') : 10;
        $this->dispatch('initSelect2', target: '.am-select2');  
        $assignment  = $this->assignmentsService->getAssignment($this->assignmentId);
        if(empty($assignment) || $assignment->status != 'published') {
            abort(404);
        }
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $assignment  = $this->assignmentsService->getAssignment($this->assignmentId,  withCount: ['submissionsAssignments']);
        $assignments = $this->assignmentsService->getAttemptedAssignments(
            select:     [
                            '*',
                        ],
            relations:  [
                           'assignment',    
                           'student.profile',
                        ],
            tutorId:        $this->user->id,
            assignmentId:   $this->assignmentId,
            filters:        $this->filters,
            perPage:        $this->perPage,
            role:           $this->user->role,
        );    
        return view('assignments::livewire.tutor.assignments-list.submissions-assignments-list' , compact('assignments', 'assignment'));
    }

    public function updatedFilters()
    {
        if($this->filters['status'] == '') {
            $this->filters['status'] = null;
        }
        $this->showClearFilters = true;
        $this->resetPage();
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }


    public function resetFilters()
    {
        $this->filters = [
            'keyword'          => null,
            'per_page'         => 10,
            'status'           => null,
        ];
        $this->showClearFilters = false;
        $this->resetPage();
        $this->dispatch('resetFilters');
    }

    // public function resetFilters()
    // {
    //     $this->filters = [];
    // }

    public function loadData()
    {
        $this->isLoading = false;
    }
}
