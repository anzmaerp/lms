<div class="am-cr-bundle">
    <div class="am-userperinfo">
        <div class="am-title_wrap">
            <div class="am-title">
                <h2>{{ __('coursebundles::bundles.course_bundle') }}</h2>
                <p>{{ __('coursebundles::bundles.create_bundle_desc') }}</p>
            </div>
        </div>

        <form class="am-themeform am-themeform_personalinfo">
            <fieldset>
                <div class="form-group @error('title') am-invalid @enderror">
                    <label class="am-label am-important">{{ __('coursebundles::bundles.bundle_title') }}</label>
                    <div class="form-control_wrap">
                        <input class="form-control" wire:model="title"
                            placeholder="{{ __('coursebundles::bundles.bundle_title_placeholder') }}" type="text">
                        <x-input-error field_name="title" />
                    </div>
                </div>

                @if ($isAdmin)
                    @foreach ($lines as $index => $line)
                        <div wire:key="line-{{ $index }}" class="line-wrapper position-relative"
                            style="border: 1px solid #ddd; padding: 15px; margin: 15px 0; border-radius: 8px;">
                            @if (count($lines) > 1 && $index > 0 && !($lines[$index]['isLocked'] ?? false))
                                <button type="button" wire:click="removeLine({{ $index }})"
                                    class="btn btn-sm position-absolute"
                                    style="top:-5px; left:0px; border-radius:50%; width:34px; height:34px; display:flex; align-items:center; justify-content:center; font-weight:bold; font-size:16px;">
                                    ✕
                                </button>
                            @endif
                            <div class="form-group @error('lines.' . $index . '.instructorId') am-invalid @enderror"
                                style="display:flex; gap:135px; padding: 10px 0">
                                <label class="am-label am-important">
                                    {{ __('coursebundles::bundles.select_instructor') }}
                                </label>
                                <div class="form-control_wrap">
                                    <select wire:model.live="lines.{{ $index }}.instructorId"
                                        wire:key="instructor-{{ $index }}"
                                        class="instructor-select-{{ $index }}"
                                        @if ($lines[$index]['isLocked'] ?? false) disabled @endif>
                                        <option value="">
                                            {{ __('coursebundles::bundles.select_instructor_placeholder') }}
                                        </option>
                                        <option value="alltutors">{{ __('Select All') }}</option>
                                        @foreach ($instructors as $instructor)
                                            <option value="{{ $instructor->id }}">
                                                {{ $instructor->profile?->first_name }}
                                                {{ $instructor->profile?->last_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-kupondeal::input-error field_name="lines.{{ $index }}.instructorId" />
                                </div>
                            </div>
                            <div class="form-group @error('lines.' . $index . '.selected_courses') am-invalid @enderror"
                                style="display:flex; gap:115px; padding: 0;">
                                <label class="am-label am-important">
                                    {{ __('coursebundles::bundles.select_courses') }}
                                </label>
                                <div class="form-group-two-wrap am-nativelang">
                                    <div>
                                        <span class="am-select am-multiple-select">
                                            <select wire:model.live="lines.{{ $index }}.selected_courses"
                                                name="lines[{{ $index }}][selected_courses][]" multiple
                                                wire:key="courses-{{ $index }}"
                                                class="courses-select-{{ $index }}"
                                                @if ($lines[$index]['isLocked'] ?? false) disabled @endif>
                                                @foreach ($line['courses'] ?? [] as $course)
                                                    <option value="{{ $course['id'] }}"
                                                        {{ in_array($course['id'], $line['selected_courses'] ?? []) ? 'selected' : '' }}>
                                                        {{ $course['title'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </span>
                                        <x-kupondeal::input-error field_name="lines.{{ $index }}.selected_courses" />
                                        @if (!$selectAllInstructors)
                                            <label>
                                                <input type="checkbox" wire:click="selectAll({{ $index }})">
                                                {{ __('general.select_all') }}
                                            </label>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if (!$selectAllInstructors)
                        <button type="button" wire:click="addLine">{{ __('general.add_line') }}</button>
                    @endif
                @else
                    <div class="form-group @error('selected_courses') am-invalid @enderror">
                        <label class="am-label am-important">
                            {{ __('coursebundles::bundles.select_courses') }}
                            <div class="am-custom-tooltip">
                                <span class="am-tooltip-text">
                                    <span>{{ __('coursebundles::bundles.tooltip_time_limit') }}</span>
                                </span>
                                <i class="am-icon-exclamation-01"></i>
                            </div>
                        </label>
                        <div class="form-group-two-wrap am-nativelang">
                            <div x-init="$wire.dispatch('initSelect2', { target: '.am-select2', data: @js($courses), selected: @js($selected_courses) });">
                                <span class="am-select am-multiple-select">
                                                <select
                                                    class="languages am-select2"
                                                    data-placeholder="{{ __('coursebundles::bundles.select_courses_placeholder') }}"
                                                    data-componentid="@this"
                                                    data-wiremodel="selected_courses"
                                                    data-searchable="true"
                                                    id="selected_courses"
                                                    multiple
                                                >
                                        @foreach ($courses as $course)
                                            <option value="{{ $course['id'] }}"
                                                {{ in_array($course['id'], $selected_courses ?? []) ? 'selected' : '' }}>
                                                {{ $course['text'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </span>
                                <x-input-error field_name="selected_courses" />
                            </div>
                            <div class="tu-labels languageList"></div>
                        </div>
                    </div>
                @endif

                <div class="form-group @error('short_description') am-invalid @enderror">
                    <label class="am-label am-important">{{ __('coursebundles::bundles.bundle_short_description') }}</label>
                    <div class="form-control_wrap">
                        <input class="form-control" wire:model="short_description"
                            placeholder="{{ __('coursebundles::bundles.bundle_short_description_placeholder') }}"
                            type="text">
                        <x-input-error field_name="short_description" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="am-label am-important">{{ __('coursebundles::bundles.bundle_thumbnail') }}</label>
                    <div class="am-uploadoption" x-data="{ isUploading: false }"
                        wire:key="uploading-image-{{ time() }}">
                        <div class="tk-draganddrop" wire:loading.class="am-uploading" wire:target="image"
                            x-bind:class="{ 'am-dragfile': isDragging, 'am-uploading': isUploading }"
                            x-on:drop.prevent="isUploading = true; isDragging = false"
                            wire:drop.prevent="$upload('image', $event.dataTransfer.files[0])">
                            <x-text-input name="file" type="file" id="at_upload_image" x-ref="file_upload_image"
                                accept="{{ !empty($allowImgFileExt) ? join(',', array_map(function ($ex) { return '.' . $ex; }, $allowImgFileExt)) : '*' }}"
                                x-on:change="isUploading = true; $wire.upload('image', $refs.file_upload_image.files[0])" />
                            <label for="at_upload_image" class="am-uploadfile">
                                <span class="am-dropfileshadow">
                                    <i class="am-icon-plus-02"></i>
                                    <span class="am-uploadiconanimation">
                                        <i class="am-icon-upload-03"></i>
                                    </span>
                                    {{ __('coursebundles::bundles.drop_file_here') }}
                                </span>
                                <em>
                                    <i class="am-icon-export-03"></i>
                                </em>
                                <span>{!! __('coursebundles::bundles.upload_file_text') !!}
                                    <em>{{ __('coursebundles::bundles.upload_file_format') }}</em>
                                </span>
                                <svg class="am-border-svg">
                                    <rect width="100%" height="100%" rx="12"></rect>
                                </svg>
                            </label>
                        </div>
                        <x-input-error field_name="image" />
                        @if (!empty($image))
                            <div class="am-uploadedfile">
                                @if (method_exists($image, 'temporaryUrl'))
                                    <img src="{{ $image->temporaryUrl() }}" />
                                    <span>{{ basename(parse_url($image->temporaryUrl(), PHP_URL_PATH)) }}</span>
                                @elseif (!empty($bundle->thumbnail) && Storage::disk(getStorageDisk())->exists($bundle->thumbnail?->path))
                                    <img src="{{ Storage::url($bundle->thumbnail?->path) }}" />
                                    <span>{{ Str::replace('course_bundles/', '', $bundle->thumbnail?->path) }}</span>
                                @endif
                                <a href="#" wire:click.prevent="removePhoto()" class="am-delitem">
                                    <i class="am-icon-trash-02"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                @if (isPaidSystem())
                    <div class="am-cr-bundle_price">
                        <div class="am-title_wrap">
                            <div class="am-title am-sub-title">
                                <h2>{{ __('coursebundles::bundles.pricing') }}</h2>
                                <p>{{ __('coursebundles::bundles.pricing_desc') }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="am-label am-important">{{ __('coursebundles::bundles.regular_price') }}</label>
                        <div class="form-control_wrap sr-formgroup">
                            <input class="form-control regular-price-input" placeholder="450" type="text">
                            <i>$</i>
                        </div>
                    </div>
                    <div class="form-group @error('price') am-invalid @enderror">
                        <label class="am-label am-important" for="course-subtitle">{{ __('coursebundles::bundles.sale_price') }}</label>
                        <div class="form-group-two-wrap am-cr-discount-wrap">
                            <div class="at-form-group">
                                <input type="number" wire:model.live.debounce.500ms="price" id="price" placeholder="150">
                                <i>{{ getCurrencySymbol() }}</i>
                            </div>
                            <div class="am-cr-allow-discount">
                                <label for="allow-discount" class="am-cr-label">
                                    {{ __('coursebundles::bundles.allow_discount') }}
                                    <span class="am-cr-optional">{{ __('coursebundles::bundles.optional') }}</span>
                                </label>
                                <input type="checkbox" wire:click="toggleDiscountAllowed" id="allow-discount"
                                    class="am-cr-toggle" wire:model="discountAllowed">
                            </div>
                            <x-input-error field_name="price" />
                        </div>
                    </div>
                    @if ($discountAllowed)
                        <div class="form-group">
                            <div class="am-cr-choose_discount">
                                <div class="am-cr-free_course">
                                    <div class="am-cr-free_discription">
                                        <label>{{ __('coursebundles::bundles.choose_discount_amount') }}</label>
                                        <p>{{ __('coursebundles::bundles.discount_description') }}</p>
                                    </div>
                                    @if (!empty($final_price))
                                        <strong>
                                            {{ formatAmount($final_price) }}
                                            <span>{{ __('coursebundles::bundles.original_price') }}</span>
                                        </strong>
                                    @endif
                                </div>
                                <div class="am-cr-discount-table am-payouthistory">
                                    <table class="am-table">
                                        <thead>
                                            <tr>
                                                <th>{{ __('coursebundles::bundles.discount_amount') }}</th>
                                                <th>{{ __('coursebundles::bundles.discount_price') }}</th>
                                                <th>{{ __('coursebundles::bundles.purchase_price') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($discounts as $discountPercentage)
                                                <tr>
                                                    <td data-label="Discount percentage">
                                                        <div class="am-radio">
                                                            <input name="discount_percentage"
                                                                @if ($discountPercentage == $discount) checked @endif
                                                                wire:click="updateDiscount({{ $discountPercentage }})"
                                                                type="radio"
                                                                id="discount-{{ $discountPercentage }}"
                                                                value="{{ $discountPercentage }}">
                                                            <label for="discount-{{ $discountPercentage }}">{{ $discountPercentage }}%</label>
                                                        </div>
                                                    </td>
                                                    <td data-label="Discount price">
                                                        <span>${{ number_format(((float) $discountPercentage / 100) * (float) $this->price, 2) }}</span>
                                                    </td>
                                                    <td data-label="Purchase price">
                                                        <span>${{ number_format((1 - (float) $discountPercentage / 100) * (float) $this->price, 2) }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td data-label="Discount percentage">
                                                    <div class="am-cr-input-wrap">
                                                        <div class="am-radio">
                                                            <input name="discount_percentage"
                                                                @if ($discount == $customDiscount) checked @endif
                                                                wire:click="updateCustomDiscount" type="radio"
                                                                id="discount-6">
                                                            <label for="discount-6"></label>
                                                        </div>
                                                        <input wire:model.live.debounce.500ms="customDiscount"
                                                            type="text" placeholder="70">%
                                                    </div>
                                                </td>
                                                <td data-label="Discount price">
                                                    <span>{{ formatAmount(((int) $customDiscount / 100) * (float) $this->price) }}</span>
                                                </td>
                                                <td data-label="Purchase price">
                                                    <span>{{ formatAmount((1 - (int) $customDiscount / 100) * (float) $this->price) }}</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
                <div class="am-cr-bundle_description">
                    <div class="am-title_wrap">
                        <div class="am-title am-sub-title">
                            <h2>{{ __('coursebundles::bundles.bundle_description') }}</h2>
                            <p>{{ __('coursebundles::bundles.bundle_description_desc') }}</p>
                        </div>
                    </div>
                </div>
                <div class="form-group @error('course_description') am-invalid @enderror">
                    <div wire:key="course_description{{ time() }}" id="course_description{{ time() }}"
                        x-data="{ contentDesc: @entangle('course_description') }"
                        x-init="$wire.dispatch('initSummerNote', {
                            target: '#course_description',
                            wiremodel: 'course_description',
                            content: contentDesc,
                            componentId: @this
                        });"
                        class="form-control_wrap am-custom-editor am-custom-textarea" wire:ignore>
                        <textarea id="course_description" class="form-control am-question-desc"
                            placeholder="Add course description..."
                            data-textarea="course_description"></textarea>
                        <span class="characters-count"></span>
                    </div>
                    <x-input-error field_name="course_description" />
                </div>
                <div class="form-group am-form-btns">
                    <a href="{{ route('coursebundles.tutor.bundles') }}" wire:click="$set('step', 'course-list')">
                        <button type="button" class="am-white-btn">{{ __('coursebundles::bundles.back') }}</button>
                    </a>
                    @if (empty($bundleId))
                        <button type="button" class="am-btn"
                            wire:click="saveCourseBundle">{{ __('coursebundles::bundles.save') }}</button>
                    @else
                        <button type="button" class="am-btn"
                            wire:click="updateCourseBundle({{ $bundleId }})">{{ __('coursebundles::bundles.save') }}</button>
                    @endif
                </div>
            </fieldset>
        </form>
    </div>
</div>

@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/coursebundles/css/main.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @vite(['public/summernote/summernote-lite.min.css'])
@endpush

@push('scripts')
    <script>
        window.courses = @json($courses ?? []);
    </script>
    <script defer src="{{ asset('summernote/summernote-lite.min.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('livewire:load', function () {
            Livewire.on('initSelect2', ({ target, data, selected }) => {
                const $select = $(target);
                $select.select2({
                    placeholder: $select.data('placeholder') || 'Select options',
                    allowClear: true,
                    data: data.map(item => ({
                        id: item.id,
                        text: item.text || item.title
                    })),
                    multiple: true,
                    search: $select.data('searchable') === 'true'
                });

                // Restore selected values
                if (selected && Array.isArray(selected)) {
                    $select.val(selected).trigger('change');
                }

                // Update Livewire on change
                $select.on('change', function () {
                    let componentId = $select.data('componentid');
                    let wireModel = $select.data('wiremodel');
                    if (componentId && wireModel) {
                        Livewire.find(componentId).set(wireModel, $(this).val());
                    }
                });
            });

            Livewire.on('initSelect2Line', ({ index, data, selected }) => {
                const $select = $(`.courses-select-${index}`);
                $select.select2({
                    placeholder: 'Select courses',
                    allowClear: true,
                    data: data.map(item => ({
                        id: item.id,
                        text: item.title
                    })),
                    multiple: true,
                    search: true
                });

                // Restore selected values
                if (selected && Array.isArray(selected)) {
                    $select.val(selected).trigger('change');
                }

                // Update Livewire on change
                $select.on('change', function () {
                    let componentId = $select.data('componentid');
                    let wireModel = `lines.${index}.selected_courses`;
                    if (componentId) {
                        Livewire.find(componentId).set(wireModel, $(this).val());
                    }
                });
            });
        });
    </script>
@endpush