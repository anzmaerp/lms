<?php

namespace Modules\KuponDeal\Services;

use App\Models\UserSubjectGroupSubject;
use Modules\KuponDeal\Models\Coupon;

class CouponService
{

    public function updateOrCreateCoupon($data)
    {
        $coupon = Coupon::updateOrCreate(['id' => $data['id']], $data);
        return $coupon;
    }

    public function deleteCoupon($id)
    {
        $coupon = Coupon::find($id);
        if($coupon) {
            $coupon->delete();
            return true;
        }
        return false;
    }

    public function getCoupons($userId, $status = 'active', $keyword = '', $where = [])
    {
        $query = Coupon::where('user_id', $userId);

        if($status == 'active') {
            $query->whereDate('expiry_date', '>=', now()->toDateString());
        } else {
            $query->whereDate('expiry_date', '<', now()->toDateString());
        }

        if(!empty($where)) {
            $query->where($where);
        }

        if($keyword) {
            $query->where('code', 'like', '%'.$keyword.'%');
        }

        $query->withWhereHas('couponable');

        return $query->orderBy('id', 'desc')->paginate(setting('_general.per_page_opt') ?? 10);
    }

    public function getCoupon($id)
    {
        return Coupon::find($id);
    }

    public function getAllCoupons($userId, $courseId, $couponableType)
    {
        return Coupon::where('user_id', $userId)->where('couponable_id', $courseId)->where('couponable_type', $couponableType)->get();
    }
}
