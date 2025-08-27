<?php

namespace Modules\CourseBundles\Livewire\Pages\Tutor\BundleCreation;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Modules\Courses\Models\Course;
use Illuminate\Support\Facades\Log;
use Modules\Courses\Services\CourseService;
use Modules\CourseBundles\Services\BundleService;
use Modules\CourseBundles\Http\Requests\BundleRequest;

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
    public $lines = [];
    public $selectAllInstructors = false;
    public $selectAllCourses = [];

    public $isLocked = false;


    public function toggleSelectAllInstructors($value)
    {
        Log::info('toggleSelectAllInstructors called', ['value' => $value]);
        $this->selectAllInstructors = $value;
        $this->isLocked = $value;

        if ($value) {
            $allInstructors = User::whereHas('roles', fn($q) => $q->where('name', 'tutor'))
                ->pluck('id')
                ->toArray();

            $courses = (new CourseService())->getAllCourses(
                instructorId: null,
                filters: ['status' => 'active', 'instructor_ids' => $allInstructors],
                with: ['pricing:id,course_id,price,discount,final_price']
            )->map(fn($course) => [
                'id' => $course->id,
                'title' => $course->title ?? 'Untitled Course #' . $course->id,
            ])->values()->toArray();


            $this->lines = [
                [
                    'instructorId' => $allInstructors,
                    'selectedInstructors' => $allInstructors,
                    'courses' => $courses,
                    'selected_courses' => collect($courses)->pluck('id')->toArray(),
                ]
            ];
            $this->selected_courses = collect($courses)->pluck('id')->toArray();

            Log::info('toggleSelectAllInstructors: All courses fetched', [
                'instructors' => $allInstructors,
                'courses' => $courses,
                'selected_courses' => $this->lines[0]['selected_courses'],
            ]);

            $this->dispatch('initSelect2Line', [
                'index' => 0,
                'data' => $this->lines[0]['courses'],
                'selected' => $this->lines[0]['selected_courses'],
            ]);
        } else {
            $this->lines = [
                [
                    'instructorId' => null,
                    'selectedInstructors' => [],
                    'courses' => [],
                    'selected_courses' => [],
                ]
            ];
            $this->selected_courses = []; 


            Log::info('toggleSelectAllInstructors: Reset lines', ['lines' => $this->lines]);

            $this->dispatch('initSelect2Line', [
                'index' => 0,
                'data' => [],
                'selected' => [],
            ]);
        }
    }
    public function addLine()
    {
        $this->lines[] = [
            'instructorId' => '',
            'courses' => [],
            'selected_courses' => [],
        ];
    }

    public function removeLine($index)
    {
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function updatedSelectAllInstructors($value)
    {
        if ($value) {
            $this->lines = [];

            foreach ($this->instructors as $instructor) {
                $courses = (new CourseService())->getAllCourses(
                    instructorId: $instructor->id,
                    filters: ['status' => 'active'],
                    with: ['pricing:id,course_id,price,discount,final_price']
                )
                    ->map(fn($course) => [
                        'id'   => $course->id,
                        'text' => $course->title ?? 'Untitled Course #' . $course->id,
                    ])
                    ->values();

                $this->lines[] = [
                    'instructorId'      => $instructor->id,
                    'courses'           => $courses,
                    'selected_courses'  => collect($courses)->pluck('id')->toArray(),
                ];
            }
        } else {
            $this->lines = [];
        }
    }

    public function updatedLines($value, $key)
    {
        Log::info('updatedLines triggered', ['key' => $key, 'value' => $value]);

        $parts = explode('.', $key);

        if (count($parts) === 2 && $parts[1] === 'instructorId') {
            $index = (int) $parts[0];
            $instructorId = $value;

            if ($instructorId) {
                $courses = (new CourseService())->getAllCourses(
                    instructorId: $instructorId,
                    filters: ['status' => 'active'],
                    with: ['pricing:id,course_id,price,discount,final_price']
                )
                    ->map(fn($course) => [
                        'id'   => $course->id,
                        'title' => $course->title ?? 'Untitled Course #' . $course->id,
                    ])
                    ->values()
                    ->toArray();

                $this->lines[$index]['courses'] = $courses;

                Log::info('Courses fetched', [
                    'index' => $index,
                    'instructorId' => $instructorId,
                    'courses_count' => count($courses)
                ]);
            } else {
                $this->lines[$index]['courses'] = [];
            }

            $this->lines[$index]['selected_courses'] = [];

            $this->dispatch('initSelect2Line', [
                'index' => $index,
                'data' => $this->lines[$index]['courses'],
                'selected' => $this->lines[$index]['selected_courses'],
            ]);
        } elseif (count($parts) === 2 && $parts[1] === 'selected_courses') {
            $index = (int) $parts[0];
            $selectedCourses = $value;

            if (in_array('__all__', $selectedCourses)) {
                $this->lines[$index]['selected_courses'] = collect($this->lines[$index]['courses'])
                    ->pluck('id')
                    ->toArray();
            }

            Log::info('Selected courses updated', [
                'index' => $index,
                'selected' => $this->lines[$index]['selected_courses']
            ]);

            $this->dispatch('initSelect2Line', [
                'index' => $index,
                'data' => $this->lines[$index]['courses'],
                'selected' => $this->lines[$index]['selected_courses'],
            ]);
        }
    }

    public function getCoursesByInstructor($instructorId, $index)
    {
        return collect($this->lines[$index]['courses'] ?? []);
    }
    public function selectAll($index)
    {
        $allCourseIds = collect($this->lines[$index]['courses'])->pluck('id')->toArray();

        if (count($this->lines[$index]['selected_courses']) === count($allCourseIds)) {
            $this->lines[$index]['selected_courses'] = [];
        } else {
            $this->lines[$index]['selected_courses'] = $allCourseIds;
        }

        $this->dispatch('initSelect2Line', [
            'index' => $index,
            'data' => $this->lines[$index]['courses'],
            'selected' => $this->lines[$index]['selected_courses'],
        ]);
    }

    public function mount($id = null)
    {
        if (empty($this->lines)) {
            $this->lines[] = [
                'instructorId' => '',
                'courses' => [],
                'selected_courses' => [],
            ];
        }

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
        Log::info('saveCourseBundle called', [
            'isAdmin' => $this->isAdmin,
            'selectAllInstructors' => $this->selectAllInstructors,
            'lines' => $this->lines,
            'selected_courses' => $this->selected_courses,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'price' => $this->price,
            'image' => $this->image ? 'File present' : 'No file',
        ]);

        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', [
                'type' => 'error',
                'title' => __('general.demosite_res_title'),
                'message' => __('general.demosite_res_txt')
            ]);
            return;
        }

        $rules = (new BundleRequest())->rules();
        try {
            $this->validate($rules, (new BundleRequest())->messages(), (new BundleRequest())->attributes());
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', ['errors' => $e->errors()]);
            $this->dispatch('showAlertMessage', [
                'type' => 'error',
                'title' => __('coursebundles::bundles.error_title'),
                'message' => implode(' ', array_merge(...array_values($e->errors())))
            ]);
            return;
        }

        try {
            DB::beginTransaction();

            if ($this->isAdmin && $this->selectAllInstructors) {
                $instructorIds = collect($this->lines)->pluck('instructorId')->flatten()->unique()->toArray();
                $courses = $this->selected_courses ?? [];

                if (empty($instructorIds) || empty($courses)) {
                    Log::error('No instructors or courses found (selectAllInstructors)');
                    DB::rollBack();
                    $this->dispatch('showAlertMessage', [
                        'type' => 'error',
                        'title' => __('coursebundles::bundles.error_title'),
                        'message' => __('coursebundles::bundles.courses_required')
                    ]);
                    return;
                }

                foreach ($instructorIds as $instructorId) {
                    $data = [
                        'instructor_id' => $instructorId,
                        'title' => $this->title,
                        'short_description' => $this->short_description,
                        'description' => $this->course_description ?? '',
                        'price' => $this->price ?? 0,
                        'discount_percentage' => $this->discount ?? 0,
                        'created_by' => auth()->id(),
                    ];

                    Log::info("Creating bundle for instructor {$instructorId}", $data);
                    $bundle = (new BundleService())->createCourseBundle($data);
                    (new BundleService())->addBundleCourses($bundle, $courses);

                    if (!empty($this->image)) {
                        $imageName = setMediaPath($this->image);
                        (new BundleService())->addBundleMedia($bundle, [
                            'mediable_id' => $bundle->id,
                            'mediable_type' => 'bundle',
                            'type' => 'thumbnail',
                        ], [
                            'path' => $imageName,
                        ]);
                    }
                }
            } elseif ($this->isAdmin) {
                DB::beginTransaction();

                try {
                    $hasCourses = false;

                    foreach ($this->lines as $line) {
                        $instructorId = $line['instructorId'] ?? null;
                        $courses = $line['selected_courses'] ?? [];

                        if ($instructorId && !empty($courses)) {
                            $hasCourses = true;

                            $data = [
                                'instructor_id'       => $instructorId,
                                'title'               => $this->title,
                                'short_description'   => $this->short_description,
                                'description'         => $this->course_description ?? '',
                                'price'               => $this->price ?? 0,
                                'discount_percentage' => $this->discount ?? 0,
                                'created_by'          => auth()->id(),
                            ];

                            Log::info('Creating bundle (admin manual)', $data);

                            $bundle = (new BundleService())->createCourseBundle($data);

                            (new BundleService())->addBundleCourses($bundle, $courses);

                            if (!empty($this->image)) {
                                $imageName = setMediaPath($this->image);
                                (new BundleService())->addBundleMedia($bundle, [
                                    'mediable_id'   => $bundle->id,
                                    'mediable_type' => 'bundle',
                                    'type'          => 'thumbnail',
                                ], [
                                    'path' => $imageName,
                                ]);
                            }
                        }
                    }

                    if (!$hasCourses) {
                        Log::error('No courses selected (admin manual)');
                        DB::rollBack();

                        $this->dispatch('showAlertMessage', [
                            'type'    => 'error',
                            'title'   => __('coursebundles::bundles.error_title'),
                            'message' => __('coursebundles::bundles.courses_required')
                        ]);

                        return;
                    }

                    DB::commit();
                } catch (\Throwable $e) {
                    DB::rollBack();
                    Log::error('Failed to create bundles (admin manual)', ['error' => $e->getMessage()]);
                    throw $e;
                }
            }

            // 🔹 Tuto  
            else {
                if (empty($this->selected_courses)) {
                    Log::error('No courses selected (tutor)');
                    DB::rollBack();
                    $this->dispatch('showAlertMessage', [
                        'type' => 'error',
                        'title' => __('coursebundles::bundles.error_title'),
                        'message' => __('coursebundles::bundles.courses_required')
                    ]);
                    return;
                }

                $data = [
                    'instructor_id' => auth()->id(),
                    'title' => $this->title,
                    'short_description' => $this->short_description,
                    'description' => $this->course_description ?? '',
                    'price' => $this->price ?? 0,
                    'discount_percentage' => $this->discount ?? 0,
                    'created_by' => auth()->id(),
                ];

                Log::info('Creating bundle (tutor)', $data);
                $bundle = (new BundleService())->createCourseBundle($data);
                (new BundleService())->addBundleCourses($bundle, $this->selected_courses);

                if (!empty($this->image)) {
                    $imageName = setMediaPath($this->image);
                    (new BundleService())->addBundleMedia($bundle, [
                        'mediable_id' => $bundle->id,
                        'mediable_type' => 'bundle',
                        'type' => 'thumbnail',
                    ], [
                        'path' => $imageName,
                    ]);
                }
            }

            DB::commit();
            Log::info('Bundle(s) created successfully');

            $this->dispatch('showAlertMessage', [
                'type' => 'success',
                'title' => __('coursebundles::bundles.success_title'),
                'message' => __('coursebundles::bundles.create_bundle')
            ]);

            return redirect()->route('coursebundles.tutor.bundles');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to create bundle', [
                'error' => $th->getMessage(),
                'stack' => $th->getTraceAsString(),
            ]);
            $this->dispatch('showAlertMessage', [
                'type' => 'error',
                'title' => __('coursebundles::bundles.error_title'),
                'message' => __('coursebundles::bundles.create_bundle_error')
            ]);
        }
    }



    public function updateCourseBundle($bundleId)
    {
        if (isDemoSite()) {
            $this->dispatch('showAlertMessage', [
                'type' => 'error',
                'title' => __('general.demosite_res_title'),
                'message' => __('general.demosite_res_txt')
            ]);
            return;
        }

        // Validate inputs
        $rules = (new BundleRequest())->rules();
        try {
            $this->validate($rules, (new BundleRequest())->messages(), (new BundleRequest())->attributes());
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('showAlertMessage', [
                'type' => 'error',
                'title' => __('coursebundles::bundles.error_title'),
                'message' => implode(' ', array_merge(...array_values($e->errors())))
            ]);
            return;
        }

        $bundleService = new BundleService();

        // Fetch the bundle
        $bundle = $bundleService->getBundle(
            bundleId: $bundleId,
            instructorId: $this->isAdmin ? null : auth()->id(),
            status: 'draft'
        );

        if (!$bundle) {
            abort(404);
        }

        DB::beginTransaction();

        try {
            // Update bundle details
            $data = [
                'title' => $this->title,
                'short_description' => $this->short_description,
                'description' => $this->course_description ?? '',
                'price' => $this->price ?? 0,
                'discount_percentage' => $this->discount ?? 0,
            ];

            $bundle = $bundleService->updateCourseBundle($bundle, $data);

            // Handle courses and instructors
            if ($this->isAdmin) {
                $instructorLines = $this->selectAllInstructors
                    ? collect($this->lines)->pluck('instructorId')->flatten()->unique()->toArray()
                    : collect($this->lines)->pluck('instructorId')->toArray();

                foreach ($instructorLines as $index => $instructorId) {
                    $courses = $this->selectAllInstructors
                        ? ($this->selected_courses ?? [])
                        : ($this->lines[$index]['selected_courses'] ?? []);

                    if ($instructorId && !empty($courses)) {
                        $bundleData = array_merge($data, ['instructor_id' => $instructorId]);
                        $newBundle = $bundleService->createCourseBundle($bundleData);
                        $bundleService->addBundleCourses($newBundle, $courses);

                        // Handle image safely
                        if (!empty($this->image)) {
                            $imageName = setMediaPath($this->image);
                            if ($imageName) {
                                $bundleService->addBundleMedia($newBundle, [
                                    'mediable_id' => $newBundle->id,
                                    'mediable_type' => 'bundle',
                                    'type' => 'thumbnail',
                                ], ['path' => $imageName]);
                            }
                        }
                    }
                }
            } else {
                // Tutor scenario
                if (empty($this->selected_courses)) {
                    DB::rollBack();
                    $this->dispatch('showAlertMessage', [
                        'type' => 'error',
                        'title' => __('coursebundles::bundles.error_title'),
                        'message' => __('coursebundles::bundles.courses_required')
                    ]);
                    return;
                }

                $bundleService->addBundleCourses($bundle, $this->selected_courses);

                // Handle image
                if (!empty($this->image)) {
                    $imageName = setMediaPath($this->image);
                    if ($imageName) {
                        $bundleService->addBundleMedia($bundle, [
                            'mediable_id' => $bundle->id,
                            'mediable_type' => 'bundle',
                            'type' => 'thumbnail',
                        ], ['path' => $imageName]);
                    }
                } elseif ($this->image === null && $bundle->thumbnail) {
                    $bundle->media()->where('type', 'thumbnail')->delete();
                }
            }

            DB::commit();

            $this->dispatch('showAlertMessage', [
                'type' => 'success',
                'title' => __('coursebundles::bundles.success_title'),
                'message' => __('coursebundles::bundles.update_bundle')
            ]);

            return redirect()->route('coursebundles.tutor.bundles');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Failed to update bundle', [
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);

            $this->dispatch('showAlertMessage', [
                'type' => 'error',
                'title' => __('coursebundles::bundles.error_title'),
                'message' => __('coursebundles::bundles.create_bundle_error')
            ]);
        }
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
