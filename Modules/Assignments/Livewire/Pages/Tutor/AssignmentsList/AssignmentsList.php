<?php

namespace Modules\Assignments\Livewire\Pages\Tutor\AssignmentsList;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Modules\Assignments\Services\AssignemntsService;
use Modules\Assignments\Casts\AssignmentStatusCast;
use Modules\Assignments\Casts\AssignmentTypeCast;
use Modules\Assignments\Models\Assignment;
use App\Models\User;
use App\Models\SlotBooking;
use Modules\Courses\Services\CourseService;
use App\Jobs\SendNotificationJob;
use App\Jobs\SendDbNotificationJob;

class AssignmentsList extends Component
{
    use WithPagination;

    public $isLoading           = true;
    public $filters             = ['status' => ''];
    public $assignmentStatus    = '';
    public $assignmentId        = 0;
    public $perPage             = 10;
    public $parPageList         = [10, 20, 30, 40, 50];
    public $statuses            = [];
    public $progress            = 0;

    public function mount()
    {
        $this->dispatch('initSelect2', target: '.am-select2');
        // $this->statuses =  AssignmentStatusCast::$statusMap;

        $this->statuses =  [
            Assignment::STATUS_DRAFT      =>  Assignment::DRAFT,
            Assignment::STATUS_PUBLISHED  =>  Assignment::PUBLISHED,
            Assignment::STATUS_ARCHIVED   =>  Assignment::ARCHIVED,

        ];
    }

    #[Layout('layouts.app')]
    public function render()
    {
        $assignments = [];
        $assignments = (new AssignemntsService())->getAssignments(
            instructorId: auth()->user()?->id,
            filters: $this->filters,
            perPage: $this->perPage,
            withCount: ['submissionsAssignments'],
        );
        return view('assignments::livewire.tutor.assignments-list.assignments-list', compact('assignments'));
    }

    public function resetFilters()
    {
        $this->filters = [];
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
        $this->filters['status'] = $status;
    }

    public function openPublishModal($id, $status)
    {
        $this->assignmentId = $id;
        $this->assignmentStatus = $status;
        $this->dispatch('toggleModel', id: 'course_completed_popup', action: 'show');
    }

