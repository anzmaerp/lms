<?php

namespace Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $guarded = [];
    public static $statuses = ['deactive' => 0, 'active' => 1];
    public $casts = [
        'credit_limits'     => 'array',
        'revenue_share'     => 'array',
    ];

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function period() :Attribute
    {
        $periods = ['monthly' => 1, 'yearly' => 2, '6_months' => 3];   
        return new Attribute(
            get: fn($value) => array_search($value, $periods) ?: $value,
            set: fn($value) => $periods[$value] ?? $value,
        );
    }

    public function status() :Attribute
    {
        return new Attribute(
            get: fn($value) => array_search($value, self::$statuses) ?: $value,
            set: fn($value) => self::$statuses[$value] ?? $value,
        );
    }

    public function autoRenew() :Attribute
    {
        $values = ['no' => 0, 'yes' => 1];
        return new Attribute(
            get: fn($value) => array_search($value, $values) ?: $value,
            set: fn($value) => $values[$value] ?? $value,
        );
    }
    
} 
