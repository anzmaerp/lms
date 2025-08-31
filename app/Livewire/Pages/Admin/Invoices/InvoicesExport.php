<?php

namespace App\Livewire\Pages\Admin\Invoices;

use App\Services\OrderService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\Storage;


class InvoicesExport implements FromCollection, WithHeadings
{
    use Exportable;

    private $status;
    private $search;
    private $sortby;

    public function __construct($status = null, $search = null, $sortby = 'desc')
    {
        $this->status = $status;
        $this->search = $search;
        $this->sortby = $sortby;
    }

    public function headings(): array
    {
        return [
            __('booking.id'),
            __('booking.transaction_id'),
            __('general.attached_file'),
            __('booking.items'),
            __('booking.student_name'),
            __('booking.payment_method'),
            __('booking.amount'),
            __('booking.admin_commission'),
            __('booking.status'),
        ];
    }

    public function collection()
    {
        $orderService = new OrderService();
        $orders = $orderService->getOrdersList(
            $this->status,
            $this->search,
            $this->sortby,
            false 
        );

        return $orders->map(function ($order) {
            $itemsCount = [];

            if (!empty($order?->slot_bookings_count)) {
                $itemsCount[] = $order?->slot_bookings_count == 1
                    ? __('booking.session_count', ['count' => $order?->slot_bookings_count])
                    : __('booking.sessions_count', ['count' => $order?->slot_bookings_count]);
            }

            if (\Nwidart\Modules\Facades\Module::has('courses') &&
                \Nwidart\Modules\Facades\Module::isEnabled('courses') &&
                !empty($order?->courses_count)) {
                $itemsCount[] = $order?->courses_count == 1
                    ? __('booking.course_count', ['count' => $order?->courses_count])
                    : __('booking.courses_count', ['count' => $order?->courses_count]);
            }

            if (\Nwidart\Modules\Facades\Module::has('subscriptions') &&
                \Nwidart\Modules\Facades\Module::isEnabled('subscriptions') &&
                !empty($order?->subscriptions_count)) {
                $itemsCount[] = $order?->subscriptions_count == 1
                    ? __('booking.subscription_count', ['count' => $order?->subscriptions_count])
                    : __('booking.subscriptions_count', ['count' => $order?->subscriptions_count]);
            }

            if (\Nwidart\Modules\Facades\Module::has('coursebundles') &&
                \Nwidart\Modules\Facades\Module::isEnabled('coursebundles') &&
                !empty($order?->coursebundles_count)) {
                $itemsCount[] = $order?->coursebundles_count == 1
                    ? __('booking.coursebundle_count', ['count' => $order?->coursebundles_count])
                    : __('booking.coursebundles_count', ['count' => $order?->coursebundles_count]);
            }

            $paymentMethod = __('settings.' . $order?->payment_method . '_title');
            if (str_contains($paymentMethod, 'settings.')) {
                $paymentMethod = $order?->payment_method;
            }

            return [
                $order?->id,
                $order?->transaction_id ?? '-',
                !empty($order?->payment_file_path) ? url(Storage::url($order?->payment_file_path)) : '',
                implode(' | ', $itemsCount),
                $order?->userProfile?->full_name,
                $paymentMethod,
                formatAmount($order?->amount),
                empty($order?->subscription_id) ? formatAmount($order?->admin_commission) : formatAmount(0),
                $order?->status,
            ];
        });
    }
}
