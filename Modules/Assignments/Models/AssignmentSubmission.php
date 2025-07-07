<?php

namespace Modules\Assignments\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Assignments\Casts\AssignmentStatusCast;
use Modules\Assignments\Casts\AssignmentTypeCast;
use Modules\Assignments\Models\Assignment;
use App\Models\User;
use Modules\Assignments\Casts\AssignmentResultCast;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Arr;

class AssignmentSubmission extends Model
{
    
    /**
     * Assignment Results
     */
    const RESULT_ASSIGNED       = 'assigned';
    const RESULT_IN_REVIEW      = 'in_review';
    const RESULT_PASSED         = 'pass';
    const RESULT_FAILED         = 'fail';

    /**
     * Assignment Statuses
     */
    const FAIL                  = '0';
    const PASS                  = '1';
    const IN_REVIEW             = '2';
    const ASSIGNED              = '3';


    public const RESULT_COLOR = [
        'fail'              => '#FF0000',
        'pass'              => '#008000',
        'in_review'         => '#0000FF',
        'assigned'          => '#808080',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * @return int The sum of the elements in the input array.
     */
    protected $fillable = [
        'assignment_id',
        'student_id',
        'submission_text',
        'graded_at',
        'marks_awarded',
        'submitted_at',
        'result',
        'graded_at',
        'ended_at',
        'feedback',
    ];
    

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'result'                => AssignmentResultCast::class,
    ];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('assignments.db_prefix') . 'assignment_submissions';
    }

    /**
     * Get the status color attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function resultColor(): Attribute
    {
        return Attribute::make(
            get: fn() => Arr::get(self::RESULT_COLOR, $this->result, null)
        );
    }


    public function assignment() 
    {
        return $this->belongsTo(Assignment::class);
    }
    

    /**
     * Relationship: AssignmentSubmission belongs to a Student
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

        /**
     * Get the attachments of the assignment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments()
    {
        return $this->morphMany(Media::class, 'mediable');
    }
}
