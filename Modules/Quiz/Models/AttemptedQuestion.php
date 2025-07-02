<?php

namespace Modules\Quiz\Models;

use Illuminate\Database\Eloquent\Model;

class AttemptedQuestion extends Model
{
   /**
     * The attributes that are mass assignable.
     *
     * @var array
    */
    protected $fillable = [
       'quiz_attempt_id',
       'question_id',
       'question_option_id',
       'answer',
        'is_correct',
       'marks_awarded',
       'remarks',
    ];

    /**
     * Get the question associated with the attempted question.
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    
    public function getTable(): string
    {
        return config('quiz.db_prefix') . 'attempted_questions';
    }
}
