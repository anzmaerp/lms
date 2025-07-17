<?php

namespace Modules\CourseBundles\Livewire\Pages\Tutor\BundleCreation;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Modules\Courses\Services\CourseService;
use Modules\CourseBundles\Http\Requests\BundleRequest;
use Modules\CourseBundles\Services\BundleService;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class CreateBundle extends Component
{
    use WithFileUploads;

    public $bundleId;
    public $bundle;
    public $courses = [];
    public $selectedCourses = [];
    public $course_description;
    public $short_description;
    public $title;
    public $description;
    public $selected_courses;
    public $bundle_courses;
    public $price;
    public $discount;
    public $final_price;
    public $isUploading = false;
    public $image;

    public $allowImageSize;
    public $allowImgFileExt;
    public $fileExt;

    public $isCustomDiscount = false;
    public $customDiscount;
    public $discountAllowed = false;
    public $discounts = [10, 20, 30, 40, 50];

    public $instructorId;
    public $instructors = [];
    public $isAdmin = false;

    public function mount($id = null)
    {
        $this->isAdmin = auth()->user()->hasRole('admin');

        if (!empty($id) && !is_numeric($id)) {
            abort(404);
        }
        if ($this->isAdmin) {
            $this->instructors = User::whereHas('roles', fn($q) => $q->where('name', 'tutor'))
                ->with('profile:id,user_id,first_name,last_name')
                ->get();
        } else {
            $this->instructorId = auth()->id();
        }
        if (!empty($id)) {
            $this->bundleId = $id;
            $this->getBundleDetails($this->bundleId);

            if ($this->isAdmin && $this->bundle) {
                $this->instructorId = $this->bundle->instructor_id;
            }

            if ($this->bundle && $this->bundle->courses) {
                $this->selectedCourses = $this->bundle->courses->pluck('id')->toArray();
                $this->selected_courses = $this->selectedCourses;

                $this->bundle_courses = $this->bundle->courses->map(fn($c) => ['id' => $c->id, 'text' => $c->title]);
            }
        }

        if ($this->instructorId) {
            $activeCourses = (new CourseService())->getAllCourses(
                instructorId: $this->instructorId,
                filters: ['status' => 'active'],
                with: ['pricing:id,course_id,price,discount,final_price']
            )->map(fn($course) => [
                'id' => $course->id,
                'text' => $course->title ?? 'Untitled Course #' . $course->id,
            ]);

            $this->courses = collect($this->bundle_courses ?? [])
                ->merge($activeCourses)
                ->unique('id')
                ->values();
        } else {
            $this->courses = [];
        }

        $image_file_ext = setting('_general.allowed_image_extensions') ?? 'jpg,png';
        $image_file_size = (int)(setting('_general.max_image_size') ?? '5');
        $this->allowImageSize = $image_file_size ?: 5;
        $this->allowImgFileExt = explode(',', $image_file_ext);
        $this->fileExt = fileValidationText($this->allowImgFileExt);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        return view('coursebundles::livewire.tutor.bundle-creation.create-bundle');
    }

    public function toggleIsFree()
    {
        $this->price = null;
        $this->discount = 0;
        $this->final_price = null;
    }

    public function toggleDiscountAllowed()
    {
        $this->discount = 0;
        $this->final_price = $this->price;
    }

    public function calculatefinal_price()
    {
        $this->final_price = (float)$this->price * (1 - ((float)$this->discount / 100));
    }

    public function updatedPrice()
    {
        $this->calculatefinal_price();
    }

    public function updateDiscount($discount)
    {
        $this->isCustomDiscount = false;
        $this->discount = $discount;
        $this->calculatefinal_price();
    }

    public function updateCustomDiscount()
    {
        $this->discount = $this->customDiscount;
        $this->isCustomDiscount = true;
        $this->calculatefinal_price();
    }

    public function updatedCustomDiscount()
    {
        $this->customDiscount = max(0, min(100, $this->customDiscount));

        if (!$this->isCustomDiscount) {
            return;
        }

        $this->discount = $this->customDiscount;
        $this->calculatefinal_price();
    }

    public function removePhoto()
    {
        $this->image = null;
    }

    public function updatedImage()
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        $extensions = $this->allowImgFileExt ?: ['jpg', 'png'];
        $max_size = $this->allowImageSize;
        $file_extension = $this->image->getClientOriginalExtension();
        $file_size = $this->image->getSize() / 1024 / 1024;

        if (!in_array($file_extension, $extensions)) {
            $this->dispatch('showAlertMessage', type: 'error', message: __('validation.invalid_file_type', ['file_types' => implode(', ', $extensions)]));
            $this->image = null;
        } elseif ($file_size > $max_size) {
            $this->dispatch('showAlertMessage', type: 'error', message: __('validation.max_file_size_err', ['file_size' => $max_size]));
            $this->image = null;
        }
    }

    public function saveCourseBundle()
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }

        $this->validate((new BundleRequest())->rules(), (new BundleRequest())->messages(), (new BundleRequest())->attributes());

        if (!empty($this->image)) {
            $imageName = setMediaPath($this->image);
        }

        try {
            DB::beginTransaction();

            $data = [
                'instructor_id' => $this->instructorId ?? auth()->id(),
                'title' => $this->title ?? '',
                'short_description' => $this->short_description ?? '',
                'description' => $this->course_description ?? '',
                'price' => $this->price ?? 0,
                'discount_percentage' => $this->discount ?? 0,
                'created_by' => auth()->id(),
            ];

            $bundle = (new BundleService())->createCourseBundle($data);

            if (!empty($bundle) && !empty($this->selected_courses)) {
                (new BundleService())->addBundleCourses($bundle, $this->selected_courses);
            }

            if (!empty($imageName)) {
                (new BundleService())->addBundleMedia($bundle, [
                    'mediable_id' => $bundle->id,
                    'mediable_type' => 'bundle',
                    'type' => 'thumbnail',
                ], [
                    'path' => $imageName,
                ]);
            }

            DB::commit();

            $this->dispatch('showAlertMessage', type: 'success', title: __('coursebundles::bundles.success_title'), message: __('coursebundles::bundles.create_bundle'));
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            DB::rollBack();
            $this->dispatch('showAlertMessage', type: 'error', title: __('coursebundles::bundles.error_title'), message: __('coursebundles::bundles.create_bundle_error'));
        }

        return redirect()->route('coursebundles.tutor.bundles');
    }

    public function updateCourseBundle($bundleId)
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', type: 'error', title: __('general.demosite_res_title'), message: __('general.demosite_res_txt'));
            return;
        }
        $this->validate((new BundleRequest())->rules(), (new BundleRequest())->messages(), (new BundleRequest())->attributes());

        $bundle = (new BundleService())->getBundle(
            bundleId: $bundleId,
            instructorId: $this->isAdmin ? null : auth()->id(),
            status: 'draft'
        );

        if (empty($bundle)) {
            abort(404);
        }

        $data = [
            'title' => $this->title,
            'short_description' => $this->short_description,
            'description' => $this->course_description,
            'price' => $this->price ?? 0,
            'discount_percentage' => $this->discount ?? 0,
        ];

        $bundle = (new BundleService())->updateCourseBundle($bundle, $data);

        if (!empty($this->selected_courses)) {
            (new BundleService())->addBundleCourses($bundle, $this->selected_courses);
        }

        if (!empty($this->image)) {
            $imageName = setMediaPath($this->image);
        }

        if (!empty($imageName)) {
            (new BundleService())->addBundleMedia($bundle, [
                'mediable_id' => $bundle->id,
                'mediable_type' => 'bundle',
                'type' => 'thumbnail',
            ], [
                'path' => $imageName,
            ]);
        }

        $this->dispatch('showAlertMessage', type: 'success', title: __('coursebundles::bundles.success_title'), message: __('coursebundles::bundles.update_bundle'));

        return redirect()->route('coursebundles.tutor.bundles');
    }

    public function updatedInstructorId()
    {
        $courses = (new CourseService())->getAllCourses(
            instructorId: $this->instructorId,
            filters: ['status' => 'active'],
            with: ['pricing:id,course_id,price,discount,final_price']
        );

        $mappedCourses = $courses->map(fn($course) => [
            'id' => $course->id,
            'text' => $course->title,
        ]);
        $allCourses = collect($this->bundle_courses ?? [])
            ->merge($mappedCourses)
            ->unique('id')
            ->values();

        $this->courses = $allCourses;

        $this->dispatch('initSelect2', [
            'target' => '.am-select2',
            'data' => $allCourses,
            'selected' => $this->selected_courses
        ]);
    }

    public function getBundleDetails($id)
    {
        $this->bundle = (new BundleService())->getBundle(
            bundleId: $id,
            instructorId: $this->isAdmin ? null : auth()->id(),
            relations: [
                'thumbnail:id,mediable_id,mediable_type,type,path',
                'courses:id,title'
            ]
        );
        if (empty($this->bundle)) {
            abort(404);
        }

        $this->title = $this->bundle->title;
        $this->short_description = $this->bundle->short_description;
        $this->course_description = $this->bundle->description;
        $this->price = $this->bundle->price;
        $this->discount = $this->bundle->discount_percentage;
        $this->customDiscount = $this->discount > 0 ? $this->discount : null;
        $this->discountAllowed = $this->discount > 0;
        $this->final_price = $this->bundle->final_price;
        $this->selected_courses = $this->bundle->courses->pluck('id')->toArray();
        $this->bundle_courses = $this->bundle->courses->map(fn($c) => ['id' => $c->id, 'text' => $c->title]);
        $this->image = $this->bundle->thumbnail;
        $this->dispatch('initSelect2', [
            'target' => '.am-select2',
            'data' => $this->bundle_courses,
            'selected' => $this->selected_courses
        ]);
    }
}
