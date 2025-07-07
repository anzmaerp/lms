<?php

namespace Modules\Assignments\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Assignments\Http\Requests\AssignmentRequest;
use Illuminate\Support\Str;
use App\Models\UserSubjectGroupSubject;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Services\BookingService;
use Modules\Assignments\Models\Assignment;
use Modules\Assignments\Http\Requests\ApiSubmitAssignmentRequest;
use Modules\Assignments\Http\Requests\SubmitAssignmentRequest;
use Modules\Assignments\Http\Resources\AssignmentResource;
use Modules\Assignments\Http\Resources\AssignmentDetailResource;
use Modules\Assignments\Http\Resources\AssignmentsCollection;
use Modules\Assignments\Http\Resources\AssignmentsListCollection;
use Modules\Assignments\Http\Resources\SubmissionAssignmentsCollection;
use Modules\Assignments\Http\Resources\Tutor\SubmissionResource;
use Modules\Assignments\Services\AssignemntsService;
use Modules\Assignments\Models\AssignmentSubmission;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SubjectService;

class AssignmentsController extends Controller
{
    use ApiResponser;

    protected $assignmentService;

    public function __construct()
    {
        $this->assignmentService    = new AssignemntsService();
    }

    public function getSubjects(Request $request)
    {
        try {
            $subjectGroups = (new SubjectService(Auth::user()))->getUserSubjectGroups(['subjects:id,name', 'group:id,name']);
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
                            'id' => $sbj->pivot->id,
                            'text' => $sbj->name,
                        ];
                    }
                }
                $formattedData[] = $groupData;
            }
            return $this->success(data: $formattedData, code: Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }
    }

    // Assignment Create or Update by Tutor

    public function createOrUpdateAssignment(AssignmentRequest $request)
    {
        $response = isDemoSite();

        if( $response ){
            return $this->error(message:  __('general.demosite_res_title'), code: Response::HTTP_FORBIDDEN);
        }

        $id = $request?->id ?? null;
        
        if($id){

            $assignment = $this->assignmentService->getAssigmentById($id);

            if(!$assignment){
                return $this->error(message: 'Assignment not found', code: Response::HTTP_NOT_FOUND);
            }

            if($assignment->instructor_id != Auth::id()){
                return $this->error(message: 'You are not authorized to update this assignment', code: Response::HTTP_FORBIDDEN);
            }

            if($assignment->status != Assignment::DRAFT){
                return $this->error(message: 'You are not authorized to update this assignment', code: Response::HTTP_FORBIDDEN);
            }
        }

        if (isActiveModule('Courses') && $request->get('assignment_for') === 'courses') {
            $assignment_for      = \Modules\Courses\Models\Course::class;
        } else {
            $assignment_for      = UserSubjectGroupSubject::class;
        }

        try {
            $request->dueTime = \Carbon\Carbon::parse($request->dueTime)->format('H:i:s');
        } catch (\Throwable $th) {
            $request->dueTime = null;
        }  

        try{
            DB::beginTransaction();

            $data = [
                'instructor_id'         => Auth::id(),
                'related_type'          => $assignment_for,
                'related_id'            => $request->related_id,
                'title'                 => $request->title,
                'description'           => $request->description,
                'total_marks'           => $request->total_marks,
                'passing_percentage'    => $request->passing_grade,
                'max_file_count'        => $request->max_file_upload ?? null,
                'max_file_size'         => $request->max_upload_file_size ?? null,
                'due_days'              => $request->dueDays,
                'due_time'              => $request->dueTime,
                'subject_slots'         => $request->user_subject_slots,
                'type'                  => $request->assignment_type,
                'characters_count'      => $request->charactersCount,  
                'status'                => 'draft',
            ];
            
            $assignment  = $this->assignmentService->createOrUpdateAssignment($id, $data);
            $media = [];
            if (!empty($request->existingAttachments)) {
                foreach($request->existingAttachments as $attachment) {
                    $media[] = [
                        'mediable_type' => Assignment::class,
                        'mediable_id'   => $assignment?->id,
                        'name'          => $attachment?->name ?? $attachment->getClientOriginalName(),
                        'type'          => $attachment?->type ?? (Str::startsWith($attachment->getMimeType(), 'image/') ? 'image' : 'file'), 
                        'path'          => $attachment?->path ?? setMediaPath($attachment),
                    ];
                }
            }
            $this->assignmentService->setAssignmentMedia($assignment, $media);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }

        $data = [
            'assignment' => $assignment,
            'media'      => $media
        ];

        return $this->success(data: $data, code: Response::HTTP_OK);
    }

    // Get Session List

    public function getSessionList(Request $request)
    {
        try {
            $id = $request->query('id');
            if (!$id) {
                return $this->error(message: 'ID is required', code: Response::HTTP_BAD_REQUEST);
            }

            $dateFormat        = setting('_general.date_format');
            $timeFormat        = setting('_lernen.time_format');
            
            $user_subject_slots = [];
            $slots = (new BookingService(Auth::user()))->getAvailableSubjectSlots($id, $dateFormat, $timeFormat);

            if(!$slots){
                return $this->error(message: 'No slots found', code: Response::HTTP_NOT_FOUND);
            }

            foreach($slots as $slot){
                $user_subject_slots[] = ['value' =>$slot['id'], 'text' => $slot['text']];
            }

            return $this->success(data: $user_subject_slots, code: Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }
    }

    // Delete Assignment
    public function deleteAssignment(Request $request)
    {
        try {
            if (isDemoSite()) {
                return $this->error(__('general.demosite_res_title'), Response::HTTP_FORBIDDEN);
            }
    
            $assignment = $this->getValidAssignment($request, ['draft']);
            if ($assignment instanceof \Illuminate\Http\JsonResponse) {
                return $assignment;
            }
    
            $deleted = $this->assignmentService->deleteAssignment($assignment->id);
    
            if (!$deleted) {
                return $this->error(message: 'Assignment not deleted', code: Response::HTTP_BAD_REQUEST);
            }
    
            return $this->success(message: 'Assignment deleted successfully', code: Response::HTTP_OK);
    
        } catch (\Throwable $e) {
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }
    }

    // Publish Assignment

    public function publishAssignment(Request $request)
    {
        try {
            $assignment = $this->getValidAssignment($request, ['draft','archived']);
            if ($assignment instanceof \Illuminate\Http\JsonResponse) {
                return $assignment;
            }
                
            $updated = $this->assignmentService->updateAssignmentStatus($assignment, 'published');
    
            if (!$updated) {
                return $this->error(message: 'Assignment not published', code: Response::HTTP_BAD_REQUEST);
            }
    
            return $this->success(message: 'Assignment published successfully', code: Response::HTTP_OK);
    
        } catch (\Throwable $e) {
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }
    }
    
    // Archive Assignment

    public function archiveAssignment(Request $request)
    {
        try {
            $assignment = $this->getValidAssignment($request, ['published']);
            if ($assignment instanceof \Illuminate\Http\JsonResponse) {
                return $assignment;
            }
    
            $updated = $this->assignmentService->updateAssignmentStatus($assignment, 'archived');
    
            if (!$updated) {
                return $this->error(message: 'Assignment not archived', code: Response::HTTP_BAD_REQUEST);
            }
    
            return $this->success(message: 'Assignment archived successfully', code: Response::HTTP_OK);
    
        } catch (\Throwable $e) {
            return $this->error($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Valid Assignment

    private function getValidAssignment(Request $request, array $allowedStatuses = [])
    {
        $id = $request->query('id');

        if (!$id) {
            return $this->error('ID is required', Response::HTTP_BAD_REQUEST);
        }

        $assignment = $this->assignmentService->getAssigmentById($id);

        if (!$assignment) {
            return $this->error(message: 'Assignment not found', code: Response::HTTP_NOT_FOUND);
        }

        if ($assignment->instructor_id !== Auth::id()) {
            return $this->error(message: 'You are not authorized to access this assignment', code: Response::HTTP_FORBIDDEN);
        }

        if (!empty($allowedStatuses) && !in_array($assignment->status, $allowedStatuses)) {
            $statuses = implode(' or ', $allowedStatuses);
            return $this->error(message: "Assignment must be in status: $statuses", code: Response::HTTP_BAD_REQUEST);
        }

        return $assignment;
    }

    // Get Course List

    public function getCourseList(Request $request)
    {
        try {
            $courses = (new \Modules\Courses\Services\CourseService())->getInstructorCourses(Auth::id(), [], ['title', 'id']);
            return $this->success(data: $courses, code: Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }
    }

    // Get Submission Assignment Detail

    public function getSubmissionAssignmentDetail(Request $request)
    {
   
        try {

            $id = $request->query('id');
            
            if(!$id){
                return $this->error('ID is required', Response::HTTP_BAD_REQUEST);
            }

            $submittedAssignment = $this->assignmentService->getAssignmentAttempt($id);

            if(empty($submittedAssignment) || $submittedAssignment->result == AssignmentSubmission::RESULT_ASSIGNED){
                return $this->error(message: 'Assignment not found', code: Response::HTTP_NOT_FOUND);
            }

            if($submittedAssignment?->assignment?->instructor_id != Auth::id()){
                return $this->error(message: 'Not authorized to access this assignment', code: Response::HTTP_FORBIDDEN);
            }

            $submittedAssignment->load(['student.profile', 'attachments']);
                
            return $this->success(data: new SubmissionResource($submittedAssignment), code: Response::HTTP_OK);

        } catch (\Exception $e) {
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }
    }

    // Submit Result

    public function submitResult(Request $request)
    {
        try {
            if (isDemoSite()) {
                return $this->error(__('general.demosite_res_title'), Response::HTTP_FORBIDDEN);
            }

            $marksAwarded = $request->marks_awarded ?? 0;

            if(!$request->id){
                return $this->error('ID is required', Response::HTTP_BAD_REQUEST);
            }

            $submittedAssignment = $this->assignmentService->getAssignmentAttempt($request->id);

            if(!$submittedAssignment){
                return $this->error(message: 'Assignment not found', code: Response::HTTP_NOT_FOUND);
            }

            if($submittedAssignment->assignment->instructor_id != Auth::id()){
                return $this->error(message: 'Not authorized to submit result for this assignment', code: Response::HTTP_FORBIDDEN);
            }

            if($submittedAssignment->result != AssignmentSubmission::RESULT_IN_REVIEW){
                return $this->error(message: 'Only in review assignments can be submitted result', code: Response::HTTP_BAD_REQUEST);
            }

            $percentage = ($marksAwarded / $submittedAssignment?->assignment?->total_marks) * 100;

            $status = $percentage >= $submittedAssignment?->assignment?->passing_percentage ? AssignmentSubmission::RESULT_PASSED : AssignmentSubmission::RESULT_FAILED;

            $this->assignmentService->updateSubmittedAssignment($submittedAssignment,['marks_awarded' => $marksAwarded, 'graded_at' => now(), 'result' => $status]);

            return $this->success(message: 'Result submitted successfully', code: Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->error(message:  $e->getMessage(), code: Response::HTTP_BAD_REQUEST);
        }
    }

    // Get Submission Assignments List
    public function getSubmissionAssignmentsList(Request $request)
    {
        $filters = [
            'keyword'        => $request->keyword,
            'status'         => $request->status,
        ];
        $this->user = Auth::user();
        $id = $request->query('id');
        
        if(!$id){
            return $this->error(message: 'ID is required', code: Response::HTTP_BAD_REQUEST);
        }
        $assignments = $this->assignmentService->getAttemptedAssignments(
            select:     [
                            '*',
                        ],
            relations:  [
                           'assignment',    
                           'student.profile',
                        ],
            tutorId:        $this->user->id,
            assignmentId:   $id,
            filters:        $filters,
            perPage:        $request->perPage,
            role:           $this->user->role,
        );    
        return $this->success(data: new SubmissionAssignmentsCollection($assignments), code: Response::HTTP_OK);
    }
    
    public function getAssignmentsList(Request $request)
    {
        try {
            $filters = [
                'keyword'        => $request->keyword,
                'status'         => $request->status,
            ];
            $assignmentsList = $this->assignmentService->getAssignments(
                instructorId: auth()->user()?->id,
                filters: array_filter($filters),
                perPage: $request->perPage ?? 10,
                withCount: ['submissionsAssignments'],
            );
            return $this->success(data: new AssignmentsListCollection($assignmentsList), code: Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->error(
                message: 'Failed to fetch assignments list.',
                code: Response::HTTP_INTERNAL_SERVER_ERROR,
            );
        }
    }
    
    public function assignmentDetail(Request $request){
        $assignment = $this->assignmentService->getAssignment($request->id,  withCount: ['submissionsAssignments']);
        return $this->success(data: new AssignmentDetailResource($assignment), code: Response::HTTP_OK);
    }

    // student  
    public function getAssignments(Request $request)
    {
        try {
            $filters = [
                'keyword'        => $request->keyword,
                'studentStatus'  => $request->studentStatus,
            ];

            $assignments = $this->assignmentService->getAttemptedAssignments(
                studentId: auth()->user()?->id,
                relations: ['assignment'],
                filters: array_filter($filters),
                perPage: $request->perPage ?? 10
            );

            return $this->success(data: new AssignmentsCollection($assignments), code: Response::HTTP_OK);
        } catch (\Exception $e) {
            return $this->error(
                message: 'Failed to fetch assignments.',
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    public function getAssignment(Request $request)
    {
        $id = $request->query('id');

        if(!$id){
            return $this->error('ID is required', Response::HTTP_BAD_REQUEST);
        }

        try {
            $assignment = $this->assignmentService->getStudentAssignment(
            relations: [
                'assignment:id,title,description,instructor_id,total_marks,passing_percentage,related_type,related_id,type,max_file_count',
                'assignment.instructor.profile:id,user_id,first_name,last_name,image,slug',
                'assignment.attachments',
                'attachments',
                
            ],
            submissionId: $id,
            studentId: auth()->user()?->id,
            );

            if (!$assignment) {
                return $this->error(message: 'Assignment not found.', code: Response::HTTP_NOT_FOUND);
            }
            
            return $this->success(data: new AssignmentResource($assignment), code: Response::HTTP_OK);

        } catch (\Exception $e) {
            
            return $this->error(message: 'Failed to fetch assignment details.', code: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function submitAssignment(ApiSubmitAssignmentRequest $request)
    {
        $response = isDemoSite();
        if ($response) {
            return $this->error(message: __('general.demosite_res_title'), code: Response::HTTP_FORBIDDEN);
        }


        $assignmentSubmission = $this->assignmentService->getStudentAssignment(
            relations: [
                'assignment:id,title,description,instructor_id,total_marks,passing_percentage,type,max_file_count,max_file_size,characters_count',
                'assignment.instructor.profile:id,user_id,first_name,last_name,image,slug',
                'assignment.attachments'
            ],
            submissionId: $request->assignment_id,
            studentId: Auth::id(),
        );

        if ($assignmentSubmission->submitted_at) {
            return $this->error(
                message: __('assignments::assignments.assignment_already_submitted'),
                code: Response::HTTP_BAD_REQUEST
            );
        }
        
        try {
            DB::beginTransaction();
    
            $media = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $attachment) {
                    $media[] = [
                        'mediable_type' => AssignmentSubmission::class,
                        'mediable_id'   => $assignmentSubmission->id,
                        'name'          => $attachment->getClientOriginalName(),
                        'type'          => Str::startsWith($attachment->getMimeType(), 'image/') ? 'image' : 'file',
                        'path'          => setMediaPath($attachment),
                    ];
                }
            }
    
            $data = [
                'assignment_id'     => $assignmentSubmission->assignment_id,
                'submitted_at'      => now(),
                'student_id'        => Auth::id(),
                'submission_text'   => $request->submission_text,
                'result'            => 'in_review',
            ];
            
            $submittedAssignment = $this->assignmentService->submitAssignment($data);
            if (!empty($media)) {
                $this->assignmentService->submitAssignmentMedia($submittedAssignment, $media);
            }
    
            DB::commit();
        } catch (\Exception $e) {

            DB::rollBack();
            return $this->error(message: __('assignments::assignments.assignment_submitted_error'), code: 500);
        }
    
        return $this->success(data: [
            'message' => __('assignments::assignments.assignment_submitted_successfully'),
        ]);
    }
}
