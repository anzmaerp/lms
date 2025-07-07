<?php

namespace Modules\Assignments\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class AssignmentResultCast implements CastsAttributes
{
    /**
     * Mapping of integer codes to status labels.
     *
     * @var array
     */

    public static $resultMap = [
        'fail'             => 0,
        'pass'             => 1,
        'in_review'        => 2,
        'assigned'         => 3,
    ];

    /**
     * Cast the given value for retrieval.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return array_search($value, self::$resultMap, true) ?: $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Convert the human-readable status to its integer equivalent, if it exists.
        return self::$resultMap[$value] ?? throw new InvalidArgumentException("Invalid status: $value");
    }
}
