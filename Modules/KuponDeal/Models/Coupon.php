<?php

namespace Modules\KuponDeal\Models;

use Modules\KuponDeal\Casts\DiscountTypeCast;
use Modules\KuponDeal\Casts\StatusCast;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $guarded = [];

    public $casts = [
        'status'                => StatusCast::class,
        'discount_type'         => DiscountTypeCast::class,
        'auto_apply'            => 'boolean',
        'conditions'            => 'array'
    ];

    public const CONDITION_FIRST_ORDER = 'first_purchase';
    public const CONDITION_MINIMUM_ORDER = 'minimum_order';

    public function couponable()
    {
        return $this->morphTo();
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
