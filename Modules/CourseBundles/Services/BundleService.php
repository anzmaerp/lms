<?php

namespace Modules\CourseBundles\Services;

use Modules\CourseBundles\Models\BundlePurchase;
use Modules\CourseBundles\Models\Bundle;
use App\Casts\OrderStatusCast;
use App\Models\OrderItem;
use Illuminate\Support\Arr;
use Modules\CourseBundles\Casts\BundleStatusCast;
use Modules\CourseBundles\Models\CourseBundle;

class BundleService
{
    /**
     * Create a new course bundle.
     *
     * @param array $data
     * @return Bundle
     */
    public function createCourseBundle($data)
    {
        $bundle = Bundle::create($data);
        return $bundle;
    }

    /**
     * Update an existing course bundle.
     *
     * @param Bundle $bundle
     * @param array $data
     * @return Bundle
     */
    public function updateCourseBundle($bundle, $data)
    {
        $bundle->update($data);
        return $bundle;
    }

    /**
     * Sync courses to a bundle.
     *
     * @param Bundle $bundle
     * @param array $coursesIds
     * @return mixed
     */
    public function addBundleCourses($bundle, $coursesIds)
    {
        return $bundle->courses()->sync($coursesIds);
    }

    /**
     * Sync instructors to a bundle using the bundle_instructor pivot table.
     *
     * @param Bundle $bundle
     * @param array $instructorIds
     * @return mixed
     */
    public function addBundleInstructors($bundle, $instructorIds)
    {
        return $bundle->instructors()->sync($instructorIds);
    }

    /**
     * Add or update media for a bundle.
     *
     * @param Bundle $bundle
     * @param array $condition
     * @param array $media
     * @return Bundle
     */
    public function addBundleMedia(Bundle $bundle, array $condition = [], array $media)
    {
        $bundle->media()->delete();
        $bundle->media()->updateOrCreate($condition, $media);
        return $bundle;
    }

    /**
     * Get courses for a bundle by slug.
     *
     * @param string $slug
     * @param array $relations
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|null
     */
    public function getBundleCourses(string $slug, array $relations = [], int $perPage = 8)
    {
        $bundle = $this->getBundle(slug: $slug, relations: []);

        if (!$bundle) {
            return null;
        }

        return $bundle->courses()->withCount('ratings', 'curriculums')->withAvg('ratings', 'rating')->paginate($perPage);
    }

