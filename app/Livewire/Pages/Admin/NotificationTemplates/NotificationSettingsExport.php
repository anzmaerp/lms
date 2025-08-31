<?php

namespace App\Livewire\Pages\Admin\NotificationTemplates;

use App\Models\NotificationTemplate;
use App\Models\Scopes\ActiveScope;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NotificationSettingsExport implements FromCollection, WithHeadings
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = NotificationTemplate::select('id','title','role','status')
            ->withoutGlobalScope(ActiveScope::class);

        if (!empty($this->search)) {
            $query->whereFullText('title', $this->search);
        }

        return $query->orderBy('id','desc')->get();
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('notification_template.notification_title'),
            __('notification_template.role_type'),
            __('general.status'),
        ];
    }
}
