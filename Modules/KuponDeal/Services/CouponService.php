<?php

namespace Modules\KuponDeal\Services;

use App\Models\UserSubjectGroupSubject;
use Modules\KuponDeal\Models\Coupon;

class CouponService
{

    public function updateOrCreateCoupon($data)
    {
        if (isset($data['couponable_id']) && is_array($data['couponable_id'])) {
            $data['couponable_id'] = json_encode($data['couponable_id']);
        }

        if (isset($data['conditions']) && is_array($data['conditions'])) {
            $data['conditions'] = json_encode($data['conditions']);
        }
        $coupon = Coupon::updateOrCreate(['id' => $data['id']], $data);
        return $coupon;
    }

    public function deleteCoupon($id)
    {
        $coupon = Coupon::find($id);
        if ($coupon) {
            $coupon->delete();
            return true;
        }
        return false;
    }

    public function getCoupons($userId, $status = 'active', $keyword = '', $where = [])
    {
        $query = Coupon::where('user_id', $userId);

        if ($status == 'active') {
            $query->whereDate('expiry_date', '>=', now()->toDateString());
        } else {
            $query->whereDate('expiry_date', '<', now()->toDateString());
        }

        if (!empty($where['couponable_type'])) {
            $query->where('couponable_type', $where['couponable_type']);
        }
        if (!empty($where['couponable_ids']) && is_array($where['couponable_ids'])) {
            $query->where(function ($q) use ($where) {
                foreach ($where['couponable_ids'] as $id) {
                    $q->orWhereRaw('JSON_CONTAINS(couponable_id, ?)', [json_encode((string)$id)]);
                }
            });
        }

        if ($keyword) {
            $query->where('code', 'like', '%' . $keyword . '%');
        }

$query->with('couponable');
     logger()->info('Final Coupon Query', ['sql' => $query->toSql(), 'bindings' => $query->getBindings()]);

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
