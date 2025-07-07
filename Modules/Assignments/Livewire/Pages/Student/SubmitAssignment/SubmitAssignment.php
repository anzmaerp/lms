<?php

namespace Modules\Assignments\Livewire\Pages\Student\SubmitAssignment;

use Modules\Assignments\Services\AssignemntsService;
use Modules\Assignments\Models\Assignment;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Assignments\Http\Requests\SubmitAssignmentRequest;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\Component;
use Illuminate\Support\Str;
use Modules\Assignments\Models\AssignmentSubmission;
use App\Jobs\GenerateCertificateJob;
use App\Jobs\SendDbNotificationJob;
use App\Jobs\SendNotificationJob;

class SubmitAssignment extends Component
{     
    use WithFileUploads;
    public $assignmentId;
    public $assignmentDetail;
    public $attachments;
    public $existingAttachments = [];
    public $maxUploadSize;

    public $allowedExtensions;
    protected ?SubmitAssignmentRequest $request = null;
    public $description;
    public $dateFormat;
    
    protected AssignemntsService $assignmentsService;

    public function boot(AssignemntsService $assignmentsService)
    {
        $this->assignmentsService = $assignmentsService;
        $this->request              = new SubmitAssignmentRequest();
    }

    public function mount($id)
    {
        $this->assignmentId = $id;
        $this->dateFormat       = setting('_general.date_format') ?? 'M d, Y';
        $attachment_ext          = setting('_general.allowed_file_extensions') ?? 'pdf,doc,docx';
        $attachment_ext         .= "," . setting('_general.allowed_video_extensions') ?? ',mp4,webm,ogg';

    
        $this->allowedExtensions = !empty( $attachment_ext ) ?  explode(',', $attachment_ext) : [];
        
        $this->assignmentDetail = $this->assignmentsService->getStudentAssignment(
            relations: [
                'assignment:id,title,description,instructor_id,total_marks,passing_percentage,type,max_file_count,max_file_size,characters_count',
                'assignment.instructor.profile:id,user_id,first_name,last_name,image,slug',
                'assignment.attachments'
            ],
            submissionId: $this->assignmentId,
            studentId: Auth::id(),
        );

        if(empty($this->assignmentDetail) || ($this->assignmentDetail->result == AssignmentSubmission::RESULT_ASSIGNED && $this->assignmentDetail->ended_at <= now()) || $this->assignmentDetail->result != AssignmentSubmission::RESULT_ASSIGNED){
            abort(404);
        }
        
        $this->maxUploadSize     = $this->assignmentDetail->assignment->max_file_size;
    }

    
    public function updatedAttachments(){

        $this->validate([
                'attachments.*' => 'mimes:'.join(',', $this->allowedExtensions).'|max:'.$this->maxUploadSize*1024,
            ],[
                'max'           => __('general.max_file_size_err',  ['file_size'    => $this->maxUploadSize.'MB']),
                'mimes'         => __('general.invalid_file_type',  ['file_types'   =>  join(',', $this->allowedExtensions)])
            ]
        );

        foreach($this->attachments as $single){
            $filename = pathinfo($single->hashName(), PATHINFO_FILENAME);
            $this->existingAttachments[$filename] = (object) $single;
        }

    }

    
    public function removeAttachment( $key ){
        
        if(!empty($this->existingAttachments[$key])){
            unset($this->existingAttachments[$key]);
        }
        if(!empty($this->existingAttachments)){
            foreach($this->existingAttachments as $key=> $single){
                $this->existingAttachments[$key] = (object) $single;
            }
        }
    }
    public function openConfirmPopup(){
        $this->validate($this->request->rules($this->assignmentDetail->assignment->type, $this->assignmentDetail?->assignment?->max_file_count, $this->assignmentDetail?->assignment?->characters_count), 
        $this->request->messages());
        $this->dispatch('toggleModel', id: 'assignment_completed_popup', action: 'show');
    }
    
    public function submitAssignment(){
    

        $response = isDemoSite();
        if( $response ){
            return $this->error(data: null,message: __('general.demosite_res_txt'),code: Response::HTTP_FORBIDDEN);
            $this->dispatch('toggleModel', id: 'assignment_completed_popup', action: 'hide');
        }

        $studentId = Auth::user()->id;
        $student = User::with('profile')->find($studentId);

        try{
            DB::beginTransaction();

            $media = [];
            if (!empty($this->existingAttachments)) {
                foreach($this->existingAttachments as $attachment) {
                    $media[] = [
                        'mediable_type' => AssignmentSubmission::class,
                        'mediable_id'   => $this->assignmentDetail->id,
                        'name'          => $attachment->name ?? $attachment->getClientOriginalName(),
                        'type'          => $attachment->type ?? (Str::startsWith($attachment->getMimeType(), 'image/') ? 'image' : 'file'), 
                        'path'          => $attachment->path ?? setMediaPath($attachment),
                    ];
                }
            }

            $data = [
                'assignment_id'     => $this->assignmentDetail->assignment_id,
                'submitted_at'      => now(),
                'student_id'        => Auth::id(),
                'submission_text'   => $this->description,
                'result'            => 'in_review',
            ];
            $submittedAssignment = $this->assignmentsService->submitAssignment($data);
            if(!empty($this->existingAttachments)) {
                $this->assignmentsService->submitAssignmentMedia($submittedAssignment, $media);
            }
            

            $emailData = [
                'assignmentTitle'       => $this->assignmentDetail?->assignment?->title,
                'studentName'           => $student?->profile?->full_name,
                'tutorName'             => $this->assignmentDetail?->assignment?->instructor?->profile?->full_name,
                'reviewedAssignmentUrl' => route('assignments.tutor.mark-assignment', ['id' => $this->assignmentDetail?->id])
            ];

            $notifyData = [
                'assignmentTitle'       => $this->assignmentDetail?->assignment?->title,
                'studentName'           => $student?->profile?->full_name,
                'tutorName'             => $this->assignmentDetail?->assignment?->instructor?->profile?->full_name,
                'reviewedAssignmentUrl' => route('assignments.tutor.mark-assignment', ['id' => $this->assignmentDetail?->id])
            ];

            dispatch(new SendNotificationJob('reviewedAssignment', $this->assignmentDetail?->assignment?->instructor, $emailData));
            dispatch(new SendDbNotificationJob('reviewedAssignment', $this->assignmentDetail?->assignment?->instructor, $notifyData));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('showAlertMessage', type: 'error', title:  __('assignments::assignments.error') , message: __('assignments::assignments.assignment_submitted_error'));
        }
        if(empty($submittedAssignment)){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('assignments::assignments.error') , message: __('assignments::assignments.assignment_submitted_error'));
            $this->dispatch('toggleModel', id: 'assignment_completed_popup', action: 'hide');
            return;
        }
        return redirect()->route('assignments.student.assignment-result', $submittedAssignment->id);
    }


    #[Layout('assignments::layouts.app')]
    public function render()
    {
        return view('assignments::livewire.student.submit-assignment.submit-assignment');
    }
}
