<?php

namespace Modules\Courses\Livewire\Pages\Admin;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Courses\Services\CourseService;

class CourseEnrollmentsExport implements FromCollection, WithHeadings
{
    protected $search;
    protected $status;
    protected $sortby;

    public function __construct($search = '', $status = '', $sortby = 'desc')
    {
        $this->search = $search;
        $this->status = $status;
        $this->sortby = $sortby;
    }

    public function collection(): Collection
    {
        $service = new CourseService();
        $orders  = $service->getCourseEnrollments($this->search, $this->status, $this->sortby);

        return $orders->getCollection()->map(function ($order) {
            $options     = $order?->options ?? [];
            $courseTitle = $options['title'] ?? $order?->orderable?->title ?? '';
            $studentName = $order?->orders?->userProfile?->full_name ?? '';
            $tutorName   = $order?->orderable?->instructor?->profile?->full_name ?? '';
            $tutorPayout = $order?->price - getCommission($order?->price);

            return [
                'ID'             => $order?->order_id,
                'Transaction ID' => $order?->orders?->transaction_id ?? '',
                'Course Title'   => $courseTitle,
                'Student Name'   => $studentName,
                'Tutor Name'     => $tutorName,
                'Amount'         => formatAmount($order?->price),
                'Tutor Payout'   => formatAmount($tutorPayout),
                'Status'         => $order?->orders?->status ?? '',
                'Created At'     => $order?->created_at?->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            __('courses::courses.id'),
            __('courses::courses.transaction_id'),
            __('courses::courses.course_title'),
            __('courses::courses.student_name'),
            __('courses::courses.tutor_name'),
            __('courses::courses.amount'),
            __('courses::courses.tutor_payout'),
            __('courses::courses.status'),
            __('general.created_at'),
        ];
    }
}
