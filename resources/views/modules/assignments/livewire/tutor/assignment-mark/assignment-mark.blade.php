<div class="am-assignment am-assignment_attempt-wrap am-assignment_submit-wrap">
    <div class="am-assignment_attempt">
        <div class="am-assignment_header">
            <div class="am-assignment_title_wrap">
                <a href="{{ route('assignments.tutor.submissions-assignments-list', ['assignmentId' => $submittedAssignment->assignment_id]) }}"><i class="am-icon-chevron-left"></i></a>
                <div class="am-assignment_title">
                    <h2>{{ $submittedAssignment?->assignment?->title }}</h2>
                    <div class="am-assignment_info">
                        @if(!empty($submittedAssignment?->ended_at))
                            <span>{{ __('assignments::assignments.submitted_at') }} <em>{{ date((setting('_general.date_format') ?? 'M d, Y'), strtotime($submittedAssignment?->ended_at)) }} {{ date('h:i a', strtotime($submittedAssignment?->ended_at)) }}</em></span>
                        @endif
                        @if($submittedAssignment->result != 'in_review')
                            <span> {{ __('assignments::assignments.obtained_marks') }}: <em>{{ $submittedAssignment->marks_awarded }}</em></span>
                        @else
                            <span> {{ __('assignments::assignments.passing_grade') }}: <em>{{ $submittedAssignment?->assignment?->passing_percentage }}%</em></span>
                        @endif
                        <span>   {{ __('assignments::assignments.total_marks') }}: <em>{{ $submittedAssignment?->assignment?->total_marks }}</em></span>
                    </div>
                </div>
            </div>
            <div class="am-userbox">
                <div class="am-userbox_name">
                    <figure>
                        @if (!empty($submittedAssignment?->student?->profile?->image) && Storage::disk(getStorageDisk())->exists($submittedAssignment?->student?->profile?->image))
                            <img src="{{ resizedImage($submittedAssignment?->student?->profile?->image, 40, 40) }}" alt="profile-img">
                        @else
                            <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 40, 40) }}" alt="profile-img">
                        @endif
                    </figure>
                    <div>
                        <h5>{{ $submittedAssignment?->student?->profile?->full_name }}</h5>
                        <span>{{ __('assignments::assignments.student') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="am-assignment_body">
            @if(!empty($submittedAssignment?->assignment?->description))
                <div class="am-assignment_section">
                    <div class="am-assignment_section_title">
                        <h5>{{ __('assignments::assignments.description') }}</h5>
                    </div>
                    <div class="am-assignment_desc am-assignment_desc_vtwo">
                        <div class="am-addmore">
                            @php
                                $fullDescription  = $submittedAssignment?->assignment?->description;
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
                </div>
            @endif
            @if(!empty($submittedAssignment->submission_text))
                <div class="am-assignment_section">
                    <div class="am-assignment_section_title">
                        <h5>{{ __('assignments::assignments.answer') }}</h5>
                        <div class="am-assignment_section_points-wrap">
                            @if(in_array($submittedAssignment->assignment->type , ['text','both']))
                                <div class="am-assignment_section_points">
                                    <span><input type="number" min="0" max="{{ $submittedAssignment?->assignment?->total_marks }}" wire:model="marksAwarded"></span>/{{ $submittedAssignment?->assignment?->total_marks }}
                                </div>
                            @endif
                            @error('marksAwarded')
                                <span class="am-error-msg">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="am-assignment_desc">
                       {!! $submittedAssignment->submission_text !!}
                    </div>
                </div>
            @endif
            @if(!empty($submittedAssignment->attachments))
            <div class="am-assignment_section">
                <div class="am-assignment_section_title">
                    <h5>{{ __('assignments::assignments.attachments') }}</h5>
                    @if(in_array($submittedAssignment->assignment->type , ['document']))
                        <div class="am-assignment_section_points">
                            <span><input type="number" min="0" max="{{ $submittedAssignment?->assignment?->total_marks }}" wire:model="marksAwarded"></span>/{{ $submittedAssignment?->assignment?->total_marks }}
                        </div>
                    @endif
                    @error('marksAwarded')
                        <span>{{ $message }}</span>
                    @enderror
                </div>
                @foreach($submittedAssignment->attachments as $attachment)
                    <div class="am-assignment_attachfile">
                        @if(Str::startsWith($attachment->type, 'image'))
                            <i class="am-icon-image"></i>
                        @else
                            <i class="am-icon-file-02"></i>
                        @endif
                        <div class="am-assignment_attachfile_name">
                            <span>{{ $attachment->name }}</span>
                            <em>{{ humanFilesize(Storage::disk(getStorageDisk())->size($attachment->path)) }}</em>
                        </div>
                        <span wire:click.prevent="downloadAttachment('{{ $attachment->path }}')">
                            <i class="am-icon-download-03"></i>
                        </span>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
        @if($submittedAssignment->result == 'in_review')
            <div class="am-assignment_footer">
                <button class="am-btn" wire:click="proceedResult">{{ __('assignments::assignments.submit_result') }}</button>
            </div>
        @endif
    </div>
    <div class="modal fade am-confirm-popup" id="result_completed_popup" wire:ignore.self>
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
                        <h3>{{ __('assignments::assignments.confrim_submit_result_title') }}</h3>
                        <p>{{ __('assignments::assignments.confrim_submit_result_desc') }}</p>
                    </div>
                    <div class="am-confirm-popup_btns">
                        <a href="#" data-bs-dismiss="modal" class="am-white-btn am-btnsmall">{{ __('assignments::assignments.cancel') }}</a>
                        <a  
                        wire:loading.class="am-btn_disable" 
                        wire:target="submitResult"
                        wire:click="submitResult" class="am-btn am-btnsmall">{{ __('assignments::assignments.submit_result') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/assignments/css/main.css') }}">
@endpush
@push('scripts')
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