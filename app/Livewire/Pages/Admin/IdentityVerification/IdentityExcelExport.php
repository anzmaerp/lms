<?php

namespace App\Livewire\Pages\Admin\IdentityVerification;

use App\Models\UserIdentityVerification;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class IdentityExcelExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $search;
    protected $sortby;
    protected $verification;

    public function __construct($search = '', $sortby = 'desc', $verification = '')
    {
        $this->search        = $search;
        $this->sortby        = $sortby;
        $this->verification  = $verification;
    }

    public function query()
    {
        $query = UserIdentityVerification::with([
            'address.country',
            'profile:id,user_id,verified_at',
        ]);

        if (!empty($this->verification)) {
            if ($this->verification === 'verified') {
                $query->whereNotNull('parent_verified_at');
            } elseif ($this->verification === 'non_verified') {
                $query->whereNull('parent_verified_at');
            }
        }

        if (!empty($this->search)) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('id', $this->sortby);
    }

    public function headings(): array
    {
        return [
            __('#'),
            __('general.name'),
            __('identity.country'),
            __('identity.gaurdian_info'),
            __('identity.school_info'),
            __('identity.identity_document'),
            __('identity.status'),
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user?->address?->country?->name ?? '-',
            sprintf(
                "Name: %s | Phone: %s | Email: %s | %s",
                $user->parent_name ?? '-',
                $user->parent_phone ?? '-',
                $user->parent_email ?? '-',
                $user->parent_verified_at ? 'Verified' : 'Not Verified'
            ),
            sprintf(
                "ID: %s | School: %s",
                $user->school_id ?? '-',
                $user->school_name ?? '-'
            ),
            $user->attachments 
                ? Storage::url($user->attachments) 
                : ($user->transcript ? Storage::url($user->transcript) : '-'),
            $user->status,
        ];
    }
}
