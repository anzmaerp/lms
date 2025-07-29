<div class="container my-5">
    <form action="{{ route('search') }}" method="GET" class="w-100">
        <div class="bg-white rounded-4 shadow p-4 d-flex flex-column gap-3">

            <!-- النوع + زر البحث -->
            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center">
                <select id="contentType" name="type" class="form-select border-0 rounded-pill w-auto">
                    <option selected disabled>اختر</option>
                    <option value="courses">دورة</option>
                    <option value="tutors">محاضرة أونلاين</option>
                </select>

                <button type="submit" class="btn btn-primary rounded-pill flex-grow-1 px-4">
                    🔍 بحث
                </button>
            </div>

            <!-- input مشترك -->
            <div class="d-flex justify-content-center">
                <input type="text" name="search_name" class="form-control border-0 rounded-pill w-100 w-md-75"
                    placeholder="ابحث باسم الدورة أو المحاضر">
            </div>

            <!-- الدورة -->
            <div id="courseFields" class="d-none">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center">
                    <select name="course_category" class="form-select border-0 rounded-pill w-auto">
                        <option selected disabled>الفئة</option>
                        <option value="beginner">مبتدئ</option>
                        <option value="intermediate">متوسط</option>
                        <option value="advanced">متقدم</option>
                    </select>
                    <select name="course_level" class="form-select border-0 rounded-pill w-auto">
                        <option selected disabled>المستوى</option>
                        <option value="level1">المستوى 1</option>
                        <option value="level2">المستوى 2</option>
                    </select>
                    <select name="course_language" class="form-select border-0 rounded-pill w-auto">
                        <option selected disabled>اللغة</option>
                        <option value="ar">العربية</option>
                        <option value="en">الإنجليزية</option>
                    </select>
                </div>
            </div>

            <!-- المحاضرة -->
            <div id="tutorFields" class="d-none">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-center">
                    <select id="group_id" class="am-select2" data-searchable="true" data-class="am-filter-dropdown"
                        data-placeholder="{{ __('subject.choose_subject_group') }}">
                        <option> </option>
                        @foreach ($subjectGroups as $group)
                            <option value="{{ $group->id }}"
                                {{ $group->id == ($filters['group_id'] ?? '') ? 'selected' : '' }}>
                                {{ $group->name }}</option>
                        @endforeach
                    </select>
                    <select id="subject_id" class="am-select2" multiple data-searchable="true"
                        data-class="am-filter-dropdown" data-placeholder="{{ __('subject.choose_subject_label') }}">
                        <option> </option>
                        @foreach ($subjects as $subject)
                            <option value="{{ $subject->id }}"
                                {{ in_array($subject->id, $filters['subject_id'] ?? []) ? 'selected' : '' }}>
                                {{ $subject?->name }}</option>
                        @endforeach
                    </select>
                    <div class="am-searchfilter_item">
                        <span class="am-searchfilter_title">{{ __('calendar.max_price') }}</span>
                        <input type="text" placeholder="{{ getCurrencySymbol() }}0.00" class="form-control"
                            id="max_price" value="{!! !empty($filters['max_price']) ? getCurrencySymbol() . $filters['max_price'] : '' !!}">
                    </div>
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

        typeSelect.addEventListener('change', function() {
            if (this.value === 'courses') {
                courseFields.classList.remove('d-none');
                tutorFields.classList.add('d-none');
            } else if (this.value === 'tutors') {
                tutorFields.classList.remove('d-none');
                courseFields.classList.add('d-none');
            } else {
                courseFields.classList.add('d-none');
                tutorFields.classList.add('d-none');
            }
        });
    });
</script>
