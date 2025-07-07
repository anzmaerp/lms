<?php

namespace Modules\Assignments\Livewire\Pages\Tutor\AssignmentMark;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendDbNotificationJob;
use App\Models\User;
use Livewire\Attributes\Layout;
use Modules\Assignments\Models\AssignmentSubmission;
use Modules\Assignments\Services\AssignemntsService;

class AssignmentMark extends Component
{
    public $submittedAssignment;
    protected $assignmentService;
    public $isAuthor = false;
    public $marksAwarded = 0;

    public function boot()
    {
        $this->assignmentService = new AssignemntsService();
    }

    public function mount($id)
    {
        $this->submittedAssignment = $this->assignmentService->getAssignmentAttempt($id);
        if (empty($this->submittedAssignment) || $this->submittedAssignment->result == AssignmentSubmission::RESULT_ASSIGNED) {
            abort(404);
        } elseif ($this->submittedAssignment?->assignment->instructor_id != auth()->id()) {
            abort(403);
        }
        if ($this->submittedAssignment?->assignment->instructor_id == auth()->id()) {
            $this->isAuthor = true;
        }
    }

    public function downloadAttachment($attachment)
    {
        if (!Storage::disk(getStorageDisk())->exists($attachment)) {
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.error') , message: __('assignments::assignments.attachment_downloaded_error'));
            return;
        }
        return Storage::disk(getStorageDisk())->download($attachment);
    }

    public function proceedResult()
    {
        $this->validate([
            'marksAwarded' => 'required|numeric|min:0|max:' . $this->submittedAssignment->assignment->total_marks,
        ], [
            'required' => __('assignments::assignments.marks_field_required'),
            'min'      => __('assignments::assignments.marks_field_valid', ['max' => $this->submittedAssignment->assignment->total_marks]),
            'max'      => __('assignments::assignments.marks_field_valid', ['max' => $this->submittedAssignment->assignment->total_marks]),
            'numeric'  => __('assignments::assignments.marks_field_valid', ['max' => $this->submittedAssignment->assignment->total_marks])
        ]);
        $this->dispatch('toggleModel', id: 'result_completed_popup', action: 'show');
    }

    public function submitResult()
    {
        $percentage = ($this->marksAwarded / $this->submittedAssignment?->assignment?->total_marks) * 100;
        $status = $percentage >= $this->submittedAssignment?->assignment?->passing_percentage ? AssignmentSubmission::RESULT_PASSED : AssignmentSubmission::RESULT_FAILED;
        $this->assignmentService->updateSubmittedAssignment($this->submittedAssignment,['marks_awarded' => $this->marksAwarded, 'graded_at' => now(), 'result' => $status]);
        
        $student = User::with('profile')->find($this->submittedAssignment?->student_id);

        $emailData = [
            'assignmentTitle'       => $this->submittedAssignment?->assignment?->title,
            'studentName'           => $student?->profile?->full_name,
            'tutorName'             => $this->submittedAssignment?->assignment?->instructor?->profile?->full_name,
            'assignmentResultUrl'   => route('assignments.student.assignment-result', ['submissionId' => $this->submittedAssignment->id])
        ];

        $notifyData = [
            'assignmentTitle'       => $this->submittedAssignment?->assignment?->title,
            'studentName'           => $student?->profile?->full_name,
            'tutorName'             => $this->submittedAssignment?->assignment?->instructor?->profile?->full_name,
            'assignmentResultUrl'   => route('assignments.student.assignment-result', ['submissionId' => $this->submittedAssignment->id])
        ];

        dispatch(new SendNotificationJob('generateAssignmentResult', $student, $emailData));
        dispatch(new SendDbNotificationJob('generateAssignmentResult', $student, $notifyData));
        
        $this->dispatch('toggleModel', id: 'result_completed_popup', action: 'hide');
        $this->dispatch('showAlertMessage', type: 'success' , message: __('assignments::assignments.result_submitted'));
    }

    #[Layout('assignments::layouts.app')]
    public function render()
    {
        return view('assignments::livewire.tutor.assignment-mark.assignment-mark');
    }
}
