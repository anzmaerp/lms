<?php

namespace Modules\Assignments\Livewire\Pages\Tutor\CreateAssignment;

use App\Models\User;
use Modules\Assignments\Http\Requests\AssignmentRequest;
use Modules\Assignments\Services\AssignemntsService;
use App\Models\UserSubjectGroupSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\BookingService;
use App\Services\SubjectService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Livewire\Component;
use Modules\Assignments\Casts\AssignmentTypeCast;
use Modules\Assignments\Models\Assignment;
use Illuminate\Support\Str;

class CreateAssignment extends Component
{
    use WithPagination;
    use WithFileUploads;

    public $assignments_for;
    public $related_id;
    public $assignment_for;
    public $assignment_type;
    public $assignmentTypes;
    public $total_marks;
    public $passing_grade;
    public $filesCountList;
    public $max_file_upload;
    public $max_upload_file_size;
    public $attachments = [];
    public $existingAttachments = [];
    public $allowedExtensions;
    public $maxUploadSize;
    public $description;
    public $dueDays;
    public $dueTime;
    public $assignment_id      = null;
    public $dateFormat;
    public $timeFormat;
    public $slots = [];
    public $user_subject_slots, $selectedSubjectSlots = [];
    public $related_ids        = [];
    public $title;  
    public $charactersCount;

    public $image_size;
    public $file_size;
    public $video_size;

    public $assignmentId;
    public $assignment;
    
    protected ?AssignmentRequest $request = null;
    protected AssignemntsService $assignemntsService;
    protected BookingService $bookingService;
    protected SubjectService $subjectService;
    protected ?User $user = null;

    public function boot()
    {
        $this->user                 = Auth::user();
        $this->assignemntsService   = new AssignemntsService();
        $this->request              = new AssignmentRequest();
        $this->bookingService       = new BookingService();
        $this->subjectService       = new SubjectService($this->user);
    }

    public function mount($id = null)
    {

        if(!empty($id) ) {
            if(!is_numeric($id)){
                abort(404);
            }

            $this->dateFormat        = setting('_general.date_format');
            $this->timeFormat        = setting('_lernen.time_format');
            $attachment_ext          = setting('_general.allowed_file_extensions') ?? 'pdf,doc,docx';
            $attachment_ext         .= "," . setting('_general.allowed_video_extensions') ?? ',mp4,webm,ogg';
            $this->image_size        = (int) (setting('_general.max_image_size') ?? '5');
            $this->file_size         = (int) (setting('_general.max_file_size') ?? '20');
            $this->video_size        = (int) (setting('_general.max_video_size') ?? '20');

            $this->assignmentId     = $id;
            $this->assignment       = $this->assignemntsService->getAssignment(
                assignmentId: $this->assignmentId,
                instructorId: $this->user->id,
                status: 'draft',
            );

            if(empty($this->assignment)){
                abort(404);
            }

            $this->getAssignment($this->assignment);

            $data = $this->initOptions($this->assignment->related_type);

            $eventData = [
                'quizzable_type' => $this->assignment->related_type,
                'option_list'    => $data,
                'session_slots'  => $session_slots ?? []
            ];
            $this->dispatch('editAssignment', eventData: $eventData);
            
            foreach ($this->assignment->attachments as $media) {
                $this->attachments[$media->path] = $this->existingAttachments[$media->path] = (object) [
                    'path' => $media->path,
                    'name' => $media->name,
                    'type' => $media->type,
                ];
            }

        }

        $this->dispatch('initSelect2', target: '.am-select2');
       
        $this->maxUploadSize     = max($this->image_size,$this->file_size,$this->video_size);
        $this->allowedExtensions = !empty( $attachment_ext ) ?  explode(',', $attachment_ext) : [];
        $this->assignmentTypes   = array_keys(AssignmentTypeCast::$typeMap);
        $this->filesCountList    = range(1, 10);
        
        $this->assignments_for = [
            [
                'label' => 'Subject',
                'value' => UserSubjectGroupSubject::class,
            ],
        ];  

        if (isActiveModule('Courses')) {
            $this->assignments_for[] = [
                'label' => 'Course',
                'value' => \Modules\Courses\Models\Course::class,
            ];
        } else {
            // $this->assignments_for = UserSubjectGroupSubject::class;
            $data = $this->initOptions($this->assignments_for);
            $this->dispatch('assignmentValuesUpdated', options: $data, reset: false);
        }
        
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('assignments::livewire.tutor.create-assignment.create-assignment');
    }

