<?php

namespace Modules\KuponDeal;

use Modules\KuponDeal\Casts\StatusCast;
use Modules\KuponDeal\Models\Coupon;
use App\Facades\Cart;
use App\Models\CartItem;
use App\Models\SlotBooking;
use App\Models\UserSubjectGroupSubject;
use Exception;
use Illuminate\Support\Carbon;

class KuponDeal
{
    /**
     * Apply a coupon to a specific cart item.
     *
     * @param string $couponCode - The coupon code entered by the user.
     * @param int $cartableId - The ID of the item the coupon is applied to.
     * @param string $cartableType - The type of item ('session' or 'course').
     * @return array - Response with status and message.
     */
    public function applyCoupon(string $couponCode)
    {
        try {
            $coupon = Coupon::where('code', $couponCode)
                ->where('status', StatusCast::$statuses['active'])
                ->first();

            if (empty($coupon)) {
                return [
                    'status' => 'error',
                    'message' => __('kupondeal::kupondeal.entered_coupon_is_not_valid'),
                ];
            }

            if ($coupon->expiry_date && Carbon::parse($coupon->expiry_date)->isPast()) {
                return [
                    'status' => 'error',
                    'message' => __('kupondeal::kupondeal.this_coupon_has_expired'),
                ];
            }

            $cartItems = $this->getCouponBasedCartItems($coupon);

            if ($cartItems->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => __('kupondeal::kupondeal.the_selected_item_is_not_in_the_cart'),
                ];
            }

            foreach ($cartItems as $cartItem) {
                if (!empty($cartItem->options['discount_code']) && $cartItem->options['discount_code'] == $coupon->code) {
                    continue;
                }

                $discountAmount = getDiscountedAmount($cartItem->price, $coupon->discount_type, $coupon->discount_value);

                if ($discountAmount > $cartItem->price) {
                    $discountAmount = $cartItem->price;
                }

                $options = $cartItem->options;
                $options['discount_code']  = $coupon->code;
                $options['discount_type']  = $coupon->discount_type;
                $options['discount_color'] = $coupon->color;
                $options['discount_value'] = $coupon->discount_value;
                $options['auto_apply']     = $coupon->auto_apply;
                Cart::update($cartItem->cartable_id, $cartItem->cartable_type, [
                    'discount_amount' => $discountAmount,
                    'options' => $options
                ]);
            }

            return [
                'status' => 'success',
                'message' => __('kupondeal::kupondeal.coupon_applied_successfully')
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Remove a coupon from a specific cart item.
     *
     * @param string $couponCode - The coupon code to be removed.
     * @return array - Response with status and message.
     */

    public function removeCoupon(string $couponCode)
    {
        try {
            $coupon = Coupon::where('code', $couponCode)
                ->where('status', StatusCast::$statuses['active'])
                ->first();

            if (empty($coupon)) {
                return [
                    'status' => 'error',
                    'message' => __('kupondeal::kupondeal.entered_coupon_is_not_valid'),
                ];
            }

            $cartItems = $this->getCouponBasedCartItems($coupon);

            if ($cartItems->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => __('kupondeal::kupondeal.the_selected_item_is_not_in_the_cart'),
                ];
            }

            foreach ($cartItems as $cartItem) {
                $options = $cartItem->options;
                unset($options['discount_code']);
                unset($options['discount_type']);
                unset($options['discount_color']);
                unset($options['discount_value']);
                Cart::update($cartItem->cartable_id, $cartItem->cartable_type, ['discount_amount' => 0, 'options' => $options]);
            }

            return [
                'status' => 'success',
                'message' => __('kupondeal::kupondeal.coupon_removed_successfully'),
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get Coupon Based Cart Items
     * @param Coupon $coupon
     * @return Collection
     */
    public function getCouponBasedCartItems(Coupon $coupon, $appliedDiscount = false)
    {
        $cartItems = collect();

        // Normalize couponable_id
        $couponIds = [];
        $raw = $coupon->couponable_id;

        if (!empty($raw)) {
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $couponIds = array_map('intval', $decoded);
                } elseif (is_numeric($raw)) {
                    $couponIds = [(int)$raw];
                }
            } elseif (is_numeric($raw)) {
                $couponIds = [(int)$raw];
            } elseif (is_array($raw)) {
                $couponIds = $raw;
            }
        }

        if ($coupon->couponable_type == UserSubjectGroupSubject::class) {
            $cartItems = CartItem::whereHasMorph('cartable', SlotBooking::class, function ($query) use ($couponIds) {
                $query->whereHas('slot.subjectGroupSubjects', function ($query) use ($couponIds) {
                    if (!empty($couponIds)) {
                        $query->whereIn('id', $couponIds);
                    }
                });
            })->when($appliedDiscount, function ($query) {
                $query->where('discount_amount', '>', 0);
            })->get();
        } else {
            $cartItems = CartItem::where('user_id', auth()->id())
                ->where('cartable_type', $coupon->couponable_type)
                ->when(!empty($couponIds), function ($query) use ($couponIds) {
                    $query->whereIn('cartable_id', $couponIds);
                })
                ->when($appliedDiscount, function ($query) {
                    $query->where('discount_amount', '>', 0);
                })
                ->get();
        }

        return $cartItems;
    }

    /** 
     * Get Coupon By Couponable
     * @param string $couponableType
     * @param int $couponableId
     * @return Coupon|null
     */
    public function getCouponByCouponable($couponableType, $couponableId)
    {
        return Coupon::where('couponable_type', $couponableType)->where('couponable_id', $couponableId)->first();
    }

    /**
     * Cart Has Coupon 
     * @param Coupon $coupon
     * @return bool
     */
    public function cartHasCoupon(Coupon $coupon)
    {
        $cartItems = $this->getCouponBasedCartItems($coupon, true);
        return !$cartItems->isEmpty();
    }

    /**
     * Apply Coupon If Available
     * @param int $couponableId
     * @param string $couponableType
     * @return array
     */
    public function applyCouponIfAvailable($couponableId, $couponableType)
    {
        $coupon = $this->getCouponByCouponable($couponableType, $couponableId);
        if (!empty($coupon)) {
            if ($this->cartHasCoupon($coupon)) {
                return $this->applyCoupon($coupon->code);
            }

            if ($coupon->auto_apply && Carbon::parse($coupon->expiry_date)->isFuture()) {
                return $this->applyCoupon($coupon->code);
            }
        }

        return [
            'status' => 'success',
            'message' => __('kupondeal::kupondeal.coupon_not_available'),
        ];
    }

    public function getCouponConditions($couponCode)
    {
        return Coupon::select('id', 'conditions')->where('code', $couponCode)
            ->where('status', StatusCast::$statuses['active'])
            ->first();
    }
}
