<?php

namespace Modules\Assignments\Models;

use App\Models\User;
use App\Models\UserSubjectGroupSubject;
use Illuminate\Database\Eloquent\Model;
use Modules\Assignments\Casts\AssignmentStatusCast;
use Modules\Assignments\Casts\AssignmentTypeCast;
use Modules\Assignments\Models\Media;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Courses\Models\Course;
use Modules\Assignments\Models\AssignmentSubmission;

class Assignment extends Model
{
    /*
    /* Assignment statuses
    */
    const STATUS_DRAFT       = 0;
    const STATUS_PUBLISHED   = 1;
    const STATUS_ARCHIVED    = 2;


     /*
    /* Assignment statuses
    */
    const DRAFT       = 'draft';
    const PUBLISHED   = 'published';
    const ARCHIVED    = 'archived';


    /**
     * Assignment types
     */
    const TYPE_TEXT          = 1;
    const TYPE_DOCUMENT      = 2;
    const TYPE_BOTH          = 3;
    

    public const STATUS_COLOR = [
        'draft'             => '#FFA500',
        'published'         => '#008000',
        'archived'          => '#808080',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * @return int The sum of the elements in the input array.
     */
    protected $fillable = [
        'title',
        'instructor_id',
        'description',
        'total_marks',
        'subject_slots',
        'passing_percentage',
        'due_days',
        'due_time',
        'type',
        'max_file_size',
        'max_file_count',
        'characters_count',
        'related_id',
        'related_type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status'                => AssignmentStatusCast::class,
        'type'                  => AssignmentTypeCast::class,
        'subject_slots'         => 'array', 
    ];

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('assignments.db_prefix') . 'assignments';
    }

    /**
     * Get the status color attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => Arr::get(self::STATUS_COLOR, $this->status, null)
        );
    }

    
    /**
     * Get the instructor of the assignment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }


    /**
     * Get the related model instance.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the thumbnail for the assignment.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function thumbnail(): Attribute
    {
        return Attribute::get(function () {
            if (isActiveModule('Courses') && $this->related_type == Course::class) {
                return $this->related?->thumbnail?->path;
            }

            if ($this->related_type == UserSubjectGroupSubject::class) {
                return $this->related?->image;
            }

            return null;
        });
    }


    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    /**
     * Get the attachments of the assignment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function attachments(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

     /**
     * Relationship: has many Assignment Attempt
     */
    public function submissionsAssignments()
    {
        return $this->hasMany(AssignmentSubmission::class);
    }

}
