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

            <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between">
                <select id="contentType" name="type" class="form-select border-0 rounded-pill px-4 w-auto" required>
                    <option selected disabled>{{__('settings.select_option')}}</option>
                    <option value="courses">{{__('courses::courses.courses')}}</option>
                    <option value="tutors">{{ __('courses::courses.lecture')}}</option>
                </select>
                <div id="containerSearch"  class="text-center" style=" border: 1px solid #6712c7; border-radius: 20px;">
                    <input type="text" id="searchInput" name="filters[keyword]" class="form-control border-0 rounded-pill px-4 py-2 w-100 w-md-75 mx-auto" placeholder="{{__('general.search_by_keyword')}}">
                </div>
                <button type="submit" class="btn btn-primary rounded-pill px-5">
                    🔍{{ __('general.search') }}
                </button>
            </div>


            <div id="courseFields" class="row g-3 d-none">

                <div class="col-12 col-md-4">
                    <div class="am-searchfilter_item">
                        <div class="dropdown w-100 d-flex justify-content-center align-items-center">
                            <input type="text" name="searchCategories[]" id="selectedCategory" class="d-none" value="">
                            <button id="dropdownButton" class="btn text-end w-100 btn-primary dropdown-toggle d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{__('courses::courses.category')}}
                            </button>
                            <ul style="height: 160px; overflow-y: overlay; width: 100%;" class="dropdown-menu z-3 w-100">
                                @foreach ($categories as $category)
                                <li style="font-size:14px;" class="list-unstyled dropdownc-item2 text-end">
                                    <a class="dropdown-item" href="#" data-value="{{ $category->id }}">{{ $category->name }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>


                <div class="col-12 col-md-4">
                    <div class="am-searchfilter_item">
                        <div class="dropdown w-100 d-flex justify-content-center align-items-center">
                            <input type="text" name="filters[levels][]" id="selectedLevel" class="d-none" value="">
                            <button style="background-color: unset; border: 1px solid #600cc3; color: black;" class="btn text-end w-100 btn-primary dropdown-toggle d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{__('courses::courses.level')}}
                            </button>
                            <ul style="height: 160px; overflow-y: overlay; width: 100%;" class="dropdown-menu z-3 w-100">
                                @foreach ($levels as $level)
                                <li style="font-size:16px;" class="list-unstyled text-end dropdownc-item3">
                                    <a class="dropdown-item2 text-dark" href="#" data-value="{{ $level['id'] }}">{{ $level['name'] }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>


                <div class="col-12 col-md-4">
                    <div class="am-searchfilter_item">
                        <div class="dropdown w-100 d-flex justify-content-center align-items-center">
                            <input type="text" name="searchLanguages[]" id="selectedLanguage" class="d-none" value="">
                            <button style="background-color: unset; border: 1px solid #600cc3; color: black;" class="btn text-end w-100 btn-primary dropdown-toggle d-flex align-items-center justify-content-between" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{__('courses::courses.language')}}
                            </button>
                            <ul style="height: 160px; overflow-y: overlay; width: 100%;" class="dropdown-menu z-3 w-100">
                                @foreach ($languages as $language)
                                <li style="font-size:14px;" class="list-unstyled text-end dropdownc-item3">
                                    <a class="dropdown-item3 text-dark" href="#" data-value="{{ $language->id }}">{{ $language->name }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div id="tutorFields" class="row g-3 d-none">
                <!-- المجموعة -->
                <div class="col-12 col-md-4">
                    <div class="am-searchfilter_item">
                        <div class="dropdown w-100 d-flex justify-content-center align-items-center">
                            <input type="text" name="group_id" id="selectedGroup" class="d-none" value="">
                            <button id="dropdownButtonGroup" class="btn text-end w-100 btn-primary dropdown-toggle d-flex justify-content-between align-items-center" style="background-color: unset; border: 1px solid #600cc3; color: black;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('courses::courses.group') }}
                            </button>
                            <ul class="dropdown-menu z-3 w-100" style="height: 160px; overflow-y: overlay;">
                                @foreach ($subjectGroups as $group)
                                <li style="font-size:16px;" class="list-unstyled text-end dropdown-item-group5">
                                    <a class="dropdown-item-group" href="#" data-value="{{ $group->id }}">{{ $group->name }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- المادة -->
                <div class="col-12 col-md-4">
                    <div class="am-searchfilter_item">
                        <div class="dropdown w-100 d-flex justify-content-center align-items-center">
                            <input type="text" name="subject_id" id="selectedSubject" class="d-none" value="">
                            <button id="dropdownButtonSubject" class="btn text-end w-100 btn-primary dropdown-toggle d-flex justify-content-between align-items-center" style="background-color: unset; border: 1px solid #600cc3; color: black;" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ __('subject.choose_subject_label') }}
                            </button>
                            <ul class="dropdown-menu z-3 text-end w-100" style="height: 160px; overflow-y: overlay;">
                                @foreach ($subjects as $subject)
                                <li style="font-size:14px;" class="list-unstyled dropdown-item-subject5">
                                    <a class="dropdown-item-subject" href="#" data-value="{{ $subject->id }}">{{ $subject->name }}</a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- السعر -->
                <div class="col-12 col-md-4">
                    <div class="am-searchfilter_item">
                        <span class="am-searchfilter_title d-block mb-2">{{ __('calendar.max_price') }}</span>
                        <input type="number" step="0.01" name="max_price" class="form-control" placeholder="{{ getCurrencySymbol() }}0.00">
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

        const toggleFields = () => {
            const type = typeSelect.value;
            courseFields.classList.toggle('d-none', type !== 'courses');
            tutorFields.classList.toggle('d-none', type !== 'tutors');
        };
        typeSelect.addEventListener('change', toggleFields);
        toggleFields();
    });

