<?php

namespace Modules\IPManager\Services;

use Illuminate\Support\Facades\Auth;
use Modules\IPManager\Models\UserLog;
use Illuminate\Collection\Collection;
use Carbon\Carbon;

class UserLogsService
{
    public function getUserLogs(array $filters = [], array $with = [])
    {
        return UserLog::with($with)
            ->when($filters['search'] ?? null, function ($query, $search) {
                return $query->where('ip_address', 'LIKE', "%{$search}%");
            })
            ->when(!empty($filters['start_date']), function ($query) use ($filters) {
                $formattedDate = Carbon::createFromFormat('F-d-Y', $filters['start_date'])->format('Y-m-d');
                return $query->whereDate('session_start', '>=', $formattedDate);
            })
            ->when(!empty($filters['end_date']), function ($query) use ($filters) {
                $formattedEndDate = Carbon::createFromFormat('F-d-Y', $filters['end_date'])->format('Y-m-d');
                return $query->whereDate('session_end', '<=', $formattedEndDate);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                if ($status === 'open') {
                    return $query->whereNull('session_end'); 
                } elseif ($status === 'ended') {
                    return $query->whereNotNull('session_end');
                }
            })
            ->when($filters['sort'] ?? null, function ($query, $sort) {
                return $query->orderBy('id', $sort);
            })
            ->paginate(20);
    }

    public function getUserLogById($id)
    {
        return UserLog::where('id', $id)->first();
    }    
    
    public function updateUserLog($userId = null)
    {
        $userId = $userId ? $userId : Auth::user()->id;
        $userLog = UserLog::where('user_id', $userId)->whereNull('session_end')
            ->latest('id')->first();

        if ($userLog) {
            $userLog->update(['session_end' => now()]);
        }

        return $userLog;
    }

    public function endSessionUserLog($id)
    {
        return UserLog::where('id', $id)->update(['session_end' => now()]);
    }

    public function deleteUserLog($id)
    {
        return UserLog::where('id', $id)->delete();
    }
}