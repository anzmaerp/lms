@php
    use App\Services\SubjectService;
    use Modules\Courses\Services\CourseService;
    use Illuminate\Support\Facades\Auth;

    $service = new SubjectService(Auth::user());
    $subjectGroups = $service->getSubjectGroups();
    $subjects = $service->getSubjects();

    $courseService = new CourseService();
    $categories = $courseService->getCategories();
    $levels = $courseService->getLevels();
    $languages = $courseService->getLanguages();
@endphp

<div class="container my-5">
    <form action="{{ route('search') }}" method="GET" class="w-100">
        <div class="bg-white rounded-4 shadow p-4 d-flex flex-column gap-4">

            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center">
                <select id="contentType" name="type" class="form-select border-0 rounded-pill px-4 w-auto" required>
                    <option selected disabled>{{__('settings.select_option')}}</option>
                    <option value="courses">{{__('courses::courses.courses')}}</option>
                    <option value="tutors">{{ __('courses::courses.lecture')}}</option>
                </select>

                <button type="submit" class="btn btn-primary rounded-pill px-5">
                    🔍{{ __('general.search') }}
                </button>
            </div>

            <div class="text-center">
                <input type="text" name="filters[keyword]"
                    class="form-control border-0 rounded-pill px-4 py-2 w-100 w-md-75 mx-auto"
                    placeholder="{{__('general.search_by_keyword')}}">
            </div>

            <div id="courseFields" class="d-none">
                <div class="am-searchfilter_item">
                    <span class="am-searchfilter_title">{{ __('courses::courses.category') }}</span>
                    <select name="searchCategories[]" class="am-select2" multiple>
                        <option> </option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="am-searchfilter_item">
                    <span class="am-searchfilter_title">{{__('courses::courses.level') }}</span>
                    <select name="filters[levels][]" class="am-select2" multiple>
                        <option> </option>
                        @foreach ($levels as $level)
                            <option value="{{ $level['id'] }}">{{ $level['name'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="am-searchfilter_item">
                    <span class="am-searchfilter_title">{{__('courses::courses.language') }}</span>
                    <select name="searchLanguages[]" class="am-select2" multiple>
                        <option> </option>
                        @foreach ($languages as $language)
                            <option value="{{ $language->id }}">{{ $language->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="tutorFields" class="d-none">
                <div class="am-searchfilter_item">
                    <select name="group_id">
                        <option> </option>
                        @foreach ($subjectGroups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="am-searchfilter_item">
                        <select name="subject_id" id="subject_id" class="am-select2" multiple data-searchable="true"
                            data-class="am-filter-dropdown" data-placeholder="{{ __('subject.choose_subject_label') }}">
                            <option> </option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}"
                                    {{ in_array($subject->id, $filters['subject_id'] ?? []) ? 'selected' : '' }}>
                                    {{ $subject?->name }}
                                </option>
                            @endforeach
                        </select>
                </div>
                <div class="am-searchfilter_item">
                    <span class="am-searchfilter_title">{{ __('calendar.max_price') }}</span>
                <input type="number" step="0.01" name="max_price" placeholder="{{ getCurrencySymbol() }}0.00">
                </div>
            </div>

        </div>
    </form>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const typeSelect = document.getElementById('contentType');
        const courseFields = document.getElementById('courseFields');
        const tutorFields = document.getElementById('tutorFields');

        const toggleFields = () => {
            const type = typeSelect.value;
            courseFields.classList.toggle('d-none', type !== 'courses');
            tutorFields.classList.toggle('d-none', type !== 'tutors');
        };
        typeSelect.addEventListener('change', toggleFields);
        toggleFields();
    });
</script>
