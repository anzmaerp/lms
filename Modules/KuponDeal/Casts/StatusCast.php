<?php

namespace Modules\KuponDeal\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class StatusCast implements CastsAttributes
{
    public static $statuses = [
        'inactive' => 0,
        'active' => 1,
    ];

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return array_search($value, self::$statuses) ?: $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return self::$statuses[$value] ?? $value;
    }

}
