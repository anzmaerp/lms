<?php

namespace Modules\Courses\Livewire\Pages\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Modules\Courses\Models\Course;

class CoursesExport implements FromCollection, WithHeadings
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Course::with(['category:id,name', 'subCategory:id,name', 'instructor.profile:id,user_id,first_name,last_name'])
            ->select('id','title','status','category_id','sub_category_id','instructor_id');

        // Apply keyword filter
        if (!empty($this->filters['keyword'])) {
            $query->where('title', 'like', "%{$this->filters['keyword']}%");
        }

        // Apply status filter
        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('id', $this->filters['sort'] ?? 'desc')
            ->get()
            ->map(function ($course) {
                return [
                    'id'            => $course->id,
                    'title'         => $course->title,
                    'instructor'    => $course->instructor?->profile?->full_name,
                    'category'      => $course->category?->name,
                    'subcategory'   => $course->subCategory?->name,
                    'status'        => $course->status,
                ];
            });
    }

    public function headings(): array
    {
        return [
            __('courses::courses.id'),
            __('courses::courses.title'),
            __('courses::courses.instructor'),
            __('courses::courses.category'),
            __('courses::courses.subcategory'),
            __('courses::courses.status'),
        ];
    }
}
