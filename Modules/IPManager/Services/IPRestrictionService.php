<?php

namespace Modules\IPManager\Services;

use Illuminate\Support\Facades\Auth;
use Modules\IPManager\Models\IpRestriction;
use Illuminate\Collection\Collection;

class IPRestrictionService
{
    public function getIPRestrictions(array $filters = [], array $with = [])
    {
        return IpRestriction::with($with)
            ->when($filters['search'] ?? null, function ($query, $search) {
                return $query->where('ip_address', 'LIKE', "%{$search}%");
            })
            ->when($filters['sort'] ?? null, function ($query, $sort) {
                return $query->orderBy('id', $sort);
            })
            ->paginate(20);
    }

    public function getIPRestrictionById($id)
    {
        return IpRestriction::find($id);
    }

    public function deleteIPRestriction($id)
    {
        return IpRestriction::find($id)->delete();
    }

    public function updateOrCreateIPRestriction($id,$data)
    {
        return IpRestriction::updateOrCreate(
            [
                'id' => $id,
            ],
            $data
        );
    }
    public function checkIPExists($ipAddress){
        return IpRestriction::where('ip_start', $ipAddress)->exists();
    }
}