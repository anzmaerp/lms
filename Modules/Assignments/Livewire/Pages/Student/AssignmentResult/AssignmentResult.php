<?php

namespace Modules\Assignments\Livewire\Pages\Student\AssignmentResult;

use Modules\Assignments\Services\AssignemntsService;
use Modules\Assignments\Models\AssignmentSubmission;
use Modules\Assignments\Models\Assignment;
use Modules\Upcertify\Models\Certificate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AssignmentResult extends Component
{

    public $assignmentDetail    = null;
    public $assignmentHeading   = '';
    public $submissionId;
    public $dateFormat          = '';

    protected AssignemntsService $assignmentsService;


    public function boot(AssignemntsService $assignmentsService)
    {
        $this->assignmentsService = $assignmentsService;
    }

    public function mount($submissionId)
    {
        $this->submissionId         = $submissionId;
        $this->dateFormat           = setting('_general.date_format') ?? 'M d, Y';
        $this->assignmentDetail     = $this->assignmentsService->getStudentAssignment(
            relations: [
                'assignment:id,title,description,instructor_id,total_marks,passing_percentage',
                'assignment.instructor.profile:id,user_id,first_name,last_name,image,slug',
                'attachments'
            ],  
            submissionId: $this->submissionId,
            studentId: Auth::id(),
        );

        if(empty($this->assignmentDetail) || $this->assignmentDetail->result == AssignmentSubmission::RESULT_ASSIGNED){
            abort(404);
        }
    }
    
    #[Layout('assignments::layouts.app')]
    public function render()
    {
        return view('assignments::livewire.student.assignment-result.assignment-result');
    }

    public function download($filePath)
    {
        if (!Storage::disk(getStorageDisk())->exists($filePath)) {
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.error') , message: __('assignments::assignments.attachment_downloaded_error'));
            return;
        }

        $this->dispatch('showAlertMessage', type: 'success', title:  __('general.success') , message: __('assignments::assignments.attachment_downloaded'));
        return Storage::disk(getStorageDisk())->download($filePath);
    }

}
