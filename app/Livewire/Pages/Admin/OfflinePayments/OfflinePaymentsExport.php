<?php

namespace App\Livewire\Pages\Admin\OfflinePayments;

use App\Models\OfflinePayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OfflinePaymentsExport implements FromCollection, WithHeadings
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = OfflinePayment::select('id', 'name', 'description', 'status', 'created_at');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('admin/payment.payment_name'),
            __('general.description'),
            __('general.status'),
            __('general.date'),
        ];
    }
}
