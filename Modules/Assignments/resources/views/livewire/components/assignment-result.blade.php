<div class="am-assignment_submitmsg">
    @if(isset($icon))
        <div class="am-deletepopup_icon @if($assignmentDetail?->result == 'in_review') confirm-icon @elseif($assignmentDetail?->result == 'fail') warning-icon @endif">
            <span>
                <i class="{{ $icon }}"></i>
            </span>
        </div>
    @endif
    @if(isset($image))
        <figure>
            <img src="{{asset($image)}}" alt="profile-img">
        </figure>
    @endif
    <h2>{{ $title }}</h2>
    <p>{{ $description }}</p>
    <a href="{{ route('assignments.student.student-assignments') }}">
        <button class="am-btn">{{ __('assignments::assignments.go_to_dashboard') }}</button>
    </a>
</div>