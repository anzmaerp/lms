<?php

namespace Modules\CourseBundles\Models;

use App\Models\User;
use Google\Service\Gmail\Draft;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Modules\CourseBundles\Casts\BundleStatusCast;
use Modules\Courses\Models\Course;
use Modules\Courses\Models\Media;

class Bundle extends Model
{

    protected $table;

    public function __construct()
    {
        $this->table = (config('coursebundles.db_prefix') ?? 'courses_') . 'bundles';
    }

    const STATUS_DRAFT          = 0;
    const STATUS_PUBLISHED      = 1;
    const STATUS_ARCHIVED       = 2;

    public const STATUS_COLOR = [
        'draft'             => '#FFA500',
        'published'         => '#008000',
        'archived'          => '#808080',
    ];


    protected $fillable = [
        'instructor_id',
        'slug',
        'title',
        'short_description',
        'description',
        'price',
        'discount_percentage',
        'status',
    ];

    protected $casts = [
        'status' => BundleStatusCast::class,
    ];

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
     * Get the instructor for the bundle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the thumbnail for the bundle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function thumbnail(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable');
    }

    
    /**
     * Get the purchases for the bundle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(BundlePurchase::class);
    }

    
    /**
     * Get the courses for the bundle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, (config('coursebundles.db_prefix') ?? 'courses_') . 'course_bundles', 'bundle_id', 'course_id');
    }
    
    
    /**
     * Get the course bundles for the bundle.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function courseBundles(): HasMany
    {
        return $this->hasMany(CourseBundle::class);
    }
    
     /**Get bundle media */
     public function media(): MorphOne
     {
         return $this->morphOne(Media::class, 'mediable');
     }
    
    
}
