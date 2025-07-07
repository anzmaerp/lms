<?php

namespace Modules\CourseBundles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Courses\Models\Course;

class CourseBundle extends Model
{
    protected $table;
    public $timestamps = false;

    public function __construct()
    {
        $this->table = (config('coursebundles.db_prefix') ?? 'courses_') . 'course_bundles';
    }


    protected $fillable = [
        'bundle_id',
        'course_id',
    ];

    /**
     * Get the instructor for the bundle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function bundle(): BelongsTo
    {
        return $this->belongsTo(Bundle::class, 'bundle_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
