<?php

namespace Modules\Assignments\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class AssignmentTypeCast implements CastsAttributes
{
    /**
     * Mapping of integer codes to status labels.
     *
     * @var array
     */
    public static $typeMap = [
        'text'         => 0,
        'document'     => 1,
        'both'      => 2,
    ];

    /**
     * Cast the given value for retrieval.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return array_search($value, self::$typeMap, true) ?: $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        // Convert the human-readable status to its integer equivalent, if it exists.
        return self::$typeMap[$value] ?? throw new InvalidArgumentException("Invalid status: $value");
    }
}
