<?php

namespace Modules\Subscriptions\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class UserSubscription extends Model
{
    use HasFactory;

    protected $guarded = [];
    public static $statuses = ['expired' => 0, 'active' => 1];

    public $casts = [
        'credit_limits' => 'array',
        'remaining_credits' => 'array',
        'revenue_share' => 'array'
    ];

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

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userProfile(): HasOneThrough 
    {
        return $this->hasOneThrough(Profile::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    public function order()
    {
        return $this->hasOneThrough(Order::class, OrderItem::class, 'id', 'id', 'order_item_id', 'order_id');
    }
} 
