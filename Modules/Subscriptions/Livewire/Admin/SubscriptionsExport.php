<?php

namespace Modules\Subscriptions\Livewire\Admin;

use Modules\Subscriptions\Models\Subscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubscriptionsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Subscription::select('id', 'name', 'price', 'period', 'status', 'created_at')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Price',
            'Period',
            'Status',
            'Created At',
        ];
    }
}
