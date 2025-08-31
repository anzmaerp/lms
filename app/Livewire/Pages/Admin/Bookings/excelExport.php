<?php

namespace App\Livewire\Pages\Admin\Bookings;

use App\Services\OrderService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExcelExport implements FromCollection, WithHeadings
{
    use Exportable;

    private $status;
    private $search;
    private $sortby;
    private $selectedSubject;
    private $selectedSubGroup;

    public function __construct($status = null, $search = null, $sortby = 'desc', $selectedSubject = null, $selectedSubGroup = null)
    {
        $this->status           = $status;
        $this->search           = $search;
        $this->sortby           = $sortby;
        $this->selectedSubject  = $selectedSubject;
        $this->selectedSubGroup = $selectedSubGroup;
    }

    public function headings(): array
    {
        return [
            __('booking.id'),
            __('booking.transaction_id'),
            __('booking.subject'),
            __('booking.student_name'),
            __('booking.tutor_name'),
            __('booking.amount'),
            __('booking.tutor_payout'),
            __('booking.status'),
        ];
    }

    public function collection()
    {
        $orderService = new OrderService();
        $orders = $orderService->getBookings(
            $this->status,
            $this->search,
            $this->sortby,
            $this->selectedSubject,
            $this->selectedSubGroup,
            false 
        );

        return $orders->map(function ($order) {
            $options       = $order?->options ?? [];
            $subject       = $options['subject'] ?? '';
            $subjectGroup  = $options['subject_group'] ?? '';
            if (\Nwidart\Modules\Facades\Module::has('subscriptions') && \Nwidart\Modules\Facades\Module::isEnabled('subscriptions')) {
                $tutorPayout = $options['tutor_payout'] ?? 0;
            } else {
                $tutorPayout = $order?->price - getCommission($order?->price);
            }

            return [
                $order?->order_id,
                $order?->orders?->transaction_id ?? '-',
                $subject . ' (' . $subjectGroup . ')',
                $order?->orderable?->student?->first_name . ' ' . $order?->orderable?->student?->last_name,
                $order?->orderable?->tutor?->first_name,
                formatAmount($order?->price),
                formatAmount($tutorPayout),
                $order?->orders?->status,
            ];
        });
    }
}
