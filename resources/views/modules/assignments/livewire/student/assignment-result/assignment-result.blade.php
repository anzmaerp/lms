<div class="am-assignment_attempt-wrap am-assignment_submit-wrap">
    <div class="am-assignment_attempt">
        @include('assignments::livewire.components.assignment-detail')
        <div class="am-assignment_body">
            @if($assignmentDetail?->result == 'in_review')
                @include('assignments::livewire.components.assignment-result', ['icon' => 'am-icon-check-circle06','title' => __('assignments::assignments.assignment_submitted_successfully'), 'description' => __('assignments::assignments.assignment_successfully_dec')])
            @elseif($assignmentDetail?->result == 'pass')
                @include('assignments::livewire.components.assignment-result', ['image' => 'modules/assignments/images/congrates.png','title' => __('assignments::assignments.congratulations_passed'), 'description' => __('assignments::assignments.great_job')])
            @elseif($assignmentDetail?->result == 'fail')
                @include('assignments::livewire.components.assignment-result', ['icon' => 'am-icon-exclamation-01','title' => __('assignments::assignments.keep_learning'), 'description' => __('assignments::assignments.keep_learning_dec')])
            @endif
            @if(!empty($assignmentDetail->submission_text) && $assignmentDetail?->result != 'in_review')
                <div class="am-assignment_section">
                    <div class="am-assignment_section_title">
                        <h5>{{ __('assignments::assignments.answer') }}</h5>
                    </div>
                    <div class="am-assignment_desc">
                       {!! $assignmentDetail->submission_text !!}
                    </div>
                </div>
            @endIf
            @if($assignmentDetail?->attachments->isNotEmpty() && $assignmentDetail?->result != 'in_review')
                <div class="am-assignment_section">
                    <div class="am-assignment_section_title">
                        <h5>{{ __('assignments::assignments.attachments') }}</h5>
                    </div>
                    @foreach($assignmentDetail?->attachments as $attachment)
                        <div class="am-assignment_attachfile">
                            @if($attachment->type == 'image')
                                <i class="am-icon-image"></i> 
                            @else
                                <i class="am-icon-file-02"></i>
                            @endif
                            <div class="am-assignment_attachfile_name">
                                <span>{{ str_replace('assignments/', '', $attachment?->name) }}</span>
                                <em>{{ humanFilesize(Storage::disk(getStorageDisk())->size($attachment->path)) }}</em>
                            </div>
                            <span wire:click="download('{{ $attachment?->path }}')">
                                <i class="am-icon-download-03"></i>
                            </span>
                        </div>
                    @endforeach
                </div>
            @endIf
        </div>
    </div>
</div>
@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/assignments/css/main.css') }}">
@endpush