<?php

namespace Modules\KuponDeal\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class DiscountTypeCast implements CastsAttributes
{
    public static $discountTypes = [
        'fixed' => 0,
        'percentage' => 1,
    ];

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return array_search($value, self::$discountTypes) ?: $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return self::$discountTypes[$value] ?? $value;
    }

}
