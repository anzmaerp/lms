<?php

namespace App\Livewire\Pages\Admin\Dispute;

use App\Models\Dispute;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DisputesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $keyword;
    protected $status;
    protected $sortby;

    public function __construct($keyword = null, $status = null, $sortby = 'desc')
    {
        $this->keyword = $keyword;
        $this->status  = $status;
        $this->sortby  = $sortby;
    }

    public function collection()
    {
        $query = Dispute::with([
            'booking.student.profile',
            'booking.tutor.profile',
            'creatorBy.profile',
            'responsibleBy.profile',
        ]);

        if (!empty($this->keyword)) {
            $query->where('dispute_reason', 'like', '%' . $this->keyword . '%')
                  ->orWhere('uuid', 'like', '%' . $this->keyword . '%');
        }

        if (!empty($this->status)) {
            $query->where('status', $this->status);
        }

        return $query->orderBy('id', $this->sortby)->get();
    }

    public function map($dispute): array
    {
        return [
            $dispute->id,
            $dispute->uuid,
            $dispute->dispute_reason,
            $dispute->booking?->slot?->subjectGroupSubjects?->subject?->name,
            $dispute->creatorBy?->profile?->full_name,
            $dispute->responsibleBy?->profile?->full_name,
            ucfirst(str_replace('_', ' ', $dispute->status)),
            $dispute->created_at?->format('d M Y'),
        ];
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Reason',
            'Session Subject',
            'Student',
            'Tutor',
            'Status',
            'Created At',
        ];
    }
}
