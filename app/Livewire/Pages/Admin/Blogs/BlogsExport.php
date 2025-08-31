<?php

namespace App\Livewire\Pages\Admin\Blogs;

use App\Models\Blog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BlogsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        $query = Blog::with('categories:id,name')->select('id', 'title', 'description', 'status', 'created_at');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            __('ID'),
            __('general.title'),
            __('general.description'),
            __('blogs.category'),
            __('general.status'),
            __('general.date'),
        ];
    }

    public function map($blog): array
    {
        return [
            $blog->id,
            $blog->title,
            strip_tags($blog->description),
            $blog->categories->pluck('name')->implode(', '),
            $blog->status,
            $blog->created_at->format('Y-m-d H:i'),
        ];
    }
}
