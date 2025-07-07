<?php

namespace Modules\Assignments\Services;
use Modules\Assignments\Models\Media;
use Modules\Assignments\Models\Assignment;
use Modules\Assignments\Casts\AssignmentStatusCast;
use Modules\Assignments\Casts\AssignmentTypeCast;
use Modules\Assignments\Models\AssignmentSubmission;
use App\Models\User;


class AssignemntsService
{

    /**
     * Get assignment
     *
     */
    public function assignment(int $assignmentId)
    {
        //
    }

    /**
     * Build the assignment query.
     *
     * @param int|null $instructorId
     * @param array $with
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    /**
     * Build the assignment query with applied filters, relationships, and aggregations.
     *
     * @param int|null $instructorId
     * @param array $with
     * @param array $filters
     * @param array $withCount
     * @param array $withAvg
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildAssignmentQuery($instructorId=null, $studentId=null, $with=[], $filters=[], $withCount=[], $withAvg=[], $withSum=[],$excluded = [])
    {
        $statusMap = [
            'draft'     => Assignment::STATUS_DRAFT,
            'published' => Assignment::STATUS_PUBLISHED,
            'archived'  => Assignment::STATUS_ARCHIVED,
        ];
        
        if (isset($filters['status']) && isset($statusMap[$filters['status']])) {
            $filters['status'] = $statusMap[$filters['status']];
        }
        
        $query = Assignment::query()
            // Filter by instructor ID if provided
            ->when($instructorId, fn($query) => $query->where('instructor_id', $instructorId))
            ->when($excluded, fn($query) => $query->whereNotIn('id', $excluded))
            // Apply various filters
            ->when(!empty($filters['keyword']), fn($query) => $query->where('title', 'like', '%' . $filters['keyword'] . '%'))
            ->when(isset($filters['status']) && $filters['status'] != '', fn($query) => $query->where('status', $filters['status']))
            ->when(
                !empty($filters['statuses']),
                fn($query) =>
                $query->whereIn('status', $filters['statuses'])
            )
           
            // Eager load relationships
            ->with($with)
            ->withCount($withCount)

            // Apply additional average calculations if provided
            ->when(!empty($withAvg) && is_array($withAvg), function ($query) use ($withAvg) {
                foreach ($withAvg as $relationship => $column) {
                    $query->withAvg($relationship, $column);
                }
            })
            // Apply sorting
            ->when(!empty($filters['sort']), function ($query) use ($filters) {
                $query->orderBy('created_at', $filters['sort']);
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            });

        return $query;
    }

     /**
     * Get assignment
     *
     * @param int|null $assignmentId
     * @param string|null $slug
     * @param int|null $instructorId
     * @param array $relations
     * @param array $withSum
     * @return Assignment|null
     */
    public function getAssignment(int $assignmentId = null, string $slug = null, int $instructorId = null, $relations = [], $withAvg = [], $status = null, $withCount = [], $withSum = [])
    {
        if (empty($assignmentId) && empty($slug)) {
            return null;
        }
       
        $query =  Assignment::with($relations)

            ->when($instructorId, function ($query, $instructorId) {
                return $query->where('instructor_id', $instructorId);
            })

            ->when($assignmentId, function ($query, $assignmentId) {
                return $query->where('id', $assignmentId);
            })

            ->when(isset($status), function ($query) use ($status) {
                return $query->where('status', $status);
            })

            ->withCount($withCount);

        if (!empty($withAvg) && is_array($withAvg)) {
            foreach ($withAvg as $relationship => $column) {
                $query->withAvg($relationship, $column);
            }
        }

        if (!empty($withSum) && is_array($withSum)) {
            foreach ($withSum as $relationship => $column) {
                $query->withSum($relationship, $column);
            }
        }
        return $query->first();
    }

