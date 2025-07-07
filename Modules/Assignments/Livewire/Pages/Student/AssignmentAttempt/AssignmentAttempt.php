<?php

namespace Modules\Assignments\Livewire\Pages\Student\AssignmentAttempt;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Modules\Assignments\Services\AssignemntsService;
use Modules\Assignments\Models\AssignmentSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AssignmentAttempt extends Component
{
    /**
     * Mount the component with the provided quiz ID.
     *
     * @return void
     */
    public $submissionId;
    public $dateFormat       = '';
    public $assignmentHeading = '';
    public $assignmentDetail = null;
    protected AssignemntsService $assignmentsService;

     
    public function boot(AssignemntsService $assignmentsService)
    {
        $this->assignmentsService = $assignmentsService;
    }

    public function mount($id)
    {
        $this->submissionId = $id;

        $this->dateFormat           = setting('_general.date_format') ?? 'M d, Y';
        $this->assignmentHeading    = setting('_assignments.attempt_assignment_heading');

        $this->assignmentDetail     = $this->assignmentsService->getStudentAssignment(
            relations: [
                'assignment:id,title,description,instructor_id,total_marks,passing_percentage',
                'assignment.instructor.profile:id,user_id,first_name,last_name,image,slug',
                'assignment.attachments'
            ],
            submissionId: $this->submissionId,
            studentId: Auth::id(),
        );
        if(empty($this->assignmentDetail) || $this->assignmentDetail->result != AssignmentSubmission::RESULT_ASSIGNED){
            abort(404);
        }
    }
    
    /**
     * Render the component view.
     *
     * @return \Illuminate\View\View
     */
    #[Layout('assignments::layouts.app')]
    public function render()
    {
        return view('assignments::livewire.student.assignment-attempt.assignment-attempt');
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
