<div class="am-create-assignment">
    <div class="am-create-assignment_wrap">
        <div class="am-title_wrap">
            <div class="am-title">
                <h2>{{__('assignments::assignments.assignment_creation') }}</h2>
                <p>{{__('assignments::assignments.assignment_creation_desc') }}</p>
            </div>
        </div>
        <div class="am-create-assignment_content" id="create-assignment">
            <form class="am-themeform"
            x-data="{
                    selectedValues: {{ !empty($selectedSubjectSlots) ? json_encode($selectedSubjectSlots) : '[]' }},
                    init() {
                        this.selectedValues = Array.isArray(this.selectedValues)
                        ? this.selectedValues
                        : Object.values(this.selectedValues);
                        let selectElement = document.getElementById('user_subject_slots');
                        Livewire.dispatch('initSelect2', { target: '#selectElement' });
                        Livewire.on('slotsList', () => {
                            setTimeout(() => {
                                $('#user_subject_slots').select2().on('change', (e)=>{
                                    let textInput = jQuery(e.target).siblings('span').find('textarea');
                                    if(textInput){
                                        setTimeout(() => {
                                            textInput.val('');
                                            textInput.attr('placeholder', 'Select session');
                                        }, 50);
                                    }
                                    this.updateSelectedValues()
                                });
                            }, 50);
                        });
                        $('#user_subject_slots').select2().on('change', ()=>{
                            this.updateSelectedValues()
                        });
                    },
                    updateSelectedValues(){
                    let selectElement = document.getElementById('user_subject_slots');
                        this.selectedValues = Array.from(selectElement.selectedOptions)
                            .filter(option => option.value)
                            .map(option => ({
                                value: option.value,
                                text: option.text,
                                price: option.getAttribute('data-price')
                            })
                        );
                        @this.set('user_subject_slots', this.selectedValues.map(s => s.value));
                    },
                    removeValue(value) {
                        const selectElement = document.getElementById('user_subject_slots');
                        const optionToDeselect = Array.from(selectElement.options).find(option => option.value === value);
                        if (optionToDeselect) {
                            optionToDeselect.selected = false;
                            $(selectElement).trigger('change');
                        }
                    },
                    submitFilter() {
                        const selectElement = document.getElementById('user_subject_slots');
                        @this.set('subjectGroupIds', $(selectElement).select2('val'));
                    }
                }">
                <fieldset>
                    <div class="form-group-wrap">
                        <div class="form-group">
                            <div class="form-group-two-wrap">
                                <div class="form-control_wrap @error('title') am-invalid @enderror">
                                    <label class="am-label am-important" for="title">{{__('assignments::assignments.assignment_title') }}</label>
                                    <input class="form-control" wire:model="title"  type="text" placeholder="{{__('assignments::assignments.add_title') }}">
                                    <x-input-error field_name="title" />
                                </div>
                                <div class="form-control_wrap @error('assignment_for') am-invalid @enderror">
                                    <x-input-label class="am-important" for="assignment_for" wire:loading.class="am-disabled">{{__('assignments::assignments.assignment_for') }}</x-input-label>
                                    <span class="am-select" wire:ignore>
                                        <select class="am-select2" data-componentid="@this" id="assignment_for" data-parent="#create-assignment" data-live="true" data-wiremodel="assignment_for" data-placeholder="{{ __('assignments::assignments.select_assigment_for') }}">
                                            <option value="">{{ __('assignments::assignments.select_assigment_for') }}</option>
                                            @foreach ($assignments_for as $type)
                                                <option value="{{ $type['value'] }}" @if($assignment_for == $type['value']) selected @endif>{{ $type['label'] }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                    <x-input-error field_name="assignment_for" />
                                </div>
                            </div>
                        </div>
                    <div class="form-group @error('related_id') am-invalid @enderror" wire:loading.class="am-disabled" wire:loading.target="assignment_for">
                        <x-input-label class="am-important" for="related_id">{{ isActiveModule('Courses') ? __('assignments::assignments.select_option') :  __('assignments::assignments.select_subject')}}</x-input-label>
                        <span class="am-select" wire:ignore>
                            <select class="am-select2" data-componentid="@this" id="related_id" data-parent="#create-assignment" data-live="true" data-wiremodel="related_id" @if(isActiveModule('Courses')) disabled @endif data-placeholder="{{ __('assignments::assignments.select_option') }}">
                                <option value="">{{ __('assignments::assignments.select_option') }}</option>
                                @foreach ($related_ids as $assign)
                                    <option value="{{ $assign['id'] }}" @if($related_id == $assign['id']) selected @endif>{{ $assign['title'] }}</option>
                                @endforeach
                            </select>
                        </span>
                        <x-input-error field_name="related_id" />
                    </div>
                    @if($assignment_for == 'App\Models\UserSubjectGroupSubject')
                        <div class="form-group am-knowlanguages @error('user_subject_slots') am-invalid @enderror" wire:loading.class="am-disabled" wire:loading.target="related_id">
                            <x-input-label for="Slots" class="am-important" :value="__('assignments::assignments.select_session')" />
                            <div class="form-group-two-wrap am-nativelang">
                                <div id="user_slot" wire:ignore>
                                    <span class="am-select am-multiple-select">
                                        <select data-componentid="@this" data-disable_onchange="true" data-parent="#create-assignement" class="slots" data-hide_search_opt="true" id="user_subject_slots" @if(!$related_id) disabled @endif data-wiremodel="user_subject_slots" multiple data-placeholder="{{ __('assignments::assignments.select_session') }}">
                                            <option value="" >{{ __('assignments::assignments.select_session') }}</option>
                                            @foreach($slots as $slot)
                                                <option @if(!empty($slot['selected'])) selected @endif value="{{ $slot['id'] }}">{{ $slot['text']  }}</option>      
                                            @endforeach
                                        </select>
                                        <template x-if="selectedValues.length > 0">
                                            <ul class="am-subject-tag-list">
                                                <template x-for="(subject, index) in selectedValues">
                                                    <li>
                                                        <a href="javascript:void(0)" class="am-subject-tag" @click="removeValue(subject.value)">
                                                            <span x-text="`${subject.text}`"></span>
                                                            <i class="am-icon-multiply-02"></i>
                                                        </a>
                                                    </li>
                                                </template>
                                            </ul>
                                        </template>
                                    </span>
                                </div>
                                <x-input-error field_name="user_subject_slots" />
                            </div>
                        </div>                        
                    @endif
                     
                    <div class="form-group">
                        <div class="form-group-two-wrap">
                            <div class="form-control_wrap @error('assignment_type') am-invalid @enderror">
                                <label class="am-label am-important" for="subject">{{__('assignments::assignments.type') }}</label>
                                <span class="am-select" wire:ignore>
                                    <span class="am-select">
                                        <select class="am-select2" data-placeholder="{{ __('assignments::assignments.select_assignment_type') }}" data-componentid="@this" id="assignment_type" data-live="true" data-searchable="false" data-wiremodel="assignment_type">
                                            <option value="" >{{ __('assignments::assignments.select_assignment_type') }}</option>
                                            @foreach($assignmentTypes as $type)
                                                <option value="{{ $type }}" {{$assignment_type == $type ? 'selected' : ''}}  >{{ __('assignments::assignments.'.$type) }}</option>
                                            @endforeach
                                        </select>
                                    </span>
                                </span>
                                <x-input-error field_name="assignment_type" />
                            </div>
                            <div class="form-control_wrap @error('total_marks') am-invalid @enderror">
                                <label class="am-label am-important" for="title">{{__('assignments::assignments.total_marks') }}</label>
                                <input class="form-control" min="0" wire:model="total_marks" name="email" type="number" placeholder="100">
                                <x-input-error field_name="total_marks" />
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <div class="form-group-two-wrap">
                            <div class="form-control_wrap @error('passing_grade') am-invalid @enderror">
                                <label class="am-label am-important" for="title">
                                    {{__('assignments::assignments.passing_grade') }}
                                    <div class="am-custom-tooltip am-infoicon">
                                        <span class="am-tooltip-text">
                                            <strong>{{ __('assignments::assignments.passing_grade') }}</strong><br>
                                            <span>{{ __('assignments::assignments.passing_grade_tooltip') }}</span>
                                        </span>
                                        <i class="am-icon-exclamation-01"></i>
                                    </div>
                                </label>
                                <div class="am-input-field @error('passing_grade') am-invalid @enderror">
                                    <input class="form-control" wire:model="passing_grade" name="percentage" type="number" placeholder="85">
                                    <span class="am-input-field_icon">
                                        %
                                    </span>
                                </div>
                                <x-input-error field_name="passing_grade" />
                            </div>
                            <div class="form-control_wrap @error('charactersCount') am-invalid @enderror">
                                <label class="am-label {{$assignment_type == 'text' || $assignment_type == 'both' ? 'am-important' : ''}}" for="title">{{__('assignments::assignments.character_limit') }}</label>
                                <input class="form-control" wire:model="charactersCount" name="character_limit" type="number" placeholder="500">
                                <x-input-error field_name="charactersCount" />
                            </div>
                        </div>
                    </div>
                    @if($assignment_type == 'document' || $assignment_type == 'both')
                        <div class="form-group">
                            <div class="form-group-two-wrap">
                                <div class="form-control_wrap @error('max_file_upload') am-invalid @enderror">
                                    <label class="am-label am-important" for="subject">{{__('assignments::assignments.max_files_to_upload') }}</label>
                                    <span class="am-select">
                                        <span class="am-select" wire:ignore>
                                            <select class="am-select2" data-componentid="@this" id="max_file_upload" data-live="true" data-wiremodel="max_file_upload" data-placeholder="{{ __('assignments::assignments.select_option') }}">
                                                <option value="">{{ __('assignments::assignments.select_option') }}</option>
                                                @foreach($filesCountList as $count)
                                                <option value="{{$count}}" @if($max_file_upload == $count) selected @endif >{{ $count }}</option>
                                                @endforeach
                                            </select>
                                        </span>
                                        <x-input-error field_name="max_file_upload" />
                                    </span>
                                </div>
                                <div class="form-control_wrap @error('max_upload_file_size') am-invalid @enderror">
                                    <label class="am-label" for="title">{{__('assignments::assignments.max_upload_file_size') }}</label>
                                    <div class="am-input-field">
                                        <input class="form-control" wire:model="max_upload_file_size" name="percentage" type="text" placeholder="10">
                                        <span class="am-input-field_icon">
                                            {{__('assignments::assignments.mb') }}
                                        </span>
                                    </div>
                                    <x-input-error field_name="max_upload_file_size" />
                                </div>
                            </div>
                        </div>
                    @endIf
                    <div class="form-group">
                        <div class="form-group-two-wrap form-group-deadline">
                            <div class="form-control_wrap @error('dueDays') am-invalid @enderror">
                                <label class="am-label am-important" for="title">
                                    {{__('assignments::assignments.deadline') }}
                                    <div class="am-custom-tooltip am-infoicon">
                                        <span class="am-tooltip-text">
                                            <strong>{{ __('assignments::assignments.deadline') }}</strong><br>
                                            <span>{{ __('assignments::assignments.deadline_tooltip') }}</span>
                                        </span>
                                        <i class="am-icon-exclamation-01"></i>
                                    </div>
                                </label>
                                <x-text-input wire:model.live="dueDays" placeholder="{{__('general.no_of_days')}}" type="number" />
                                <x-input-error field_name="dueDays" />
                            </div>
                            <div class="form-control_wrap @error('dueTime') am-invalid @enderror">
                                <label class="am-label am-time-lable">Time</label>
                                <div class="am-input-field">
                                    <x-text-input class="flat-time" id="dof" data-format="F-d-Y" wire:model="dueTime" placeholder="{{__('general.time')}}" type="text" id="datepicker" data-enable_time="true"  autofocus autocomplete="name" />
                                    <span class="am-input-field_icon">
                                        <i class="am-icon-time"></i>
                                    </span>
                                </div>
                                <x-input-error field_name="dueTime" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group @error('image') am-invalid @enderror">
                        <label class="am-label">{{__('assignments::assignments.attachments') }}</label>
                        <div class="am-uploadoption" x-data="{isUploading:false}" wire:key="uploading-image-{{ time() }}">
                            <div class="tk-draganddrop" wire:loading.class="am-uploading" wire:target="attachments,existingAttachments"
                                x-bind:class="{ 'am-dragfile' : isDragging, 'am-uploading' : isUploading }" 
                                x-on:drop.prevent="isUploading = true; isDragging = false" wire:drop.prevent="$uploadMultiple('attachments', $event.dataTransfer.files)">
                                <x-text-input name="file" type="file" id="at_upload_image" multiple x-ref="attachments"
                                    accept="{{ !empty($allowedExtensions) ? join(',', array_map(function($ex){return('.'.$ex);}, $allowedExtensions)) : '*' }}"
                                    x-on:change="isUploading = true; $wire.uploadMultiple('attachments', $refs.attachments.files)" />
                                <label for="at_upload_image" class="am-uploadfile">
                                    <span class="am-dropfileshadow">
                                        <i class="am-icon-plus-02"></i>
                                        <span class="am-uploadiconanimation">
                                            <i class="am-icon-upload-03"></i>
                                        </span>
                                        {{ __('assignments::assignments.drop_file_here') }}
                                    </span>
                                    <em>
                                        <i class="am-icon-export-03"></i>
                                    </em>
                                    <span>{!! __('assignments::assignments.upload_file_text') !!} 
                                        <em>@if (!empty($allowedExtensions))  <em>{{ implode(",",$allowedExtensions) }} (max. {{ $maxUploadSize }} mb)</em>@endif</em>
                                    </span>
                                    <svg class="am-border-svg "><rect width="100%" height="100%" rx="12"></rect></svg>
                                </label>
                            </div>
                            <x-input-error field_name="attachments.*" />
                            @if(!empty($existingAttachments))
                                <div class="am-assignment_section">
                                    @foreach($existingAttachments as $key => $single)
                                        <div class="am-assignment_attachfile">
                                            @php
                                               if ($single instanceof Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                                                    $mimeType = $single->getMimeType() ?? '';
                                                    $fileName = $single->getClientOriginalName();
                                                    $fileSize = $single->getSize();
                                                } else {
                                                    $mimeType = $single->type ?? '';
                                                    $fileName = $single->name ?? 'Unknown File';
                                                    $fileSize = Storage::disk(getStorageDisk())->size($single->path) ?? 0;
                                                }
                                            @endphp
                                            @if(Str::startsWith($mimeType, 'image'))
                                                <i class="am-icon-image"></i> 
                                            @else
                                                <i class="am-icon-file-02"></i>
                                            @endif
                                            <div class="am-assignment_attachfile_name">
                                                <span>{{ $fileName }}</span>
                                                <em>{{ humanFilesize($fileSize) }} </em>
                                            </div>
                                            <span wire:click.prevent="removeAttachment('{{ $key }}')">
                                                <i class="am-icon-trash-02"></i>
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>

                    <div x-init="$wire.dispatch('initSummerNote', {target: '#profile_desc', wiremodel: 'description', conetent: `{{ $description }}`, componentId: @this});" class="form-group am-custom-textarea @error('description') am-invalid @enderror">
                        <div class="am-label-wrap">
                            <label class="am-label am-important">{{__('assignments::assignments.description') }}</label>
                            @if(setting('_ai_writer_settings.enable_on_assignments_settings') == '1')
                                <button type="button" class="am-ai-btn" data-bs-toggle="modal" data-bs-target="#aiModal"  data-prompt-type="assignments"  data-target-selector="#profile_desc" data-target-summernote="true">
                                    <img src="{{ asset('images/ai-icon.svg') }}" alt="AI">
                                    {{ __('general.write_with_ai') }}
                                </button>
                            @endif
                        </div>
                        <div class="am-editor-wrapper">
                            <div wire:ignore class="am-custom-editor am-custom-textarea">
                                <textarea id="profile_desc" class="form-control am-question-desc" placeholder="{{ __('assignments::assignments.type_here') }}" data-textarea="profile_desc">{{ $description ?? '' }}</textarea>
                                <span class="characters-count"></span>
                            </div>
                            <x-input-error field_name="charactersCount" />
                        </div>
                    </div>
                    
                    <div class="form-group am-form-btns">
                        <a href="{{ route('assignments.tutor.assignments-list') }}" class="am-white-btn">{{__('assignments::assignments.back') }}</a>
                        <button type="button" class="am-btn"   
                         wire:loading.class="am-btn_disable" 
                         wire:target="saveAssignment"
                         wire:click="saveAssignment">{{__('assignments::assignments.save_and_update') }}</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>


@push('styles')
<link rel="stylesheet" href="{{ asset('modules/assignments/css/main.css') }}">
    @vite([
        'public/summernote/summernote-lite.min.css',
        'public/css/flatpicker.css'
    ])
@endpush

@push('scripts')
    <script defer src="{{ asset('js/flatpicker.js') }}"></script>
    <script defer src="{{ asset('js/flatpicker-month-year-plugin.js') }}"></script>
    <script defer src="{{ asset('summernote/summernote-lite.min.js') }}"></script>
    <script>
        document.addEventListener('livewire:navigated', function() {
            setTimeout(() => {
                initializeTimePicker()
            }, 500);
        });
    </script>
    <script type="text/javascript">
        window.addEventListener('editAssignment', (event) => {
            const {related_type, option_list, session_slots} = event.detail.eventData;
            Livewire.dispatch('initSelect2', { target: '.am-select2', timeOut: 150 });
            setTimeout(() => {
                initOptionList(option_list);
                if(related_type === '{{ UserSubjectGroupSubject::class }}'){
                    initOption(session_slots);
                }
            }, 300);

        });

        window.addEventListener('assignmentValuesUpdated', (event) => {            
            let { options, reset } = event.detail;
            initOptionList(options);
            if(reset) {
                jQuery('#related_id').val('').trigger('change');
            }
        });

        window.addEventListener('addSlotsOptions', (event) => {
            let {options = []} = event.detail;
            const listItem = Object?.values(options) ?? []
            initOption(listItem);
        });

        function initOption (optionList) {
            let $select = jQuery('#user_subject_slots');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy').empty();
            }

            $select.select2({ 
                data: [{
                    id: '', 
                    text: 'Select an option'
                }, ...optionList],
                theme: 'default',
                disabled: false
            });

            setTimeout(() => {
                const event = new CustomEvent('slotsList', { detail: { } });
                document.dispatchEvent(event);
            }, 1000);
        }

        function initOptionList (options) {
            let $select = jQuery('#related_id');
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.select2('destroy').empty();
            }
            $select.select2({ 
                data: [{
                    id: '', 
                    text: 'Select an option'
                }, ...options],
                theme: 'default',
                dropdownParent: jQuery('#create-assignment'),
                disabled: false
            });
        }

        function initSummerNoteInput() {
            var initialContent = '';
            var initialContent = @this.get('form.description');
            $('.am-question-desc').summernote('destroy');
            $('.am-question-desc').summernote(summernoteConfigs('.am-question-desc'));
            $('.am-question-desc').summernote('code', initialContent);
            $(document).on('summernote.change', '.am-question-desc', function(we, contents, $editable) {             
                @this.set("form.description",contents, false);
            });
        }

        document.addEventListener('DOMContentLoaded', function( ) {
            jQuery(document).ready(function() {
                setTimeout(function() {
                    $(document).on('change', "#subject_list", function(e) {
                        @this.set('subject_list', $(this).select2('val'));
                    });
                }, 50);
            });
        });
    </script>
@endpush