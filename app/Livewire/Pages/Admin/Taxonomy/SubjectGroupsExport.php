<?php

namespace App\Livewire\Pages\Admin\Taxonomy;

use App\Models\SubjectGroup;
use App\Models\Scopes\ActiveScope;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubjectGroupsExport implements FromCollection, WithHeadings
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = SubjectGroup::select('id','name','description','status')
            ->withoutGlobalScope(ActiveScope::class);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->whereFullText('name', $this->search)
                  ->orWhereFullText('description', $this->search);
            });
        }

        return $query->orderBy('id','desc')->get();
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('general.name'),
            __('general.description'),
            __('general.status'),
        ];
    }
}
