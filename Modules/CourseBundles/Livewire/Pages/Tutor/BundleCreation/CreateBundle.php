<?php

namespace Modules\CourseBundles\Livewire\Pages\Tutor\BundleCreation;

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
    public $courses;
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

    public function mount($id = null)
    {
        if(!empty($id) && !is_numeric($id)){
            return abort(404);
        }
    
        if(!empty($id)) {
            $this->bundleId = $id;

            $this->getBundleDetails($this->bundleId);
        }

        $this->courses = (new CourseService())->getAllCourses(
            instructorId: auth()->user()->id,
            filters: ['status' => 'active'],
            with: ['pricing:id,course_id,price,discount,final_price']
        );  

        $this->courses = $this->courses->map(
            fn($course) => [
                'price' => $course->pricing?->price, 
                'text' => $course->title, 
                'id' => $course->id, 
                'selected' => in_array($course->id, $this->bundle_courses?->pluck('id')->toArray()?? [])
            ]
        );

        $image_file_ext          = setting('_general.allowed_image_extensions') ?? 'jpg,png';
        $image_file_size         = (int) (setting('_general.max_image_size') ?? '5');
        $this->allowImageSize    = !empty( $image_file_size ) ? $image_file_size : '5';
        $this->allowImgFileExt   = !empty( $image_file_ext ) ?  explode(',', $image_file_ext) : [];
        $this->fileExt           = fileValidationText($this->allowImgFileExt);

    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('coursebundles::livewire.tutor.bundle-creation.create-bundle');
    }

    public function toggleIsFree() {
        $this->price        = null;
        $this->discount     = 0;
        $this->final_price   = null;
    }

    public function toggleDiscountAllowed() {
        $this->discount     = 0;
        $this->final_price   = $this->price;
    }

    public function calculatefinal_price() {
        $this->final_price = (float)$this->price * (1 - ((float)$this->discount / 100));
    }

    public function updatedPrice() {
        $this->calculatefinal_price();
    }

    public function updateDiscount($discount) {
        $this->isCustomDiscount = false;
        $this->discount = $discount;
        $this->calculatefinal_price();
    }

    public function updateCustomDiscount() {
        $this->discount = $this->customDiscount;
        $this->isCustomDiscount = true;
        $this->calculatefinal_price();
    }

    public function updatedCustomDiscount() {
        
        if ($this->customDiscount < 0) {
            $this->customDiscount = 0;
        }

        if ($this->customDiscount > 100) {
            $this->customDiscount = 100;
        }

        if (!$this->isCustomDiscount) {
            return;
        }

        $this->discount  = $this->customDiscount;

        $this->calculatefinal_price();
    }

    public function removePhoto()
    {
        $this->image = null;
    }

    public function updatedImage(){
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }

        $extensions = !empty(setting('_general.allowed_image_extensions')) ? explode(',', setting('_general.allowed_image_extensions')) : ['jpg', 'png']; 
        $max_size   = !empty(setting('_general.max_image_size')) ? setting('_general.max_image_size') : 5;

        $file_extension = $this->image->getClientOriginalExtension();
        $file_size = $this->image->getSize() / 1024 / 1024; 

        if (!in_array($file_extension, $extensions)) {
            $this->dispatch('showAlertMessage', type: 'error', message: __(
                'validation.invalid_file_type', 
                ['file_types' => implode(', ', $extensions)]
            ));
            $this->image = null;
            return;
        } elseif($file_size > $max_size) {
            $this->dispatch('showAlertMessage', type: 'error', message: __(
                'validation.max_file_size_err', 
                ['file_size' => $max_size]
            ));
            $this->image = null;
            return;
        }
    }

    public function saveCourseBundle()
    {
        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }

        $this->validate((new BundleRequest())->rules(), (new BundleRequest())->messages(), (new BundleRequest())->attributes());

        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }

        if (!empty($this->image)) {
            $imageName = setMediaPath($this->image);
        }

        try {
            DB::beginTransaction();

            $data = [
                'instructor_id'         => auth()->id(),
                'title'                 => $this->title ?? '',
                'short_description'     => $this->short_description ?? '',
                'description'           => $this->course_description ?? '',
                'price'                 => $this->price ?? 0,
                'discount_percentage'   => $this->discount ?? 0,
            ];

            $bundle = (new BundleService())->createCourseBundle($data);

            if (!empty($bundle) && !empty($this->selected_courses)) {
                (new BundleService())->addBundleCourses($bundle, $this->selected_courses);
            }

            if (!empty($imageName) ) {
                (new BundleService())->addBundleMedia($bundle, [
                    'mediable_id'       => $bundle->id,
                    'mediable_type'     => 'bundle',
                    'type'              => 'thumbnail',
                ], [
                    'path'              => $imageName,
                ]);
            }

            $this->dispatch('showAlertMessage', type: 'success', title:  __('coursebundles::bundles.success_title') , message: __('coursebundles::bundles.create_bundle'));
            
            DB::commit();
        } catch (\Throwable $th) {
            Log::info($th->getMessage());
            DB::rollBack();   
            $this->dispatch('showAlertMessage', type: 'error', title:  __('coursebundles::bundles.error_title') , message: __('coursebundles::bundles.create_bundle_error'));
        }

        
        return redirect()->route('coursebundles.tutor.bundles');
    }

    public function updateCourseBundle($bundleId)
    {    

        $response = isDemoSite();
        if( $response ){
            $this->dispatch('showAlertMessage', type: 'error', title:  __('general.demosite_res_title') , message: __('general.demosite_res_txt'));
            return;
        }

        $this->validate((new BundleRequest())->rules(), (new BundleRequest())->messages(), (new BundleRequest())->attributes());



        $bundle = (new BundleService())->getBundle(
            bundleId: $bundleId,
            instructorId: auth()->user()->id,
            status: 'draft'
        );

        if(empty($bundle)) {
            abort(404);
        }

         $data = [
            'title'                 => $this->title,
            'short_description'     => $this->short_description,
            'description'           => $this->course_description,
            'price'                 => $this->price ?? 0,
            'discount_percentage'   => $this->discount ?? 0,
        ];

        $bundle = (new BundleService())->updateCourseBundle($bundle, $data);

        if (!empty($this->selected_courses)) {
            (new BundleService())->addBundleCourses($bundle, $this->selected_courses);
        }

         if (!empty($this->image)) {
            $imageName = setMediaPath($this->image);
        }

        if (!empty($imageName) ) {
            (new BundleService())->addBundleMedia($bundle, [
                'mediable_id'       => $bundle->id,
                'mediable_type'     => 'bundle',
                'type'              => 'thumbnail',
            ], [
                'path'              => $imageName,
            ]);
        }
        if($this->bundleId){
             $this->dispatch('showAlertMessage', type: 'success', title:  __('coursebundles::bundles.success_title') , message: __('coursebundles::bundles.update_bundle'));
        } else {
            
            $this->dispatch('showAlertMessage', type: 'success', title:  __('coursebundles::bundles.success_title') , message: __('coursebundles::bundles.create_bundle'));
        }
        return redirect()->route('coursebundles.tutor.bundles');
    }

    public function getBundleDetails($id) {

        $this->bundle = (new BundleService())->getBundle(
            bundleId: $id,
            instructorId: auth()->user()->id,
            relations: [
                'thumbnail:id,mediable_id,mediable_type,type,path',
                'courses:id,title'
            ]
        );

        if(empty($this->bundle)) {
            return abort(404);
        }

        $this->title                = $this->bundle->title;
        $this->short_description    = $this->bundle->short_description;
        $this->course_description   = $this->bundle->description;
        $this->price                = $this->bundle->price;
        $this->discount             = $this->bundle->discount_percentage;
        $this->customDiscount       = $this->bundle->discount_percentage > 0 && !in_array($this->discounts, [null, ''])? $this->discount : null;
        $this->discountAllowed      = $this->bundle->discount_percentage > 0 ? true : false;
        $this->final_price          = $this->bundle->final_price;
        $this->selected_courses     = $this->bundle->courses->pluck('id')->toArray();

        $this->bundle_courses       = $this->bundle->courses->map(function($course) {
            return [
                'id'                => $course->id,
                'text'             => $course->title,
            ];
        });

        $this->image = $this->bundle->thumbnail;

    }
}
