<?php

namespace App\Livewire\Pages\Admin\EmailTemplates;

use App\Models\EmailTemplate;
use App\Models\Scopes\ActiveScope;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\Exportable;

class EmailTemplatesExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    protected $search;
    protected $sortby;

    public function __construct($search = '', $sortby = 'desc')
    {
        $this->search = $search;
        $this->sortby = $sortby;
    }

    public function query()
    {
        $query = EmailTemplate::select('id', 'title', 'role', 'status')
            ->withoutGlobalScope(ActiveScope::class);

        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('id', $this->sortby);
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('email_template.email_title'),
            __('email_template.role_type'),
            __('general.status'),
        ];
    }

    public function map($template): array
    {
        return [
            $template->id,
            $template->title,
            ucfirst($template->role),
            $template->status == 'active' ? __('general.active') : __('general.deactive'),
        ];
    }
}