    #[On('archive-assignment')]
    public function archiveAssignment($params = [])
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        return $this->updateAssignmentStatus($params['id'], 'archived');
    }

    public function publishAssignment()
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        $this->updateAssignmentStatus($this->assignmentId, 'published');

        $assignment = Assignment::where('id', $this->assignmentId)->first();

        if (
            $assignment
            && $assignment?->related_type == 'Modules\Courses\Models\Course'
            && isActiveModule('courses')
        ) {
            $assignment->load('related.enrollments');

            $courseService = new CourseService();
            if (method_exists($courseService, 'getCourseProgress')) {
                $courseDuration = $courseService->getCourseProgress(
                    courseId: $assignment?->related_id,
                    withSum: [
                        'courseWatchedtime' => 'duration'
                    ],
                    studentId: $assignment?->related?->enrollments->pluck('student_id')->toArray()
                );

                if (!empty($courseDuration?->course_watchedtime_sum_duration) && !empty($assignment?->related?->content_length)) {
                    foreach ($assignment?->related?->enrollments as $enrollment) {
                        $this->progress = floor(($courseDuration?->course_watchedtime_sum_duration / $assignment?->related?->content_length) * 100);
                        if ($this->progress >= 100) {
                            $this->assignAssignment($assignment?->related, $enrollment?->student_id);
                        }
                    }
                }
            }
        } else {
            if (isActiveModule('assignments')) {
                $bookings = \App\Models\SlotBooking::get();
                foreach ($bookings as $booking) {
                    $sessionEndDate = $booking->end_time;
                    if ($sessionEndDate && $sessionEndDate < now()) {

                        $assignments = (new AssignemntsService())->assignmentsBySlot($booking->user_subject_slot_id);

                        if ($assignments->isNotEmpty()) {
                            foreach ($assignments as $assignment) {
                                if ($assignment->status == 'published') {
                                    $assignmentDetail = (new AssignemntsService())
                                        ->assignAssignment($assignment->id, [$booking->student_id]);

                                    if ($assignmentDetail && isset($assignmentDetail->id)) {
                                        $emailData = [
                                            'assignmentTitle'       => $assignment->title,
                                            'studentName'           => $booking->student?->full_name,
                                            'tutorName'             => $assignment->tutor?->profile?->full_name,
                                            'assignedAssignmentUrl' => route('assignments.student.attempt-assignment', ['id' => $assignmentDetail->id]),
                                        ];

                                        $notifyData = $emailData;

                                        dispatch(new SendNotificationJob('assignedAssignment', $booking->booker, $emailData));
                                        dispatch(new SendDbNotificationJob('assignedassignment', $booking->booker, $notifyData));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->dispatch('toggleModel', id: 'course_completed_popup', action: 'hide');
    }

    public function assignAssignment($course, $studentId)
    {
        $student = User::with('profile')->find($studentId);
        $isAssigned = false;

        if (isActiveModule('assignments')) {
            $assignments = $course?->assignments;
            if (!empty($assignments)) {
                foreach ($assignments as $assignment) {
                    $isAlreadyAssigned = (new \Modules\Assignments\Services\AssignemntsService())
                        ->getAssignedAssignment($assignment->id, $studentId);

                    if (!$isAlreadyAssigned && $assignment->status == 'published') {
                        $isAssigned = true;

                        $assignmentDetail = (new \Modules\Assignments\Services\AssignemntsService())
                            ->assignAssignment($assignment->id, [$studentId]);

                        $assignmentData = Assignment::with('tutor.profile')
                            ->whereStatus(Assignment::STATUS_PUBLISHED)
                            ->find($assignmentDetail->assignment_id);

                        $emailData = [
                            'assignmentTitle'       => $assignment->title,
                            'studentName'           => $student?->profile?->full_name,
                            'tutorName'             => $assignmentData?->instructor?->profile?->full_name,
                            'assignedAssignmentUrl' => route('assignments.student.attempt-assignment', ['id' => $assignmentDetail?->id])
                        ];

                        $notifyData = $emailData;

                        dispatch(new SendNotificationJob('assignedAssignment', $student, $emailData));
                        dispatch(new SendDbNotificationJob('assignedassignment', $student, $notifyData));
                    }
                }
            }
        }

        return $isAssigned;
    }

    public function updateAssignmentStatus($assignmentId, $status)
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $assignment = (new AssignemntsService())->getAssignment($assignmentId);

        if (!$assignment) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('assignments::assignments.no_assignments_found'), message: __('assignments::assignments.no_assignments_found'));
            return;
        }

        $updated = (new AssignemntsService())->updateAssignmentStatus($assignment, $status);

        if ($updated) {
            $this->dispatch('showAlertMessage', type: 'success', title: __('assignments::assignments.assignment_status_success', ['status' => $status]), message: __('assignments::assignments.assignment_status_success', ['status' => $status]));
        } else {
            $this->dispatch('showAlertMessage', type: 'error', title: __('assignments::assignments.error_title'), message: __('assignments::assignments.assignment_status_failed'));
        }
    }

    #[On('delete-assignment')]
    public function deleteAssignment($params = [])
    {
        $response = isDemoSite();
        if ($response) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $deleted = (new AssignemntsService())->deleteAssignment($params['id']);
        if ($deleted) {
            $this->resetFilters();
            $this->dispatch('showAlertMessage', type: 'success', title: __('assignments::assignments.assignment_deleted_successfully'), message: __('assignments::assignments.assignment_deleted_successfully'));
        } else {
            $this->dispatch('showAlertMessage', type: 'error', title: __('assignments::assignments.assignment_delete_error'), message: __('assignments::assignments.assignment_delete_failed'));
        }
    }
}
