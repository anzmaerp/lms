<?php

namespace Modules\IPManager\Casts;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class RestrictionTypeCast implements CastsAttributes
{
    protected $type = [
        0 => 'specific_ip',
        1 => 'ip_range',
        2 => 'country',
    ];

    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        return isset($this->type[$value]) ? $this->type[$value] : null;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?int
    {
        return isset($value) ? array_search($value, $this->type) : null;
    }

}
