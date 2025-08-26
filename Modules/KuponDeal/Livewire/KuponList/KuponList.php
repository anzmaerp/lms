<?php

namespace Modules\KuponDeal\Livewire\KuponList;

use Carbon\Carbon;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use App\Services\SubjectService;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use Modules\KuponDeal\Models\Coupon;
use App\Models\UserSubjectGroupSubject;
use Modules\KuponDeal\Requests\CouponRequest;
use Modules\KuponDeal\Services\CouponService;

class KuponList extends Component
{
    use WithPagination;
    public $form = [
        'id' => null,
        'user_id' => '',
        'code' => '',
        'discount_type' => '',
        'discount_value' => '',
        'expiry_date' => '',
        'couponable_type' => '',
        'couponable_id' => [],
        'auto_apply' => 0,
        'color' => '',
        'conditions' => [],
        'status' => 1,
        'description' => '',
        'instructor_id' => '',
    ];
    public $conditions;
    public $lines = [];
    public $use_conditions = false;
    public $keyword = '';
    public $isLoading = true;
    public $couponable_types = [];
    public $isAdmin = false;
    public $couponable_ids = [];
    public $instructors = [];
    public $active_tab = 'active';
    public $isEdit = false;
    public $instructorId = null;
    protected $couponService, $subjectService;
    public $selectAllInstructors = false;

    public $isLocked = false;



    public function toggleSelectAllInstructors($value)
    {
        $this->selectAllInstructors = $value;

        if ($value) {
            $allInstructors = User::whereHas('roles', fn($q) => $q->where('name', 'tutor'))
                ->pluck('id')->toArray();

            $this->lines = [
                [
                    'selectedInstructors' => $allInstructors,
                    'instructorId' => $allInstructors,
                    'couponable_type' => '', // سيتم تحديده بعد اختيار النوع
                    'couponable_ids' => [],
                    'couponable_id' => [],
                ]
            ];

            $this->isLocked = true;
        } else {
            // استرجاع الوضع الطبيعي للسطر الواحد الفارغ
            $this->lines = [
                [
                    'instructorId' => null,
                    'couponable_type' => '',
                    'couponable_ids' => [],
                    'couponable_id' => [],
                ]
            ];
            $this->isLocked = false;
        }
    }

    public function initOptionsForAll($type)
    {
        if ($type == \Modules\Courses\Models\Course::class) {
            return \Modules\Courses\Models\Course::select('id', 'title')->get()
                ->map(fn($c) => ['id' => $c->id, 'title' => $c->title])
                ->toArray();
        }

        if ($type == UserSubjectGroupSubject::class) {
            $subjectGroups = \App\Models\UserSubjectGroup::with('subjects')->get();

            $formattedData = [];
            foreach ($subjectGroups as $sbjGroup) {
                foreach ($sbjGroup->subjects as $sbj) {
                    $formattedData[] = [
                        'id' => $sbj->pivot->id,
                        'title' => $sbj->name
                    ];
                }
            }
            return $formattedData;
        }

        return [];
    }

    public function boot()
    {
        $this->couponService = new CouponService();
        $this->subjectService  = new SubjectService(Auth::user());
    }

