<?php

namespace Modules\Subscriptions\Livewire\Admin;

use Modules\Subscriptions\Models\Subscription;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PurchasedSubscriptionsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        return (new \Modules\Subscriptions\Services\SubscriptionService())
            ->getPurchasedSubscriptions($this->filters, false); 
    }

    public function map($subscription): array
    {
        return [
            $subscription?->order?->id,
            $subscription?->order?->transaction_id,
            $subscription?->orderItem?->title,
            formatAmount($subscription?->orderItem?->price),
            $subscription?->expires_at ? \Carbon\Carbon::parse($subscription?->expires_at)->format('Y-m-d') : '',
            json_encode($subscription->credit_limits), 
            $subscription?->status,
        ];
    }

    public function headings(): array
    {
        return [
            'Order ID',
            'Transaction ID',
            'Subscription Name',
            'Price',
            'Valid Till',
            'Credit Limits',
            'Status',
        ];
    }
}
