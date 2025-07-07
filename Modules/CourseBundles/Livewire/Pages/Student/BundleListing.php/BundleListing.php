<?php

namespace Modules\CourseBundles\Livewire\Pages\Student\BundleListing;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\CourseBundles\Services\BundleService;

class BundleListng extends Component
{
    use WithPagination;
    
    public $filters     = [];
    public $perPage     = 10;

    public function mount() {
        $this->dispatch('initSelect2', target: '.am-select2');
       
    }

    #[Computed]
    public function courses(){
        $bundles = (new BundleService())->getBundles(
            studentId:  auth()->user()->id,
            withSum: [
                'courseProgress' => 'duration'
            ],
        );

        return $bundles;
    }

    public function loadCourseData() {}

    #[Layout('layouts.app')]
    public function render()
    {
        $courses = $this->courses;
        $favCourseIds = Like::where('likeable_type', 'course')->where('user_id', Auth::id())?->pluck('likeable_id')?->toArray() ?? [];
        return view('courses::livewire.student.course-list.courselist', compact('courses', 'favCourseIds'));
    }

    public function loadCoursesData() {
        $this->isLoading = false;
    }

    public function likeCourse($courseId)
    {
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }
        $course = (new CourseService())->getCourse($courseId);

        if ($this->isLiked($course)) {
            $course->likes()->where('user_id', Auth::id())->delete();
        } else {
            $course->likes()->create([
                'user_id' => Auth::id(),
            ]);
        }
    }

    public function isLiked($course)
    {
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }
        return $course->likes()->where('user_id', Auth::id())->exists();
    }
}