         /**
     * Get assignment
     *
     * @param int|null $submissionId
     * @param string|null $slug
     * @param int|null $studentId
     * @param array $relations
     * @param array $withSum
     * @return StudentAssignment|null
     */
    public function getStudentAssignment(int $submissionId = null, string $slug = null, int $studentId = null, $relations = [], $withAvg = [], $status = null, $withCount = [], $withSum = [])
    {
        if (empty($submissionId)) {
            return null;
        }

        $query =  AssignmentSubmission::with($relations)

            ->when($studentId, function ($query, $studentId) {
                return $query->where('student_id', $studentId);
            })

            ->when($submissionId, function ($query, $assignmentId) {
                return $query->where('id', $assignmentId);
            })

            ->when($status, function ($query, $status) {
                return $query->where('status', $status);
            })

            ->withCount($withCount);

        if (!empty($withAvg) && is_array($withAvg)) {
            foreach ($withAvg as $relationship => $column) {
                $query->withAvg($relationship, $column);
            }
        }

        if (!empty($withSum) && is_array($withSum)) {
            foreach ($withSum as $relationship => $column) {
                $query->withSum($relationship, $column);
            }
        }

        return $query->first();
    }
    
     /**
     * Update the status of a assignment.
     *
     * @param int $assignmentId
     * @param string $status
     * @return bool
     */
    public function updateAssignmentStatus($assignment, $status)
    {
        if (! array_key_exists($status, AssignmentStatusCast::$statusMap)) {
            return false;
        }
    
        $assignment->status = $status;
        $assignment->save();
        return true;
    }

