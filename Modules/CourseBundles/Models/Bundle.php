<?php

namespace Modules\CourseBundles\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Modules\CourseBundles\Casts\BundleStatusCast;
use Modules\Courses\Models\Course;
use Modules\Courses\Models\Media;

class Bundle extends Model
{
    protected $table;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = (config('coursebundles.db_prefix') ?? 'courses_') . 'bundles';
    }

    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_ARCHIVED = 2;

    public const STATUS_COLOR = [
        'draft' => '#FFA500',
        'published' => '#008000',
        'archived' => '#808080',
    ];

    protected $fillable = [
        'slug',
        'title',
        'short_description',
        'description',
        'price',
        'discount_percentage',
        'status',
        'created_by',
    ];

    protected $casts = [
        'status' => BundleStatusCast::class,
    ];

    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn() => Arr::get(self::STATUS_COLOR, $this->status, null)
        );
    }

    public function instructors(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'bundle_instructor', 'bundle_id', 'instructor_id')
            ->withTimestamps();
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function thumbnail(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')->where('type', 'thumbnail');
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(BundlePurchase::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(
            Course::class,
            (config('coursebundles.db_prefix') ?? 'courses_') . 'course_bundles',
            'bundle_id',
            'course_id'
        );
    }

    public function courseBundles(): HasMany
    {
        return $this->hasMany(CourseBundle::class);
    }

    public function media(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