    public function updatedAttachments(){

        $allowed = $this->allowedExtensions ?: ['jpg', 'jpeg', 'png', 'svg', 'pdf', 'doc', 'docx','gif', 'webp','mp4','webm','ogg'];
        $maxMB = $this->maxUploadSize ?: 5;
        $maxKB = $maxMB * 1024;

        $this->validate([
            'attachments.*' => 'mimes:' . implode(',', $allowed) . '|max:' . $maxKB,
        ], [
            'attachments.*.max' => __('general.max_file_size_err', [
                'file_size' => $maxMB . 'MB',
            ]),
            'attachments.*.mimes' => __('general.invalid_file_type', [
                'file_types' => implode(', ', $allowed),
            ]),
        ]);
    
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
    
    public function updatedAssignmentType(){
        $this->dispatch('initSelect2', target: '.am-select2');
    }

    public function updatedAssignmentFor($value) {
        $data = $this->initOptions($value);
        $this->dispatch('assignmentValuesUpdated', options: $data, reset: true);
        if ($value == 'App\Models\UserSubjectGroupSubject') {
            $this->dispatch('initSelect2', target: '.am-select2');
            $this->dispatch('slotsList');
        }
    }   

    public function updatedRelatedId($value) {
        $this->slots = $this->bookingService->getAvailableSubjectSlots($value, $this->dateFormat, $this->timeFormat);
        foreach($this->slots as $slot){
            if(in_array($slot['id'], $this->user_subject_slots ?? [])){
                $this->selectedSubjectSlots[] = ['value' =>$slot['id'], 'text' => $slot['text']];
            }
        }
        $this->dispatch('addSlotsOptions', options: $this->slots);
    }   
    
    public function initOptions($type)
    {
        if ($type == \Modules\Courses\Models\Course::class) {
            $courses = (new \Modules\Courses\Services\CourseService())->getInstructorCourses(Auth::id(), [], ['title', 'id']);
            return $courses->map(fn($course) => ['text' => $course->title, 'id' => $course->id, 'selected' => !empty($this->related_id) ? $this->related_id == $course->id : false]) ?? [];
        } else if ($type == UserSubjectGroupSubject::class) {
            $subjectGroups = $this->subjectService->getUserSubjectGroups(['subjects:id,name', 'group:id,name']);
            $formattedData = [];
            foreach ($subjectGroups as $sbjGroup) {
                if ($sbjGroup->subjects->isEmpty()) {
                    continue;
                }
                $groupData = [
                    'text' => $sbjGroup->group->name,
                    'children' => []
                ];

                if ($sbjGroup->subjects) {
                    foreach ($sbjGroup->subjects as $sbj) {
                        $groupData['children'][] = [
                            'id'        => $sbj->pivot->id,
                            'text'      => $sbj->name,
                            'selected'  => !empty($this->related_id) ? $this->related_id == $sbj->pivot->id : false
                        ];
                    }
                }
                $formattedData[] = $groupData;
            }

            return $formattedData;
        }
    }

    public function saveAssignment()
    {
        if(isDemoSite()){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }

        try {
            $this->dueTime = \Carbon\Carbon::parse($this->dueTime)->format('H:i:s');
        } catch (\Throwable $th) {
            $this->dueTime = null;
        }   

        $this->validate($this->request->rules(), $this->request->messages(), $this->request->attributes());

        try{
            DB::beginTransaction();

            $data = [
                'instructor_id'         => Auth::id(),
                'related_type'          => $this->assignment_for,
                'related_id'            => $this->related_id,
                'title'                 => $this->title,
                'description'           => $this->description,
                'total_marks'           => $this->total_marks,
                'passing_percentage'    => $this->passing_grade,
                'due_days'              => $this->dueDays,
                'due_time'              => $this->dueTime,
                'subject_slots'         => $this->user_subject_slots,
                'type'                  => $this->assignment_type,
                'max_file_count'        => $this->max_file_upload,
                'max_file_size'         => $this->max_upload_file_size,
                'characters_count'      => $this->charactersCount,  
                'status'                => 'draft',
            ];
            $assignment  = $this->assignemntsService->createOrUpdateAssignment($this->assignmentId, $data);
            $media = [];
            if (!empty($this->existingAttachments)) {
                foreach($this->existingAttachments as $attachment) {
                    $media[] = [
                        'mediable_type' => Assignment::class,
                        'mediable_id'   => $assignment->id,
                        'name'          => $attachment->name ?? $attachment->getClientOriginalName(),
                        'type'          => $attachment->type ?? (Str::startsWith($attachment->getMimeType(), 'image/') ? 'image' : 'file'), 
                        'path'          => $attachment->path ?? setMediaPath($attachment),
                    ];
                }
            }
            $this->assignemntsService->setAssignmentMedia($assignment, $media);

            DB::commit();

            $message = $this->assignmentId ? __('assignments::assignments.assignment_updated') : __('assignments::assignments.assignment_created');

            $this->dispatch('showAlertMessage', type: 'success', title:  __('assignments::assignments.success') , message: $message);
            $this->redirectRoute('assignments.tutor.assignments-list');
        
        } catch (\Exception $e) {
            DB::rollBack();
            Log::info($e);
            $this->dispatch('showAlertMessage', type: 'error', title:  __('assignments::assignments.error') , message: __('assignments::assignments.assignment_created_error'));
        }
    }

    protected function getAssignment($assignment)
    {
        $this->title                = $assignment->title;
        $this->description          = $assignment->description;
        $this->total_marks          = $assignment->total_marks;
        $this->passing_grade        = $assignment->passing_percentage;
        $this->dueDays              = $assignment->due_days;
        $this->dueTime              = $assignment->due_time;
        $this->user_subject_slots   = $assignment->subject_slots;
        $this->assignment_type      = $assignment->type;
        $this->max_file_upload      = $assignment->max_file_count;
        $this->max_upload_file_size = $assignment->max_file_size;
        $this->charactersCount      = $assignment->characters_count;
        $this->assignment_type      = $assignment->type;
        $this->assignment_for       = get_class($assignment->related);
        $this->related_id           = $assignment->related_id;

        $this->initOptions($this->assignment_for);
        $this->dispatch('initSelect2', target: '.am-select2');

        if($this->assignment_for == 'App\Models\UserSubjectGroupSubject') {
            $this->updatedRelatedId($assignment->related_id);
        }

    }
}
