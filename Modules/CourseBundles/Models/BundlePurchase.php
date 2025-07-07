<?php

namespace Modules\CourseBundles\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Courses\Models\Course;

class BundlePurchase extends Model
{
    protected $table;

    public function __construct()
    {
        $this->table = (config('coursebundles.db_prefix') ?? 'courses_') . 'bundle_purchases';
    }


    protected $fillable = [
        'student_id',
        'tutor_id',
        'bundle_id',
        'purchased_price'
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tutor_id');
    }
}
