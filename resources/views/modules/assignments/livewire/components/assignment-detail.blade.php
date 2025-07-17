<div class="am-assignment_header">
    <div class="am-assignment_title_wrap">
        @if(!empty($assignmentDetail?->submitted_at))
            <a href="{{ route('assignments.student.student-assignments') }}"><i class="am-icon-chevron-left"></i></a>
        @else
            <a href="{{ route('assignments.student.attempt-assignment', ['id' => $assignmentDetail?->id]) }}"><i class="am-icon-chevron-left"></i></a>
        @endif
        <div class="am-assignment_title">
            <h2>{{ $assignmentDetail?->assignment?->title }}</h2>
            <div class="am-assignment_info">
                @if(!empty($assignmentDetail?->ended_at))
                    @php
                        if(empty($assignmentDetail?->submitted_at)){
                            $title = __('assignments::assignments.deadline');  
                            $date = $assignmentDetail?->ended_at;
                        }else{
                            $title = __('assignments::assignments.submitted_at');  
                            $date = $assignmentDetail?->submitted_at;
                        }
                    @endphp
                    <span><i class="am-icon-calender-day"></i>
                        {{ $title }}
                         <em>{{ date($dateFormat, strtotime($date)) }} {{ date('h:i a', strtotime($date)) }}</em>
                    </span>
                @endif
                <span> 
                    <i class="am-icon-trophy-04"></i>
                    {{ __('assignments::assignments.total_marks') }}: 
                    <em>{{ $assignmentDetail?->assignment?->total_marks }}</em>
                </span>
                <span> 
                    <i class="am-icon-shield-check"></i>
                    {{ __('assignments::assignments.passing_grade') }}: 
                    <em>{{ $assignmentDetail?->assignment?->passing_percentage }}%</em>
                </span>
            </div>
        </div>
    </div>
    <div class="am-userbox">
        <div class="am-userbox_name">
            <figure>
                @if (!empty($assignmentDetail?->assignment?->instructor?->profile?->image) && Storage::disk(getStorageDisk())->exists($assignmentDetail?->assignment?->instructor?->profile?->image))
                    <img src="{{ resizedImage($assignmentDetail?->assignment?->instructor?->profile?->image, 40, 40) }}" alt="profile-img">
                @else
                    <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 40, 40) }}" alt="profile-img">
                @endif
            </figure>
            <div>
                @if(auth()->user()->role == 'student')
                    <a href="{{ route('tutor-detail',['slug' => $assignmentDetail?->assignment?->instructor?->profile?->slug]) }}"><h5>{{ $assignmentDetail?->assignment?->instructor?->profile?->full_name }}</h5></a>
                    <span>{{ __('assignments::assignments.author') }}</span>
                @else
                    <h5>{{ $assignmentDetail?->assignment?->instructor?->profile?->full_name }}</h5>
                    <span>{{ __('assignments::assignments.author') }}</span>
                @endif
            </div>
        </div>
    </div>
</div>