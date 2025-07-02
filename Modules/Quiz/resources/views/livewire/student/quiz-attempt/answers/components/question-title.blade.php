<div class="am-quizsteps_title">
    @if(!empty($question?->settings['display_points']) && $question?->settings['display_points'])
        <span class="am-quizsteps_title_tag">{{ $question->points }} {{ $question->points > 1 ? __('quiz::quiz.points') : __('quiz::quiz.point') }}</span>
    @endif
    @if ($question?-> type == Modules\Quiz\Models\Question::TYPE_FILL_IN_BLANKS)
       <div class="am-quizsteps_heading">
          @if($question?->settings['answer_required'])
              <em>*</em>
           @endif
           <h2>
               {{ $questionNumber }}. {!! getStudentFillInBlanksText($question->title) !!}
           </h2>
       </div>
    @else
        <div class="am-quizsteps_heading">
            @if(!empty($question?->settings['display_points']) && $question?->settings['answer_required'])
                <em>*</em>
            @endif
            <h2>
                {{ $questionNumber }}. {{ $question->title }}
            </h2>
        </div>
    @endif
</div>