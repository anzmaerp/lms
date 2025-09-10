<?php

namespace Modules\Courses\Livewire\Pages\Admin;

use Modules\Courses\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;
    protected $sortby;

    public function __construct($search = null, $sortby = 'desc')
    {
        $this->search = $search;
        $this->sortby = $sortby;
    }

    public function collection()
    {
        $query = Category::query()->whereNull('deleted_at');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('id', $this->sortby)->get(['id', 'name', 'description', 'status']);
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->name,
            strip_tags($row->description), // remove HTML from description
            ucfirst($row->status),
        ];
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('category.title'),
            __('category.description'),
            __('general.status'),
        ];
    }
}
