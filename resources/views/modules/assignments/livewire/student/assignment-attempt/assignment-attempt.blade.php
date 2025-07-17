<div class="am-assignment_attempt-wrap">
    <div class="am-assignment_attempt">
        @php
            $bgImage = !empty(setting('_assignments.assignment_banner_image')[0]['path']) ? url(Storage::url(setting('_assignments.assignment_banner_image')[0]['path'])) : asset('modules/assignments/demo-content/assignment-detail-bg.png');
        @endphp
        <div class="am-assignment_banner" style="background: url('{{ $bgImage }}')">
            <figure>
                <x-application-logo :variation="'white'" />
            </figure>
            <h1>{{ $assignmentHeading ?? __('assignments::assignments.youre_about_to_start_your_assignment') }}</h1>
        </div>
        <div class="am-assignment_attempt_body">
            <div class="am-assignment_attempt_content">
                <div class="am-userbox">
                    <div class="am-userbox_name">
                        <figure>
                            @if (!empty($assignmentDetail?->assignment?->instructor?->profile?->image) && Storage::disk(getStorageDisk())->exists($assignmentDetail?->assignment?->instructor?->profile?->image))
                                <img src="{{ resizedImage($assignmentDetail?->assignment?->instructor?->profile?->image, 40, 40) }}" alt="profile-img">
                            @else
                                <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 40, 40) }}" alt="profile-img">
                            @endif
                        </figure>
                        @if(!empty($assignmentDetail?->assignment?->instructor?->profile?->full_name))
                            <div>
                                <h5>{{ $assignmentDetail?->assignment?->instructor?->profile?->full_name }}</h5>
                                <span>{{ __('assignments::assignments.author') }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="am-userbox_detail">
                        @if(!empty($assignmentDetail?->assignment?->title))   
                            <h3>{{ $assignmentDetail?->assignment?->title }}</h3>
                        @endif
                        <ul>
                            @if(!empty($assignmentDetail?->assignment?->total_marks))
                                <li>
                                    <i class="am-icon-trophy-04"></i>
                                    <span>
                                        {{ __('assignments::assignments.total_marks') }}
                                        <em>{{ $assignmentDetail?->assignment?->total_marks  }}</em>
                                    </span>
                                </li>
                            @endif
                            @if(!empty($assignmentDetail?->assignment?->passing_percentage))
                                <li>
                                    <i class="am-icon-shield-check"></i>
                                    <span>
                                        {{ __('assignments::assignments.passing_grade') }}
                                        <em>{{ $assignmentDetail?->assignment?->passing_percentage }}%</em>
                                    </span>
                                </li>
                            @endif
                            @if(!empty($assignmentDetail->ended_at))
                                <li>
                                    <i class="am-icon-calender-day"></i>
                                    <span>
                                        {{ __('assignments::assignments.deadline') }}
                                        <em>{{ date($dateFormat, strtotime($assignmentDetail?->ended_at)) }} {{ date('h:i a', strtotime($assignmentDetail?->ended_at)) }}</em>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
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
                @if(!empty($assignmentDetail?->assignment?->attachments))
                <div class="am-assignment_section">
                    <div class="am-assignment_section_title">
                        <h5>{{ __('assignments::assignments.attachments') }}</h5>
                    </div>
                    @foreach ($assignmentDetail?->assignment?->attachments as $attachment)
                        @if(!empty($attachment?->path) && Storage::disk(getStorageDisk())->exists($attachment?->path))
                            <div class="am-assignment_attachfile">
                                @if($attachment->type == 'image')
                                    <i class="am-icon-image"></i> 
                                @else
                                    <i class="am-icon-file-02"></i>
                                @endif
                                <div class="am-assignment_attachfile_name">
                                    <span>{{ str_replace('assignments/', '', $attachment?->path) }}</span>
                                    <em>{{ humanFilesize(Storage::disk(getStorageDisk())->size($attachment->path)) }}</em>
                                </div>
                                <span>
                                    <a href="javascript:void(0)" wire:click="download('{{ $attachment?->path }}')">
                                        <i class="am-icon-download-03"></i>
                                    </a>
                                </span>
                            </div>
                        @endif
                    @endforeach  
                </div>
                @endif
                @if(!($assignmentDetail->result == 'assigned' && $assignmentDetail?->ended_at <= now()))
                    <div class="am-assignment_attempt_btns">
                        <a href="{{ route('assignments.student.submit-assignment', ['id' => $assignmentDetail?->id]) }}" class="am-btn">
                            {{ __('assignments::assignments.submit_assignment') }}
                            <i class="am-icon-chevron-right"></i>
                        </a>
                    </div>
                @endif
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