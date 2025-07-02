<?php

namespace Modules\Quiz\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Modules\Quiz\Casts\QuizStatusCast;
use Modules\Quiz\Models\QuizAttempt;
use Modules\Quiz\Models\Question;
use Modules\Quiz\Models\QuizSetting;
use App\Models\UserSubjectSlot;
use Google\Service\Gmail\Draft;

class Quiz extends Model
{
    /**
    * Quiz Statuses
    */
    const STATUS_DRAFT      = 'draft';
    const STATUS_PUBLISHED  = 'published';
    const STATUS_ARCHIVED   = 'archived';

    const DRAFT             = 0;
    const PUBLISHED         = 1;
    const ARCHIVED          = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     * @return int The sum of the elements in the input array.
     */
    public $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status'                => QuizStatusCast::class,
        'user_subject_slots'     => 'array',
    ];

    /**
     * A Quiz belongs to a Tutor (User).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tutor()
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }

    /**
    * Relationship: Quiz has many QuizMeta
     */
    public function settings()
    {
        return $this->hasMany(QuizSetting::class);
    }

    /**
     * Relationship: Quiz has many Questions
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Relationship: has many Quiz Attempt
     */
    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Relationship: Quiz can be associated with multiple models (polymorphic).
     */
    public function quizzable()
    {
        return $this->morphTo('quizzable');
    }


    public function thumbnail(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable');
        // ->whereType('thumbnail');
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('quiz.db_prefix') . 'quizzes';
    }
    
}