    /**
     * Get a bundle by ID or slug, optionally filtered by instructor.
     *
     * @param int|null $bundleId
     * @param string|null $slug
     * @param int|null $instructorId
     * @param array $relations
     * @param array $withAvg
     * @param string|null $status
     * @param array $withCount
     * @param array $withSum
     * @return Bundle|null
     */
    public function getBundle(int $bundleId = null, string $slug = null, int $instructorId = null, $relations = [], $withAvg = [], $status = null, $withCount = [], $withSum = [])
    {
        if (empty($bundleId) && empty($slug)) {
            return null;
        }

        $query = Bundle::with($relations)
            ->when($instructorId, function ($query, $instructorId) {
                return $query->whereHas('instructors', fn($q) => $q->where('instructor_id', $instructorId));
            })
            ->when($slug, function ($query, $slug) {
                return $query->where('slug', $slug);
            })
            ->when($bundleId, function ($query, $bundleId) {
                return $query->where('id', $bundleId);
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
     * Get bundles for a specific instructor.
     *
     * @param int $instructorId
     * @param array $status
     * @param array $select
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getInstructorBundles($instructorId, $status = [], $select = [])
    {
        return Bundle::whereHas('instructors', fn($query) => $query->where('instructor_id', $instructorId))
            ->when(!empty($status), fn($query) => $query->whereIn('status', $status))
            ->when(!empty($select), fn($query) => $query->select($select))
            ->get();
    }

    /**
     * Check if a bundle has been purchased.
     *
     * @param int $bundleId
     * @param int|null $studentId
     * @param int|null $tutorId
     * @return bool
     */
    public function getPurchasedBundles($bundleId, $studentId = null, $tutorId = null)
    {
        return BundlePurchase::where('bundle_id', $bundleId)
            ->when($studentId, function ($query, $studentId) {
                return $query->where('student_id', $studentId);
            })
            ->when($tutorId, function ($query, $tutorId) {
                return $query->where('tutor_id', $tutorId);
            })
            ->exists();
    }

    /**
     * Build the bundle query with applied filters, relationships, and aggregations.
     *
     * @param int|null $instructorId
     * @param int|null $studentId
     * @param array $with
     * @param array $filters
     * @param array $withCount
     * @param array $withAvg
     * @param array $withSum
     * @param array $excluded
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function buildBundleQuery($instructorId = null, $studentId = null, $with = [], $filters = [], $withCount = [], $withAvg = [], $withSum = [], $excluded = [])
    {
        $query = Bundle::query()
            ->when($instructorId, fn($query) => $query->whereHas('instructors', fn($q) => $q->where('instructor_id', $instructorId)))
            ->when($excluded, fn($query) => $query->whereNotIn('id', $excluded))
            ->when(!empty($filters['keyword']), fn($query) => $query->where('title', 'like', '%' . $filters['keyword'] . '%'))
            ->when(isset($filters['status']) && $filters['status'] != '', fn($query) => $query->where('status', $filters['status']))
            ->when(!empty($filters['statuses']), fn($query) => $query->whereIn('status', $filters['statuses']))
            ->when($studentId, fn($query) => $query->whereHas('courseBundles', fn($q) => $q->where('student_id', $studentId)))
            ->when(!empty($filters['min_price']), fn($query) => $query->where('final_price', '>=', $filters['min_price']))
            ->when(!empty($filters['max_price']), fn($query) => $query->where('final_price', '<=', $filters['max_price']))
            ->with($with);

        if (in_array('courses', $withCount)) {
            $query->withCount([
                'courses' => function ($q) use ($instructorId) {
                    if (!empty($instructorId)) {
                        $q->whereExists(function ($query) use ($instructorId) {
                            $query->select(\DB::raw(1))
                                ->from('bundle_instructor')
                                ->whereColumn('bundle_instructor.bundle_id', 'courses_bundles.id')
                                ->where('bundle_instructor.instructor_id', $instructorId);
                        });
                    }
                }
            ]);
        }

        foreach ($withCount as $relation) {
            if ($relation !== 'courses') {
                $query->withCount([$relation]);
            }
        }

        if (!empty($withSum) && isset($withSum['courses'])) {
            $query->withSum([
                'courses' => function ($q) use ($instructorId) {
                    if (!empty($instructorId)) {
                        $q->whereExists(function ($query) use ($instructorId) {
                            $query->select(\DB::raw(1))
                                ->from('bundle_instructor')
                                ->whereColumn('bundle_instructor.bundle_id', 'courses_bundles.id')
                                ->where('bundle_instructor.instructor_id', $instructorId);
                        });
                    }
                }
            ], $withSum['courses']);
        }

        foreach ($withSum as $relation => $column) {
            if ($relation !== 'courses') {
                $query->withSum($relation, $column);
            }
        }

        // Apply additional average calculations
        if (!empty($withAvg) && is_array($withAvg)) {
            foreach ($withAvg as $relationship => $column) {
                $query->withAvg($relationship, $column);
            }
        }

        // Apply sorting
        $query->when(!empty($filters['sort']), function ($query) use ($filters) {
            $query->orderBy('created_at', $filters['sort']);
        }, function ($query) {
            $query->orderBy('created_at', 'desc');
        });

        return $query;
    }

    /**
     * Get all bundles without pagination.
     *
     * @param int|null $instructorId
     * @param int|null $studentId
     * @param array $with
     * @param array $filters
     * @param array $withCount
     * @param array $withAvg
     * @param array $withSum
     * @param int|null $perPage
     * @param array $excluded
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllBundles($instructorId = null, $studentId = null, $with = [], $filters = [], $withCount = [], $withAvg = [], $withSum = [], $perPage = null, $excluded = [])
    {
        return $this->buildBundleQuery($instructorId, $studentId, $with, $filters, $withCount, $withAvg, $withSum, $excluded)->take($perPage)->get();
    }

    /**
     * Get all bundles with pagination.
     *
     * @param int|null $instructorId
     * @param int|null $studentId
     * @param array $with
     * @param array $filters
     * @param array $withCount
     * @param array $withAvg
     * @param array $withSum
     * @param int|null $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBundles(int $instructorId = null, $studentId = null, $with = [], array $filters = [], $withCount = [], $withAvg = [], $withSum = [], $perPage = null)
    {
        return $this->buildBundleQuery($instructorId, $studentId, $with, $filters, $withCount, $withAvg, $withSum)->paginate($perPage ?? 10);
    }

    /**
     * Get the count of published bundles for an instructor.
     *
     * @param int $instructorId
     * @return int
     */
    public function getInstructorBundlesCount($instructorId)
    {
        return Bundle::whereHas('instructors', fn($query) => $query->where('instructor_id', $instructorId))
            ->whereStatus(Bundle::STATUS_PUBLISHED)
            ->count();
    }

    /**
     * Update the status of a bundle.
     *
     * @param Bundle $bundle
     * @param string $status
     * @return bool
     */
    public function updateBundleStatus($bundle, $status)
    {
        if (!array_key_exists($status, BundleStatusCast::$statusMap)) {
            return false;
        }

        $bundle->status = $status;
        $bundle->save();
        return true;
    }

    /**
     * Add a bundle purchase record.
     *
     * @param array $bundleData
     * @return bool
     */
    public function addBundlePurchase($bundleData = [])
    {
        $isAdded = BundlePurchase::firstOrCreate(
            ['student_id' => $bundleData['student_id'], 'bundle_id' => $bundleData['bundle_id']],
            $bundleData
        );
        return $isAdded->wasRecentlyCreated;
    }

    /**
     * Delete a bundle.
     *
     * @param int $bundleId
     * @return bool
     */
    public function deleteBundle($bundleId)
    {
        $bundle = Bundle::find($bundleId);
        if ($bundle) {
            $bundle->delete();
            return true;
        }
        return false;
    }

    /**
     * Get bundle purchases with filtering and sorting.
     *
     * @param string|null $search
     * @param string|null $status
     * @param string|null $sortby
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getBundlePurchases($search, $status, $sortby)
    {
        $orders = OrderItem::withWhereHas('orders', function ($query) use ($status) {
            $query->select('id', 'status', 'transaction_id', 'user_id')->with('userProfile');
            if (isset(OrderStatusCast::$statuses[$status])) {
                $query->whereStatus(OrderStatusCast::$statuses[$status]);
            }
        })->whereHasMorph('orderable', [Bundle::class])
            ->with('orderable');

        if (!empty($search)) {
            $orders->where('title', 'like', '%' . $search . '%');
        }

        $orders = $orders->orderBy('id', $sortby ?? 'asc')
            ->paginate(setting('_general.per_page_opt') ?? 10);

        return $orders;
    }
}