<div class="am-assignment_attempt-wrap am-assignment_submit-wrap">
    <div class="am-assignment_attempt">
        @include('assignments::livewire.components.assignment-detail')
        <div class="am-assignment_body">
            @if(!empty($assignmentDetail?->assignment?->description))
            <div class="am-toggle-text">
                <div class="am-addmore">
                    @php
                        $fullDescription  = $assignmentDetail?->assignment?->description;
                        $shortDescription = Str::limit(strip_tags($fullDescription), 400, preserveWords: true);
                    @endphp
                    @if (Str::length(strip_tags($fullDescription)) > 400)
                        <p class="short-description">
                            {!! $shortDescription !!}
                            <a href="javascript:void(0);" class="toggle-description">{{ __('general.show_more') }}</a>
                        </p>
                        <div class="full-description d-none">
                            {!! $fullDescription !!}
                            <a href="javascript:void(0);" class="toggle-description">{{ __('general.show_less') }}</a>
                        </div>
                    @else
                        <div class="full-description">
                            {!! $fullDescription !!}
                        </div>
                    @endif
                </div>
            </div>
            @endif
            @if($assignmentDetail->assignment?->type == 'text' || $assignmentDetail->assignment?->type == 'both')
                <div class="am-label_wrap">
                    <label class="am-label am-important">{{__('assignments::assignments.answer') }}</label>
                    <span>{{__('assignments::assignments.max_character_limit', ['limit' => $assignmentDetail->assignment?->characters_count]) }}</span>
                </div>
                <div x-init="$wire.dispatch('initSummerNote', {target: '#profile_desc', wiremodel: 'description', conetent: `{{ $description }}`, componentId: @this});" class="form-group am-custom-textarea @error('description') am-invalid @enderror">
                    <div class="am-editor-wrapper">
                        <div wire:ignore class="am-custom-editor am-custom-textarea">
                            <textarea id="profile_desc" class="form-control am-question-desc" placeholder="{{ __('assignments::assignments.type_here') }}" data-textarea="profile_desc">{{ $description ?? '' }}</textarea>
                            <span class="characters-count"></span>
                        </div>
                        <x-input-error field_name="description" />
                    </div>
                </div>
            @endIf
            @if($assignmentDetail->assignment?->type == 'document' || $assignmentDetail->assignment?->type == 'both')
                <div class="form-group @error('existingAttachments') am-invalid @enderror">
                    <div class="am-label_wrap">
                        <label class="am-label am-important">{{__('assignments::assignments.attachments') }}</label>
                        <span>{{__('assignments::assignments.max_attachments', ['limit' => $assignmentDetail->assignment?->max_file_count]) }}</span>
                    </div>
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
                        <x-input-error field_name="existingAttachments" />
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
            @endIf
        </div>
        <div class="am-assignment_footer form-group am-form-btns">
            <a href="{{ route('assignments.student.attempt-assignment', ['id' => $assignmentId]) }}" class="am-white-btn">{{__('assignments::assignments.back') }}</a>
            <button class="am-btn" wire:click="openConfirmPopup">{{ __('assignments::assignments.submit_assignment') }}</button>
        </div>
    </div>
    <div class="modal fade am-confirm-popup" id="assignment_completed_popup" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="am-modal-body">
                    <span data-bs-dismiss="modal" class="am-closepopup">
                        <i class="am-icon-multiply-01"></i>
                    </span>
                    <div class="am-deletepopup_icon warning-icon">
                        <span>
                            <i class="am-icon-exclamation-01"></i>
                        </span>
                    </div>
                    <div class="am-confirm-popup_title">
                        <h3>{{ __('assignments::assignments.publish_assignment_confirmation_title') }}</h3>
                        <p>{{ __('assignments::assignments.publish_assignment_confirmation_desc') }}</p>
                    </div>
                    <div class="am-confirm-popup_btns">
                        <a href="#" data-bs-dismiss="modal" class="am-white-btn am-btnsmall">{{ __('assignments::assignments.cancel') }}</a>
                        <a  
                        wire:loading.class="am-btn_disable" 
                        wire:target="submitAssignment"
                        wire:click="submitAssignment" class="am-btn am-btnsmall">{{ __('assignments::assignments.submit') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>  

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
<link rel="stylesheet" href="{{ asset('modules/assignments/css/main.css') }}">
    @vite([
        'public/summernote/summernote-lite.min.css',
    ])
@endpush
@push('scripts')
    <script defer src="{{ asset('summernote/summernote-lite.min.js') }}"></script>
    <script type="text/javascript">
        function initSummerNoteInput() {
            var initialContent = '';
            var initialContent = @this.get('description');
            $('.am-question-desc').summernote('destroy');
            $('.am-question-desc').summernote(summernoteConfigs('.am-question-desc'));
            $('.am-question-desc').summernote('code', initialContent);
            $(document).on('summernote.change', '.am-question-desc', function(we, contents, $editable) {             
                @this.set("description",contents, false);
            });
        }
    </script>
      <script>
        document.addEventListener("DOMContentLoaded", (event) => {
           $(document).on('click','.toggle-description', function() {
               var parentContainer = $(this).closest('.am-addmore');

               parentContainer.find('.short-description').toggleClass('d-none');
               parentContainer.find('.full-description').toggleClass('d-none');
               if (parentContainer.find('.short-description').hasClass('d-none')) {
                   $(this).text('{{ __('general.show_more') }}');
               } else {
                   $(this).text('{{ __('general.show_less') }}');
               }
           });
       });
   </script>
@endpush