    public function changeTab($tab)
    {
        $this->reset();
        $this->active_tab = $tab;
        $this->isLoading = false;
    }
    public function applySelectAllInstructors()
    {
        if (auth()->user()->hasRole('admin')) {
            $allInstructors = User::role('tutor')->pluck('id')->toArray();

            foreach ($this->lines as $index => $line) {
                $this->lines[$index]['instructorId']   = $allInstructors;
                $this->lines[$index]['couponable_id']  = [];
            }
        }
    }
    public function addLine()
    {
        $this->lines[] = [
            'instructorId' => null,
            'couponable_type' => '',
            'couponable_ids' => [],
            'couponable_id' => [],
        ];
    }
    public function selectAll($index)
    {
        $ids = collect($this->lines[$index]['couponable_ids'])->pluck('id')->toArray();
        $this->lines[$index]['couponable_id'] = $ids;
    }
    public function mount()
    {
        $this->isAdmin = auth()->user()->hasRole('admin');

        if ($this->isAdmin) {
            $this->instructors = User::whereHas('roles', fn($q) => $q->where('name', 'tutor'))->get();
            $this->lines[] = [
                'instructorId' => null,
                'couponable_type' => '',
                'couponable_ids' => [],
                'couponable_id' => [],
            ];
        } else {
            $this->form['instructor_id'] = Auth::id();
            $this->couponable_ids = $this->initOptions($this->form['couponable_type'], Auth::id());
        }

        $this->conditions = [
            Coupon::CONDITION_FIRST_ORDER => [
                'text' => __('kupondeal::kupondeal.condition_one'),
                'desc' => __('kupondeal::kupondeal.condition_one_desc'),
                'required_input' => false,
            ],
            Coupon::CONDITION_MINIMUM_ORDER => [
                'text' => __('kupondeal::kupondeal.condition_two'),
                'desc' => __('kupondeal::kupondeal.condition_two_desc'),
                'required_input' => true,
            ],
        ];

        $this->couponable_types = [];

        if (\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses')) {
            $this->couponable_types[] = [
                'label' => 'Course',
                'value' => \Modules\Courses\Models\Course::class,
            ];
        }

        $this->couponable_types[] = [
            'label' => 'Subject',
            'value' => UserSubjectGroupSubject::class,
        ];

        if (!$this->isAdmin) {
            $this->form['instructor_id'] = Auth::id();
        }
    }
    #[Computed]
    public function coupons()
    {
        $where = [];

        if (!(\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))) {
            $where['couponable_type'] = UserSubjectGroupSubject::class;

            if (!empty($this->form['couponable_id']) && is_array($this->form['couponable_id'])) {
                $where['couponable_ids'] = $this->form['couponable_id'];
            }
        }

        if ($this->isAdmin && $this->instructorId) {
            $where['instructor_id'] = $this->instructorId;
        }
        $instructorId = $this->isAdmin
            ? ($this->instructorId ?? Auth::id())
            : Auth::id();

