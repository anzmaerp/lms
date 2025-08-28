<?php

namespace App\Livewire\Pages\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class excelExport implements FromQuery,WithHeadings
{

    private $formPage;
    public function __construct($page){
        $this->formPage = $page;
    }

    use Exportable;

    public function headings(): array {
        return [
                __('#' ),
                __('Name'),
                __('general.email_phone' ),
                __('general.created_date' ),
                __('admin/general.role'),
                __('general.email_verification' ),
                __('general.status'),
                // __('general.identity_verification'),
                // __('general.actions'),
            ];
    }

public function query()
{
    return User::query()
        ->select([
            'users.id',
            DB::raw("CONCAT(profiles.first_name, ' ', profiles.last_name) as name"),
            DB::raw("COALESCE(users.email, profiles.phone_number, users.phone) as email_phone"),
            'users.created_at',
            DB::raw("(SELECT GROUP_CONCAT(r.name) 
                      FROM model_has_roles mhr
                      INNER JOIN roles r ON r.id = mhr.role_id
                      WHERE mhr.model_id = users.id 
                      AND mhr.model_type = 'App\\\\Models\\\\User') as role"),
            DB::raw("CASE WHEN users.email_verified_at IS NOT NULL THEN 'Verified' ELSE 'Unverified' END as email_verification"),
            'users.status',
            // DB::raw("CASE WHEN identity_verifications.parent_verified_at IS NOT NULL THEN 'Verified' ELSE 'Unverified' END as identity_verification"),
            // DB::raw("'--' as actions")
        ])
        ->leftJoin('profiles', 'profiles.user_id', '=', 'users.id')
        // ->leftJoin('identity_verifications', 'identity_verifications.user_id', '=', 'users.id')
        ->whereHas('roles', function ($query) {
            $query->whereNotIn('roles.name', ['admin', 'sub_admin'])
                ->when($this->formPage->role, function ($query, $role) {
                    $query->where('name', $role);
                });
        })
        ->when(!empty($this->formPage->roles), function ($query) {
            $query->whereHas('roles', function ($subQuery) {
                $subQuery->where('name', $this->formPage->roles);
            });
        })
        ->when(!empty($this->formPage->filterUser), function ($query) {
            return $this->formPage->filterUser === 'active'
                ? $query->where('users.status', 'active')
                : $query->where('users.status', 'inactive');
        })
        ->when(!empty($this->formPage->verification), function ($query) {
            if ($this->formPage->verification === 'verified') {
                return $query->whereNotNull('users.email_verified_at');
            } elseif ($this->formPage->verification === 'unverified') {
                return $query->whereNull('users.email_verified_at');
            }
        })
        ->when(!empty($this->formPage->search), function ($query) {
            $query->whereHas('profile', function ($subQuery) {
                $subQuery->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->formPage->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->formPage->search . '%');
                });
            });
        })
        ->orderBy('users.id', 'desc');
}


    
}