     /**
     * Get all assignments with pagination.
     *
     * @param int $perPage
     * @param int|null $instructorId
     * @param array $filters
     * @param array $with
     * @param array $withCount
     * @param array $withAvg
     * @param string|null $searchKeyword
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAssignments(int $instructorId = null, $studentId = null, $with = [], array $filters = [], $withCount = [], $withAvg = [], $withSum=[],  $perPage = null)
    {
        return $this->buildAssignmentQuery($instructorId, $studentId, $with, $filters, $withCount, $withAvg, $withSum)->paginate($perPage ?? 10);
    }

    public function createOrUpdateAssignment($id = null, $data)
    {
        return Assignment::updateOrCreate(
            ['id' => $id], 
            $data 
        );
    }
    
    public function setAssignmentMedia($assignment, $attachments)
    {
        $assignment->attachments()->delete();
        $assignment->attachments()->createMany($attachments);
    }

    /**
     * Retrieve paginated attempted assignments with optional filters.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAttemptedAssignments(?array $select = ['*'], ?array $relations = [], ?int $tutorId = null, array $filters = [], $assignmentId = null, int $studentId = null, array $withCount = [], $role = null ,$perPage = 10)
    {

        return $this->buildAttemptedAssignmentQuery(
            select:         $select,
            relations:      $relations,
            tutorId:        $tutorId,
            filters:        $filters,
            assignmentId:   $assignmentId,
            studentId:      $studentId,
            withCount:      $withCount,
            role:           $role
        )
            ->paginate($perPage);
    }

    /**
     * Build the base query for attempted assignments with optional filters.
     *
     * @param array|null $select
     * @param array|null $relations
     * @param int|null $tutorId
     * @param array $filters
     * @param int|null $assignmentId
     * @param int|null $studentId
     * @param array $withCount
     * @param string|null $role
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildAttemptedAssignmentQuery(?array $select, ?array $relations = [], ?int $tutorId = null, array $filters = [], $assignmentId = null, int $studentId = null, array $withCount = [], $role = null)
    {
        return AssignmentSubmission::query()

            ->select($select)

            ->when($tutorId, function ($query) use ($tutorId) {
                return $query->whereHas('assignment', function ($query) use ($tutorId) {
                    $query->where('instructor_id', $tutorId);
                });
            })

            ->when($studentId, function ($query) use ($studentId) {
                return $query->whereHas('student', function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                });
            })

            ->when($assignmentId, function ($query) use ($assignmentId) {
                return $query->where('assignment_id', $assignmentId);
            })

            ->when(isset($filters['studentStatus']), function ($query) use ($filters) {
                if ($filters['studentStatus'] === 'overdue') {
                    return $query->where('result', AssignmentSubmission::ASSIGNED)->where(function ($q) {
                        $q->Where('ended_at', '<=', now());
                    });
                }
                if ($filters['studentStatus'] === (string) AssignmentSubmission::ASSIGNED) {
                    return $query->where('result', AssignmentSubmission::ASSIGNED)->where(function ($q) {
                        $q->Where('ended_at', '>=', now());
                    });
                }
                if ($filters['studentStatus'] === (string) AssignmentSubmission::IN_REVIEW) {
                    return $query->where('result', AssignmentSubmission::IN_REVIEW)->orWhere('result', AssignmentSubmission::FAIL)->orWhere('result', AssignmentSubmission::PASS);
                }

                return $query->where('result', $filters['studentStatus']);
            })

            ->when(isset($filters['status']), function ($query) use ($filters) {
                if ($filters['status'] == 'upcoming') {
                    return $query->where('result', '=', AssignmentSubmission::ASSIGNED);
                } elseif ($filters['status'] == 'attempted') {
                    return $query->where('result', '!=', AssignmentSubmission::ASSIGNED);
                } else {
                    return $query->where('result', $filters['status']);
                }
            })

            ->when(!empty($filters['sort_by']), function ($query) use ($filters) {
                return $query->orderBy('created_at', $filters['sort_by']);
            })

            ->when(!empty($filters['keyword']), function ($query) use ($filters) {
                return $query->where(function ($q) use ($filters) {
                    $q->whereHas('assignment', function ($subQuery) use ($filters) {
                        $subQuery->where('title', 'like', "%{$filters['keyword']}%");
                    });
                });
            })

            // ->when(!empty($filters['keyword']), function ($query) use ($filters) {
            //     return $query->whereHas('student.profile', function ($subQuery) use ($filters) {
            //         $subQuery->where(function ($q) use ($filters) {
            //             $q->where('first_name', 'like', "%{$filters['keyword']}%")
            //                 ->orWhere('last_name', 'like', "%{$filters['keyword']}%");
            //         });
            //     }); 
            // })

            ->when(isset($filters['assignment_id']), function ($query) use ($filters) {
                return $query->where('assignment_id', $filters['assignment_id']);
            })

            ->when($role == 'tutor', function ($query) {
               
                return $query->where('result', '!=', AssignmentSubmission::ASSIGNED);
            })
          
            ->with($relations)
            ->withCount($withCount);
    }

    public function deleteAssignment($assignmentId)
    {
        $assignment = Assignment::find($assignmentId);
        if ($assignment) {
            $assignment->delete();
            return true;
        }
        return false;
    }

    public function getAssignmentAttempt($assignmmentAttempId)
    {
        return AssignmentSubmission::whereKey($assignmmentAttempId)->withWhereHas('assignment')->withWhereHas('student')->first();
    }

    public function submitAssignment($data)
    {
        return AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $data['assignment_id'],
            'student_id' => $data['student_id']],
            $data);
    }

    public function updateSubmittedAssignment($submittedAssignment, $data)
    {
        if ($submittedAssignment->update($data)) 
        {
            return $submittedAssignment;
        }
        return false;
    }

    public function submitAssignmentMedia($submittedAssignment, $media)
    {
        $submittedAssignment->attachments()->createMany($media);
    }

    public function assignmentsBySlot($slotId)
    {
        return Assignment::with('tutor.profile')->whereJsonContains('subject_slots', "$slotId")->get();
    }

    public function assignAssignment(int $assignmentId, array $studentIds)
    {
        if (empty($assignmentId) || empty($studentIds)) {
            return false;
        }

        $assignment = Assignment::with('tutor.profile')->whereStatus(Assignment::STATUS_PUBLISHED)->find($assignmentId);

        if (empty($assignment)) {
            return false;
        }

        foreach ($studentIds as $studentId) {

            $assignmentAttempt = AssignmentSubmission::where('assignment_id', $assignment->id)->where('student_id', $studentId)->first();
            if (!empty($assignmentAttempt)) {
                continue;
            }
          
            $student = User::find($studentId);
            if (!empty($student)) {
                $endedAt = now()->addDays($assignment->due_days)->setTimeFromTimeString($assignment->due_time);
                $detail = AssignmentSubmission::create(
                    [
                        'assignment_id'     => $assignment->id,
                        'student_id'        => $student->id,
                        'result'            => AssignmentSubmission::RESULT_ASSIGNED,
                        'ended_at'          => $endedAt,
                    ]   
                );
            }
            return $detail;
        }
    }

    public function getAssignedAssignment(int $assignmentId, int $studentId)
    {
        return AssignmentSubmission::where('assignment_id', $assignmentId)->where('student_id', $studentId)->exists();
    }

    public function getAssigmentById($id)
    {
        return Assignment::find($id);
    }
}
