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
        'couponable_ids' => [],
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

    public $hideLines = false;


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
    public function selectAllInstructors()
    {
        foreach ($this->lines as $index => $line) {
            $instructorId = $line['instructorId'];
            if ($instructorId) {
                $this->lines[$index]['couponable_ids'] = $this->initOptions($line['couponable_type'], $instructorId);
                $this->lines[$index]['couponable_id'] = collect($this->lines[$index]['couponable_ids'])->pluck('id')->toArray();
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




    public function addCoupon()
    {

        \Log::info('Form Data:', $this->form);
        $request = new CouponRequest();
        $this->form['expiry_date'] = !empty($this->form['expiry_date'])
            ? Carbon::parse($this->form['expiry_date'])->format('Y-m-d')
            : null;

        if (!(\Nwidart\Modules\Facades\Module::has('courses')
            && \Nwidart\Modules\Facades\Module::isEnabled('courses'))) {
            $this->form['couponable_type'] = UserSubjectGroupSubject::class;
        }

        $rules = $request->rules();
        $rules['form.description'] = ['nullable', 'string', 'max:500'];
        $messages = $request->messages();
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
                    $messages['form.conditions.' . $condition] = __('kupondeal::kupondeal.' . $condition . '_field_error');
                }
            }
        }

        $this->validate($rules, $messages);

        $this->form['user_id'] = Auth::id();


        if ($this->isAdmin && count($this->lines) > 0) {
            foreach ($this->lines as $line) {
                $instructorId = $line['instructorId'];
                $type = $line['couponable_type'];
                $ids = $line['couponable_id'] ?? [];

                if ($instructorId && $type && !empty($ids)) {
                    foreach ($ids as $couponableId) {
                        $data = $this->form;
                        $data['instructor_id'] = $instructorId;
                        $data['couponable_type'] = $type;
                        $data['couponable_id'] = $couponableId;
                        $this->couponService->updateOrCreateCoupon($data);
                    }
                }
            }
        } else {

            $this->form['instructor_id'] = Auth::id();

            foreach ($this->form['couponable_id'] as $couponableId) {
                $data = $this->form;
                $data['couponable_id'] = $couponableId;
                $this->couponService->updateOrCreateCoupon($data);
            }
        }

        $this->resetForm();
        $this->use_conditions = false;

        $this->dispatch(
            'showAlertMessage',
            type: 'success',
            title: $this->form['id'] ? _('kupondeal::kupondeal.coupon_updated') : _('kupondeal::kupondeal.coupon_added'),
            message: $this->form['id'] ? _('kupondeal::kupondeal.coupon_updated_success') : _('kupondeal::kupondeal.coupon_added_success')
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
            'couponable_ids' => [],
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

        $type = $this->lines[$index]['couponable_type'] ?? null;
        $instructorId = $this->lines[$index]['instructorId'] ?? null;

        if ($field === 'couponable_type' || $field === 'instructorId') {
            if ($type && $instructorId) {
                $options = $this->initOptions($type, $instructorId);
                $this->lines[$index]['couponable_ids'] = $options;

                $this->lines[$index]['couponable_id'] = collect($options)->pluck('id')->toArray();
            } else {
                $this->lines[$index]['couponable_ids'] = [];
                $this->lines[$index]['couponable_id'] = [];
            }
        }
    }
}
