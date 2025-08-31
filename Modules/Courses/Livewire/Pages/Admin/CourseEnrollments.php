<?php

namespace Modules\Courses\Livewire\Pages\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Courses\Services\CourseService;
use Modules\Courses\Livewire\Pages\Admin\CourseEnrollmentsExport;

class CourseEnrollments extends Component
{
    use WithPagination;

   
    public $search = '';
    public $sortby = 'desc';
    public $status = '';
    private ?CourseService $courseService = null;


    public function boot()
    {
        $this->courseService = new CourseService();
    }

    public function mount()
    {
        $this->dispatch('initSelect2', target: '.am-select2');
    }

    #[Layout('layouts.admin-app')]
    public function render()
    {
        $courses = $this->courseService->getCourseEnrollments($this->search, $this->status, $this->sortby);

        return view('courses::livewire.admin.course-enrollments', [
            'orders' => $courses
        ]);
    }
    public function printUsersExcel()
{
    return Excel::download(
        new CourseEnrollmentsExport($this->search, $this->status, $this->sortby),
        'course_enrollments.xlsx'
    );
}

    public function updated($propertyName)
    {
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }
        if (in_array($propertyName, ['status', 'search', 'sortby'])) {
            $this->resetPage();
        }
    }
}