</script>
@push('scripts')
<script>
    $(document).ready(function() {
        $('.dropdown-item').on('click', function(e) {
            e.preventDefault(); // يمنع التنقل

            let selectedText = $(this).text(); // النص المعروض
            let selectedValue = $(this).data('value'); // القيمة الحقيقية

            $('#dropdownButton').text(selectedText); // تغيير نص الزر
            $('#selectedCategory').val(selectedValue); // حفظ القيمة في input المخفي
        });
    });

    $(document).ready(function() {
        $('.dropdown-item2').on('click', function(e) {
            e.preventDefault(); // يمنع التنقل

            let selectedText = $(this).text(); // النص الظاهر
            let selectedValue = $(this).data('value'); // القيمة الفعلية

            $('#dropdownButton2').text(selectedText); // تغيير نص الزر
            $('#selectedLevel').val(selectedValue); // حفظ القيمة في input المخفي
        });
    });

    $(document).ready(function() {
        $('.dropdown-item3').on('click', function(e) {
            e.preventDefault();

            let selectedText = $(this).text();
            let selectedValue = $(this).data('value');

            $('#dropdownButton3').text(selectedText);
            $('#selectedLanguage').val(selectedValue);
        });
    });

    $(document).ready(function() {
        $('.dropdown-item-group').on('click', function(e) {
            e.preventDefault();

            let selectedText = $(this).text();
            let selectedValue = $(this).data('value');

            $('#dropdownButtonGroup').text(selectedText);
            $('#selectedGroup').val(selectedValue);
        });
    });

$(document).ready(function () {
    $('.dropdown-item-subject').on('click', function (e) {
        e.preventDefault(); // عشان ما يروحش للرابط

        let subjectName = $(this).text(); // اسم المادة
        let subjectId = $(this).data('value'); // ID بتاع المادة

        // تحديث input المخفي
        $('#selectedSubject').val(subjectId);

        // تحديث نص الزر
        $('#dropdownButtonSubject').contents().filter(function () {
            return this.nodeType === 3; // نحصل على النص فقط بدون الأيقونة
        }).first().replaceWith(subjectName + ' ');
    });
});
$(document).ready(function () {
    $('.dropdown-item-group').on('click', function (e) {
        e.preventDefault(); // منع التنقل

        let groupName = $(this).text(); // الاسم الظاهر
        let groupId = $(this).data('value'); // الـ ID

        // نحفظ الـ ID في الـ input المخفي
        $('#selectedGroup').val(groupId);

        // نغير النص داخل الزر ونسيب الأيقونة في حال وجودها
        $('#dropdownButtonGroup').contents().filter(function () {
            return this.nodeType === 3; // نحصل على النص (بدون الأيقونات أو العناصر)
        }).first().replaceWith(groupName + ' ');
    });
});
$(document).ready(function () {
    $('.dropdown-item2').on('click', function (e) {
        e.preventDefault();

        let levelName = $(this).text();         // اسم المستوى
        let levelId = $(this).data('value');    // ID الخاص بالمستوى

        // تحديث input المخفي
        $('#selectedLevel').val(levelId);

        // تحديث نص الزر مع الحفاظ على الأيقونات إن وُجدت
        $('#selectedLevel').siblings('button').contents().filter(function () {
            return this.nodeType === 3;
        }).first().replaceWith(levelName + ' ');
    });
});
$(document).ready(function () {
    $('.dropdown-item3').on('click', function (e) {
        e.preventDefault();

        let languageName = $(this).text();          
        let languageId = $(this).data('value');      

        // تحديث input المخفي
        $('#selectedLanguage').val(languageId);


        $('#selectedLanguage')
            .siblings('button')
            .contents()
            .filter(function () {
                return this.nodeType === 3; 
            })
            .first()
            .replaceWith(languageName + ' ');
    });
});


</script>
@endpush
