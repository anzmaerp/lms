<?php

namespace App\Livewire\Pages\Admin\Payments;

use App\Models\UserWithdrawal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WithdrawRequestsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;
    protected $filter;
    protected $sort;

    public function __construct($search = null, $filter = null, $sort = 'desc')
    {
        $this->search = $search;
        $this->filter = $filter;
        $this->sort   = $sort;
    }

    public function collection()
    {
        $query = UserWithdrawal::with('User:id,first_name,last_name');

        // search by user name
        if (!empty($this->search)) {
            $query->whereHas('User', function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%");
            });
        }

        // filter by status
        if (!empty($this->filter)) {
            $query->where('status', $this->filter);
        }

        return $query->orderBy('id', $this->sort)->get();
    }

    public function map($withdrawal): array
    {
        return [
            $withdrawal->id,
            $withdrawal->User?->full_name,
            $withdrawal->created_at->format('Y-m-d H:i:s'),
            formatAmount($withdrawal->amount),
            ucfirst($withdrawal->payout_method),
            ucfirst($withdrawal->status),
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('general.name'),
            __('general.date'),
            __('general.withdraw_amount'),
            __('general.payout_type'),
            __('general.status'),
        ];
    }
}