        return $this->couponService->getCoupons($instructorId, $this->active_tab, $this->keyword, $where);
    }


    #[Layout('layouts.app')]
    public function render()
    {
        $coupons = $this->coupons();
        return view('kupondeal::livewire.kupon-list.kupon-list', compact('coupons'));
    }

    public function initData()
    {
        $this->isLoading = false;
    }

    public function addCondition($key)
    {
        $this->form['conditions'][$key] = '';
    }

    public function removeCondition($key)
    {
        if (isset($this->form['conditions'][$key])) {
            unset($this->form['conditions'][$key]);
        }
    }

    public function updatedUseConditions($value)
    {
        if (!$value) {
            $this->form['conditions'] = [];
        }
    }

    public function updatedFormCouponableType($value)
    {
        $instructorId = $this->isAdmin
            ? ($this->instructorId ?? Auth::id())
            : Auth::id();
        $user = User::find($instructorId);
        $this->subjectService = new SubjectService($user);
        $this->couponable_ids = $this->initOptions($value);
        $this->form['couponable_id'] = [];
        $this->dispatch('couponableValuesUpdated', options: array_values($this->couponable_ids), reset: true);
    }
    public function initOptions($type, $userId = null)
    {
        $userId = $userId ?: ($this->isAdmin ? $this->instructorId : Auth::id());

        if (!$userId) return [];

        if ($type == \Modules\Courses\Models\Course::class) {
            $courses = (new \Modules\Courses\Services\CourseService())->getInstructorCourses($userId, [], ['title', 'id']);
            return $courses->map(fn($course) => [
                'title' => $course->title,
                'id' => $course->id
            ])->toArray();
        }

        if ($type == UserSubjectGroupSubject::class) {
            $this->subjectService = new SubjectService(User::find($userId));
            $subjectGroups = $this->subjectService->getUserSubjectGroups(['subjects:,name']);

            $formattedData = [];
            foreach ($subjectGroups as $sbjGroup) {
                if ($sbjGroup->subjects->isEmpty()) continue;

                foreach ($sbjGroup->subjects as $sbj) {
                    $formattedData[] = [
                        'id' => $sbj->pivot->id,
                        'title' => $sbj->name
                    ];
                }
            }
            return $formattedData;
        }

        return [];
    }

    public function editCoupon($id)
    {
        $this->isEdit = true;
        $coupon = $this->couponService->getCoupon($id);
        $this->form = $coupon->toArray();
        $this->form['conditions'] = is_array($coupon->conditions)
            ? $coupon->conditions
            : json_decode($coupon->conditions, true) ?? [];
        if (!empty($this->form['conditions'])) {
            $this->use_conditions = true;
        }
        $this->form['expiry_date'] = !empty($this->form['expiry_date']) ? Carbon::parse($this->form['expiry_date'])->format('Y-m-d') : null;

        $this->dispatch(
            'onEditCoupon',
            discount_type: $coupon->discount_type,
            discount_value: $coupon->discount_value,
            expiry_date: $coupon->expiry_date,
            couponable_id: $coupon->couponable_id,
            couponable_type: $coupon->couponable_type,
            conditions: $coupon->conditions,
            color: $coupon->color,
            optionList: $this->initOptions($coupon->couponable_type)
        );
    }

    #[On('delete-coupon')]
    public function deleteCoupon($params = [])
    {
        $isDeleted = $this->couponService->deleteCoupon($params['id']);
        if ($isDeleted) {
            $this->dispatch('showAlertMessage', type: 'success', title: _('kupondeal::kupondeal.coupon_deleted'), message: _('kupondeal::kupondeal.coupon_deleted_success'));
        } else {
            $this->dispatch('showAlertMessage', type: 'error', title: _('kupondeal::kupondeal.coupon_delete_failed'), message: _('kupondeal::kupondeal.coupon_delete_failed_desc'));
        }
    }

    private function generateUniqueCode($baseCode)
    {
        $newCode = $baseCode;
        $counter = 1;

        while (\DB::table('coupons')->where('code', $newCode)->exists()) {
            $newCode = $baseCode . '-' . $counter;
            $counter++;
        }

        return $newCode;
    }

    public function addCoupon()
    {
        $request = new CouponRequest();
        $this->form['expiry_date'] = !empty($this->form['expiry_date'])
            ? Carbon::parse($this->form['expiry_date'])->format('Y-m-d')
            : null;

        // Validation rules
        $rules = $request->rules();
        $rules['form.description'] = ['nullable', 'string', 'max:500'];
        $rules['form.code'] = [
            'required',
            'string',
            'regex:/^\S*$/',
            'max:50',
            'min:3',
            Rule::unique('coupons', 'code')->ignore($this->form['id'] ?? null)
        ];
        $rules['form.discount_value'] = [
            'required',
            'numeric',
            'min:0',
            function ($attribute, $value, $fail) {
                if ($this->form['discount_type'] === 'percentage' && $value > 100) {
                    $fail(__('kupondeal::kupondeal.percentage_discount_error'));
                }
            },
        ];

        if ($this->use_conditions) {
            foreach ($this->form['conditions'] as $condition => $value) {
                if (!empty($this->conditions[$condition]['required_input'])) {
                    $rules['form.conditions.' . $condition] = 'required';
                }
            }
        }

        if (Auth::user()->hasRole('admin')) {
            unset($rules['form.couponable_type'], $rules['form.couponable_id']);

            foreach ($this->lines as $index => $line) {
                $rules["lines.$index.instructorId"]    = 'required|exists:users,id';
                $rules["lines.$index.couponable_type"] = 'required|string';
                $rules["lines.$index.couponable_id"]   = 'required|array|min:1';
            }
        } else {
            $rules['form.couponable_type'] = 'required|string';
            $rules['form.couponable_id']   = 'required|array|min:1';
        }

        $this->validate($rules);

        $this->form['user_id'] = Auth::id();

        if (Auth::user()->hasRole('admin')) {

            if ($this->selectAllInstructors) {
                $allInstructors = User::whereHas('roles', function ($q) {
                    $q->where('name', 'tutor');
                })->pluck('id')->toArray();

                foreach ($allInstructors as $instructorId) {
                    foreach ($this->lines as $lineIndex => $line) {
                        $type  = $line['couponable_type'] ?? null;
                        if (!$type) continue;

                        $items = $this->initOptions($type, $instructorId);

                        $allItemIds = collect($items)->pluck('id')->toArray();

                        $data = $this->form;
                        $data['instructor_id']   = $instructorId;
                        $data['couponable_type'] = $type;
                        $data['couponable_id']   = $allItemIds;
                        $data['code'] = $this->generateUniqueCode($data['code']);

                        \Log::info('Final Data Before Save (Admin, SelectAll, One Record per Instructor):', $data);
                        $this->couponService->updateOrCreateCoupon($data);
                    }
                }
            } else {
                foreach ($this->lines as $lineIndex => $line) {
                    $instructorId = $line['instructorId'] ?? null;
                    $type         = $line['couponable_type'] ?? null;
                    if (empty($instructorId) || empty($type)) continue;

                    $items = $this->initOptions($type, $instructorId);
                    $allItemIds = collect($items)->pluck('id')->toArray();

                    $data = $this->form;
                    $data['instructor_id']   = $instructorId;
                    $data['couponable_type'] = $type;
                    $data['couponable_id']   = $allItemIds;
                    $data['code'] = $this->generateUniqueCode($data['code']);

                    $this->couponService->updateOrCreateCoupon($data);
                }
            }
        } else {

            foreach ($this->form['couponable_id'] as $itemId) {
                $data = $this->form;
                $data['instructor_id']   = Auth::id();
                $data['couponable_type'] = $this->form['couponable_type'];
                $data['couponable_id']   = $itemId;

                $this->couponService->updateOrCreateCoupon($data);
            }
        }


        $this->resetForm();
        $this->use_conditions = false;

        $this->dispatch(
            'showAlertMessage',
            type: 'success',
            title: $this->form['id'] ? __('kupondeal::kupondeal.coupon_updated') : __('kupondeal::kupondeal.coupon_added'),
            message: $this->form['id'] ? __('kupondeal::kupondeal.coupon_updated_success') : __('kupondeal::kupondeal.coupon_added_success')
        );

        $this->dispatch('toggleModel', id: 'kd-create-coupon', action: 'hide');
    }


    public function openModal()
    {
        $this->resetForm();
        $this->use_conditions = false;
        $this->dispatch('createCoupon', color: '#000000');
        if (!(\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))) {
            $data = $this->initOptions(UserSubjectGroupSubject::class);
            $this->dispatch('couponableValuesUpdated', options: $data, reset: $this->isEdit);
        }
        $this->dispatch('couponableValuesUpdated', options: array_values($this->couponable_ids), reset: true);
    }
    public function resetForm()
    {
        $this->form = [
            'id' => null,
            'user_id' => '',
            'code' => '',
            'discount_type' => '',
            'discount_value' => '',
            'expiry_date' => '',
            'couponable_type' => '',
            'couponable_id' => [],
            'auto_apply' => 0,
            'color' => '',
            'conditions' => [],
            'status' => 1,
            'description' => '',
            'instructor_id' => '',
        ];
        $this->resetErrorBag();
    }

    public function updatedInstructorId($value)
    {
        if ($this->form['couponable_type']) {
            $user = \App\Models\User::find($value);
            $this->subjectService = new SubjectService($user);
            $this->couponable_ids = $this->initOptions($this->form['couponable_type']);
            $this->form['couponable_id'] = [];
        }
    }

    public function updatedLines($value, $key)
    {
        [$index, $field] = explode('.', $key);
        $line = $this->lines[$index];

        if ($field === 'couponable_type' && !empty($line['selectedInstructors'])) {
            $type = $line['couponable_type'];
            $allItems = [];

            foreach ($line['selectedInstructors'] as $instructorId) {
                $items = $this->initOptions($type, $instructorId);
                foreach ($items as $item) {
                    $allItems[] = [
                        'instructor_id' => $instructorId,
                        'id' => $item['id'],
                        'title' => $item['title'],
                    ];
                }
            }

            $this->lines[$index]['couponable_ids'] = $allItems;
            $this->lines[$index]['couponable_id'] = collect($allItems)->pluck('id')->toArray();
        }
    }
}
