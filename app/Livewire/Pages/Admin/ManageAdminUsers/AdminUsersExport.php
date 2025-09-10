<?php

namespace App\Livewire\Pages\Admin\ManageAdminUsers;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AdminUsersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;
    protected $filterUser;
    protected $sortby;

    public function __construct($search = null, $filterUser = null, $sortby = 'desc')
    {
        $this->search     = $search;
        $this->filterUser = $filterUser;
        $this->sortby     = $sortby;
    }

    public function collection()
    {
        $query = User::select('id', 'email', 'phone', 'gender', 'created_at', 'status')
            ->with('profile:id,user_id,first_name,last_name')
            ->whereHas('roles', function ($q) {
                $q->where('name', 'sub_admin');
            });

        if (!empty($this->filterUser)) {
            $query = $this->filterUser === 'active' ? $query->active() : $query->inactive();
        }

        if (!empty($this->search)) {
            $query->whereHas('profile', function ($q) {
                $q->where(function ($sub) {
                    $sub->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%');
                });
            });
        }

        return $query->orderBy('id', $this->sortby)->get();
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->profile?->full_name ?? '',
            $user->email,
            $user->phone,
            ucfirst($user->gender),
            $user->status,
            $user->created_at->format('Y-m-d H:i:s'),
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('Name'),
            __('general.email'),
            __('general.phone'),
            __('auth.gender'),
            __('general.status'),
            __('general.created_date'),
        ];
    }
}
