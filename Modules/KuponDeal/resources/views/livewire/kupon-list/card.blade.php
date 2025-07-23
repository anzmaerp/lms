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
                            @if (\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))
                                <div class="form-group @error('form.couponable_type') am-invalid @enderror">
                                    <x-input-label class="am-important"
                                        for="couponable_type">{{ __('kupondeal::kupondeal.couponable_type') }}</x-input-label>
                                    <span class="am-select" wire:ignore>
                                        <select class="am-select2" multiple data-componentid="@this"
                                            id="couponable_type" data-live="true" data-wiremodel="form.couponable_type"
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
                                    for="couponable_id">{{ \Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses') ? __('kupondeal::kupondeal.couponable_id') : __('kupondeal::kupondeal.subject') }}</x-input-label>
                                <span class="am-select" wire:ignore>
                                    <select mclass="am-select2" data-componentid="@this" id="couponable_id"
                                        data-live="true" data-searchable="true" data-wiremodel="form.couponable_id"
                                        @if (\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses')) disabled @endif
                                        data-placeholder="{{ __('kupondeal::kupondeal.select_option') }}">
                                        <option value="">{{ __('kupondeal::kupondeal.select_option') }}</option>
                                        @foreach ($couponable_ids as $couponable)
                                            <option value="{{ $couponable['id'] }}"
                                                @if ($form['couponable_id'] == $couponable['id']) selected @endif>
                                                {{ $couponable['title'] }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                <x-kupondeal::input-error field_name='form.couponable_id' />
                            </div>
                            <div class="form-group @error('form.discount_type') am-invalid @enderror">
                                <x-input-label class="am-important"
                                    for="discount_type">{{ __('kupondeal::kupondeal.discount_type') }}</x-input-label>
                                <span class="am-select" wire:ignore>
                                    <select class="am-select2" data-componentid="@this" id="discount_type"
                                        data-live="true" data-wiremodel="form.discount_type"
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
                                        <input type="checkbox" id="condition_switch" class="cr-toggle"
                                            value="1" wire:model.live="use_conditions">
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
                            <div class="form-group @error('form.description') am-invalid @enderror">
                                <label for="description">{{ __('kupondeal::kupondeal.description') }}</label>
                                <textarea id="description" class="form-control" wire:model="form.description" rows="3"
                                    placeholder="{{ __('kupondeal::kupondeal.enter_description') }}"></textarea>
                                <x-kupondeal::input-error field_name='form.description' />
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
                        </div>
                    </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
