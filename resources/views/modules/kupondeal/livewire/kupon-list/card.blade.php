{{-- @dd($selectAllInstructors) --}}
<div wire:ignore.self class="modal fade kd-coupon-modal" id="kd-create-coupon" tabindex="-1"
    aria-labelledby="create-couponLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered ">
        <div class="modal-content">
            <div class="am-modal-header">
                <h2>{{ !empty($form['id']) ? __('kupondeal::kupondeal.edit_coupon') : __('kupondeal::kupondeal.create_coupon') }}
                </h2>
                <span data-bs-dismiss="modal" class="am-closepopup">
                    <i class="am-icon-multiply-01"></i>
                </span>
            </div>
            <div class="am-modal-body">
                <form class="am-themeform">
                    <fieldset>
                        <div class="am-themeform__wrap">
                            <div class="form-group @error('form.code') am-invalid @enderror">
                                <label for="code"
                                    class="am-important">{{ __('kupondeal::kupondeal.coupon_code') }}</label>
                                <x-kupondeal::text-input type="text" wire:model="form.code" id="code"
                                    placeholder="#LEARN" />
                                <x-kupondeal::input-error field_name='form.code' />
                            </div>
                            <div style="width: 100%">
                                @if ($isAdmin)
                                    @foreach ($lines as $index => $line)
                                        <div class="line-wrapper position-relative"
                                            style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; border-radius: 8px;">


                                            @if (!$isLocked && count($lines) > 1 && $index > 0)
                                                <button type="button" wire:click="removeLine({{ $index }})"
                                                    class="btn btn-sm  position-absolute"
                                                    style="top:10px; left:10px; border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">
                                                    ✕
                                                </button>
                                            @endif

                                            {{-- select instructor --}}
                                            <div class="form-group @error('instructorId') am-invalid @enderror">
                                                <x-input-label class="am-important"
                                                    for="instructorId">{{ __('kupondeal::kupondeal.select_instructor') }}</x-input-label>
                                                <span class="am-select">
                                                    <select wire:model.live="lines.{{ $index }}.instructorId"
                                                        wire:change="handleInstructorChange({{ $index }}, $event.target.value)"
                                                        @if ($isLocked) disabled @endif
                                                        wire:key="instructor-{{ $index }}"
                                                        class="instructor-select-{{ $index }}">
                                                        <option value="">
                                                            {{ __('kupondeal::kupondeal.select_instructor_placeholder') }}
                                                        </option>
                                                        <option value="alltutors">{{ __('Select All') }}</option>
                                                        @foreach ($instructors as $instructor)
                                                            <option value="{{ $instructor->id }}">
                                                                {{ $instructor->profile?->first_name }}
                                                                {{ $instructor->profile?->last_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </span>
                                                <x-kupondeal::input-error
                                                    field_name="lines.{{ $index }}.instructorId" />
                                            </div>

                                            @if (\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))
                                                <div class="form-group ">
                                                    <x-input-label class="am-important"
                                                        for="couponable_type">{{ __('kupondeal::kupondeal.couponable_type') }}</x-input-label>
                                                    <span class="am-select">
                                                        <select
                                                            wire:model.live="lines.{{ $index }}.couponable_type"
                                                            wire:change="handleCouponableTypeChange({{ $index }}, $event.target.value)"
                                                            class="am-select2"
                                                            data-placeholder="{{ __('kupondeal::kupondeal.select_couponable_type') }}">
                                                            <option value="">
                                                                {{ __('kupondeal::kupondeal.select_couponable_type') }}
                                                            </option>
                                                            <option value="__ALL__">{{ __('Select All') }}</option>
                                                            @foreach ($couponable_types as $type)
                                                                <option value="{{ $type['value'] }}">
                                                                    {{ $type['label'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </span>
                                                    <x-kupondeal::input-error
                                                        field_name="lines.{{ $index }}.couponable_type" />
                                                </div>
                                            @endif

                                            <div class="form-group @error('form.couponable_id') am-invalid @enderror"
                                                wire:loading.class="am-disabled"
                                                wire:loading.target="form.couponable_type">
                                                <x-input-label class="am-important"
                                                    for="couponable_id">{{ Module::has('courses') && Module::isEnabled('courses') ? __('kupondeal::kupondeal.couponable_id') : __('kupondeal::kupondeal.subject') }}</x-input-label>
                                                <select multiple wire:model="lines.{{ $index }}.couponable_id"
                                                    data-disable_onchange="true" class="am-select2"
                                                    @if (!Module::has('courses') || !Module::isEnabled('courses')) disabled @endif
                                                    @if ($isLocked) disabled @endif>
                                                    @foreach ($line['couponable_ids'] as $c)
                                                        <option value="{{ $c['id'] }}">{{ $c['title'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <x-kupondeal::input-error
                                                    field_name="lines.{{ $index }}.couponable_id" />

                                                @if (!$isLocked)
                                                    <label>
                                                        <input type="checkbox"
                                                            wire:model="lines.{{ $index }}.select_all"
                                                            wire:change="selectAll({{ $index }})">
                                                        {{ __('Select All') }}
                                                    </label>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                            </div>

                            {{-- زرار Add Line --}}
                            @if (!$isLocked)
                                <div class="text-center mt-0">
                                    <button type="button" wire:click="addLine" class="btn outline-success px-4 py-2"
                                        style="border-radius: 6px; font-weight: bold;">
                                        + {{ __('Add Line') }}
                                    </button>
                                </div>
                            @endif
                        @else
                        @if (\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))
                                <div class="form-group @error('form.couponable_type') am-invalid @enderror">
                                    <x-input-label class="am-important"
                                        for="couponable_type">{{ __('kupondeal::kupondeal.couponable_type') }}</x-input-label>
                                    <span class="am-select" wire:ignore>
                                        <select class="am-select2" data-componentid="@this" id="couponable_type"
                                            data-live="true" data-wiremodel="form.couponable_type"
                                            data-placeholder="{{ __('kupondeal::kupondeal.select_couponable_type') }}">
                                            <option value="">
                                                {{ __('kupondeal::kupondeal.select_couponable_type') }}</option>
                                            @foreach ($couponable_types as $couponable_type)
                                                <option value="{{ $couponable_type['value'] }}"
                                                    @if ($form['couponable_type'] == $couponable_type['value']) selected @endif>
                                                    {{ $couponable_type['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                    <x-kupondeal::input-error field_name='form.couponable_type' />
                                </div>
                            @endif
                            <div class="form-group @error('form.couponable_id') am-invalid @enderror"
                                wire:loading.class="am-disabled" wire:loading.target="form.couponable_type">

                                <x-input-label class="am-important"
                                    for="couponable_id">{{ Module::has('courses') && Module::isEnabled('courses') ? __('kupondeal::kupondeal.couponable_id') : __('kupondeal::kupondeal.subject') }}</x-input-label>

                                <select multiple id="couponable_id" class="form-control" wire:model="form.couponable_id"
                                    @if (!Module::has('courses') || !Module::isEnabled('courses')) disabled @endif>

                                    @foreach ($couponable_ids as $couponable)
                                        <option value="{{ $couponable['id'] }}">
                                            {{ $couponable['title'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <x-kupondeal::input-error field_name='form.couponable_id' />
                            </div>

                            @endif
                        </div>
                        <div class="form-group @error('form.discount_type') am-invalid @enderror">
                            <x-input-label class="am-important"
                                for="discount_type">{{ __('kupondeal::kupondeal.discount_type') }}</x-input-label>
                            <span class="am-select" wire:ignore>
                                <select class="am-select2" data-componentid="@this" id="discount_type" data-live="true"
                                    data-wiremodel="form.discount_type"
                                    data-placeholder="{{ __('kupondeal::kupondeal.select_discount_type') }}">
                                    <option value="">{{ __('kupondeal::kupondeal.select_discount_type') }}
                                    </option>
                                    <option value="fixed" @if ($form['discount_type'] == 'fixed') selected @endif>
                                        {{ __('kupondeal::kupondeal.fixed_price') }}</option>
                                    <option value="percentage" @if ($form['discount_type'] == 'percentage') selected @endif>
                                        {{ __('kupondeal::kupondeal.percentage') }}</option>
                                </select>
                            </span>
                            <x-kupondeal::input-error field_name='form.discount_type' />
                        </div>
                        <div class="form-group @error('form.discount_value') am-invalid @enderror">
                            <label for="discount_value">{{ __('kupondeal::kupondeal.discount_value') }}</label>
                            <x-kupondeal::text-input type="number" wire:model="form.discount_value"
                                id="discount_value" placeholder="Enter discount value" />
                            <x-kupondeal::input-error field_name='form.discount_value' />
                        </div>
                        <div class="form-group @error('form.expiry_date')am-invalid @enderror">
                            <label for="expiry_date">{{ __('kupondeal::kupondeal.expiry_date') }}</label>
                            <div class="am-booking-calander-date flatpicker">
                                <x-text-input class="flat-date" wire:model="form.expiry_date" id="expiry_date"
                                    data-min-date="today" data-format="Y-m-d"
                                    placeholder="{{ __('kupondeal::kupondeal.expiry_date') }}" type="text"
                                    id="datepicker" autofocus autocomplete="name" />
                            </div>
                            <x-input-error field_name='form.expiry_date' />
                        </div>
                        <div class="kd-colorpicker_wrap form-group">
                            <label class="am-important"
                                for="expiry_date">{{ __('kupondeal::kupondeal.badge_color') }}</label>
                            <div wire:ignore>
                                <div class="kd-colorpicker myColorPicker">
                                    <span class="input-group-addon kd-colordemo myColorPicker-preview">&nbsp;</span>
                                    <input type="text" class="form-control">
                                </div>
                            </div>
                            <x-input-error field_name='form.color' />
                        </div>
                        <div class="form-group @error('form.description') am-invalid @enderror">
                            <label for="description" class="am-important">
                                {{ __('kupondeal::kupondeal.description') }}
                            </label>
                            <textarea class="form-control" rows="3" id="description" wire:model.lazy="form.description"
                                placeholder="{{ __('kupondeal::kupondeal.enter_description') }}"></textarea>
                            <x-kupondeal::input-error field_name="form.description" />
                        </div>
                        @if (
                            !(\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses')) ||
                                (!empty($form['couponable_type']) && $form['couponable_type'] == \App\Models\UserSubjectGroupSubject::class))
                            <div class="form-group">
                                <div class="kd-switchbtn">
                                    <label for="auto_apply"
                                        class="cr-label">{{ __('kupondeal::kupondeal.auto_apply') }}</label>
                                    <input type="checkbox" id="auto_apply" class="cr-toggle"
                                        wire:model="form.auto_apply">
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <div class="am-switchbtn-box">
                                <x-input-label for=""
                                    class="am-label">{{ __('kupondeal::kupondeal.condition') }}</x-input-label>
                                <div class="am-switchbtn">
                                    <label for="condition_switch"
                                        class="cr-label">{{ __('kupondeal::kupondeal.condition_desc') }}</label>
                                    <input type="checkbox" id="condition_switch" class="cr-toggle" value="1"
                                        wire:model.live="use_conditions">
                                </div>
                                @if ($use_conditions)
                                    @foreach ($conditions as $key => $condition)
                                        <div wire:key="condition-{{ $key }}-{{ time() }}-add"
                                            @class([
                                                'kd-coupon-order',
                                                'active' => in_array($key, array_keys($form['conditions'])),
                                            ]) x-data="{
                                                formConditions: @entangle('form.conditions'),
                                                conditions: @js($conditions)
                                            }">
                                            <div class="kd-coupon-order_title"
                                                wire:click="addCondition('{{ $key }}')">
                                                <h4>{{ $condition['text'] }}</h4>
                                                <span>{{ $condition['desc'] }}</span>
                                            </div>
                                            @if (in_array($key, array_keys($form['conditions'])))
                                                <div class="kd-coupon-order_btns">
                                                    <span
                                                        wire:key="condition-{{ $key }}-{{ time() }}-delete"
                                                        class="am-order-delete"
                                                        wire:click.prevent="removeCondition('{{ $key }}')"><i
                                                            class="am-icon-trash-02"></i></span>
                                                    @if ($condition['required_input'])
                                                        <span class="am-order-edit"
                                                            wire:key="condition-{{ $key }}-{{ time() }}-edit"
                                                            @click="jQuery('#{{ $key }}-form').removeClass('d-none')"><i
                                                                class="am-icon-pencil-01"></i></span>
                                                    @endif
                                                </div>
                                            @endif
                                            @if ($condition['required_input'] && in_array($key, array_keys($form['conditions'])))
                                                <div id="{{ $key }}-form"
                                                    class="kd-coupon-order_form @if (!empty($form['conditions'][$key])) d-none @endif"
                                                    wire:key="condition-{{ $key }}-{{ time() }}-edit-form"
                                                    wire:ignore.self>
                                                    <label for="payment"
                                                        class="am-important">{{ __('kupondeal::kupondeal.enter_min_amount') }}</label>
                                                    <input type="text"
                                                        wire:model.lazy="form.conditions.{{ $key }}"
                                                        placeholder="{!! getCurrencySymbol() !!}"
                                                        @blur="jQuery('#{{ $key }}-form').addClass('d-none')">
                                                    <x-kupondeal::input-error
                                                        field_name='form.conditions.{{ $key }}' />
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="form-group am-btn-group">
                            <button type="button" class="am-white-btn"
                                data-bs-dismiss="modal">{{ __('kupondeal::kupondeal.close') }}</button>
                            <button wire wire:click="addCoupon" type="button" class="am-btn"
                                wire:loading.attr="disabled" wire:loading.class="btn_disable">
                                <span wire:loading.remove wire:target="addCoupon">
                                    {{ !empty($form['id']) ? __('kupondeal::kupondeal.update_changes') : __('kupondeal::kupondeal.save_changes') }}
                                </span>
                                <span wire:loading wire:target="addCoupon">
                                    {{ !empty($form['id']) ? __('kupondeal::kupondeal.updating') : __('kupondeal::kupondeal.saving') }}
                                </span>
                            </button>
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
