<?php

namespace Modules\Assignments\Livewire\Pages\Student\AsignmentList;


use Modules\Assignments\Models\AssignmentSubmission;
use Modules\Assignments\Services\AssignemntsService;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Component;


class AssignmentList extends Component
{
    use WithPagination;
    
    public $parPageList      = [10, 20, 30, 40, 50];
    public $isLoading        = true;
    public $filters          = [];
    public $assignmentId     = 0;
    public $perPage          = 10;
    public $dateFormat       = '';
    public $statuses         = [];
   
    public function mount()
    {
        $this->filters = [
            'keyword'        => '',
            'studentStatus'  => (string) AssignmentSubmission::ASSIGNED,
        ];

        $this->statuses = [
            AssignmentSubmission::ASSIGNED      =>  AssignmentSubmission::RESULT_ASSIGNED,
            AssignmentSubmission::IN_REVIEW     => 'attempted',
            'overdue'                           => 'overdue',
        ];

        $this->perPage          = setting('_general.per_page_opt') ?? 10;
        $this->dateFormat       = setting('_general.date_format') ?? 'M d, Y';
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $assignments = [];
        $assignments = (new AssignemntsService())->getAttemptedAssignments(
            studentId: auth()->user()?->id,
            relations: ['assignment'],
            filters: $this->filters,
            perPage: $this->perPage
        );

        return view('assignments::livewire.student.assignments-list.assignment-list' , compact('assignments'));
    }

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function loadData()
    {
        $this->isLoading = false;
    }

    public function filterStatus($status)
    {
        $this->filters['studentStatus'] = $status;
    }
}
