@php
    use App\Livewire\Components\Courses;
@endphp
@if(\Nwidart\Modules\Facades\Module::has('Courses') && \Nwidart\Modules\Facades\Module::isEnabled('Courses') && Courses::hasCourses())
    <section class="am-feedback am-courses-block {{ pagesetting('select_verient') }}"> 
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="am-feedback-two_wrap">
                        @if(!empty(pagesetting('pre_heading')) || !empty(pagesetting('heading')) || !empty(pagesetting('paragraph')))
                            <div class="am-section_title am-section_title_center {{ pagesetting('section_title_variation') }}">
                                @if(!empty(pagesetting('pre_heading'))) <span>{{ pagesetting('pre_heading') }}</span> @endif 
                                @if(!empty(pagesetting('heading'))) <h2>{!! pagesetting('heading') !!}</h2> @endif
                                @if(!empty(pagesetting('paragraph'))) <p>{!! pagesetting('paragraph') !!}</p> @endif
                            </div>
                        @endif
                    </div>
                    @php
                        $sectionVerient = !empty(pagesetting('select_verient')) ? pagesetting('select_verient') : 'am-courses-block';
                        $coursesLimit   = !empty(pagesetting('courses_limit')) ? pagesetting('courses_limit') : 6;
                    @endphp
                    <livewire:components.courses :sectionVerient="$sectionVerient" :coursesLimit="$coursesLimit" />
                </div>
            </div>
        </div>
        @if(!empty(pagesetting('first_shape_image')))
            @if(!empty(pagesetting('first_shape_image')[0]['path']))
                <img src="{{url(Storage::url(pagesetting('first_shape_image')[0]['path']))}}" alt="First shape image" class="am-shapimg-1">
            @endif      
        @endif
        @if(!empty(pagesetting('second_shape_image')))
            @if(!empty(pagesetting('second_shape_image')[0]['path']))
                <img src="{{url(Storage::url(pagesetting('second_shape_image')[0]['path']))}}" alt="Second shape image" class="am-shapimg-2">
            @endif      
        @endif
        @if(!empty(pagesetting('third_shape_image')))
            @if(!empty(pagesetting('third_shape_image')[0]['path']))
                <img src="{{url(Storage::url(pagesetting('third_shape_image')[0]['path']))}}" alt="Third shape image" class="am-shapimg-3">
            @endif      
        @endif
    </section>
@endif

