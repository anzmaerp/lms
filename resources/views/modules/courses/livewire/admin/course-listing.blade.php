<main class="tb-main am-dispute-system am-courses-system">

    <div class="row">
        <div class="col-lg-12 col-md-12">

            <div class="tb-dhb-mainheading">
                <h4>{{ __('courses::courses.all_courses') . ' (' . $courses->total() . ')' }}</h4>

                <div class="tb-sortby" style="display: flex; flex-direction: column; gap: 1rem;">
                    <div style="text-align: left;">
                        <a class="am-btn cr-btn" href="{{ route('courses.tutor.create-course') }}">
                            {{ __('courses::courses.create_course') }}
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18"
                                fill="none">
                                <path d="M3.75 9H9M14.25 9H9M9 9V3.75M9 9V14.25" stroke="white" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </a>
                    </div>

                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">

                              <div class="tb-actionselect">
                                    <a href="javascript:void(0)" wire:click="printUsersExcel"
                                        class="d-flex align-items-center justify-content-center"
                                        style="width: 40px; height: 40px; background:#1D6F42; color:#fff; border-radius:6px;">
                                        <i class="fa fa-file-excel" style="font-size:18px;"></i>
                                    </a>                                </div>
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select" wire:ignore>
                                        <select data-componentid="@this" class="am-select2 form-control"
                                            data-searchable="false" data-live='true' id="category-select"
                                            data-wiremodel="status">
                                            <option value="">{{ __('courses::courses.all') }}</option>
                                            @foreach ($statuses as $filter_status)
                                                <option value="{{ $filter_status }}"
                                                    {{ $status == $filter_status ? 'selected' : '' }}>
                                                    {{ __('courses::courses.' . $filter_status) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control"
                                        wire:model.live.debounce.500ms="filters.keyword" autocomplete="off"
                                        placeholder="{{ __('courses::courses.search_by_keyword') }}">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>

            </div>

            <div class="am-disputelist_wrap">
                <div class="am-disputelist am-custom-scrollbar-y">
                    @if (!$courses->isEmpty())
                        <table class="tb-table">
                            <thead>
                                <tr>
                                    <th>{{ __('courses::courses.id') }}</th>
                                    <th>{{ __('courses::courses.title') }}</th>
                                    <th>{{ __('courses::courses.instructor') }}</th>
                                    <th>{{ __('courses::courses.category') }}</th>
                                    <th>{{ __('courses::courses.subcategory') }}</th>
                                    <th>{{ __('courses::courses.status') }}</th>
                                    <th>{{ __('courses::courses.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courses as $course)
                                    <tr>
                                        <td data-label="{{ __('courses::courses.id') }}">
                                            <span>{{ $course->id }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.title') }}">
                                            <span>{{ $course->title }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.instructor') }}">
                                            <span>{{ $course->instructor?->profile?->full_name }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.category') }}">
                                            <span>{{ $course->category->name }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.subcategory') }}">
                                            <span>{{ $course->subCategory->name }}</span>
                                        </td>
                                        <td data-label="{{ __('courses::courses.status') }}">
                                            <div class="am-status-tag">
                                                <em
                                                    @class([
                                                        'tk-project-tag',
                                                        'tk-active' => $course->status == 'active',
                                                        'tk-disabled' => $course->status == 'inactive',
                                                        'tk-disabled' => $course->status == 'under_review',
                                                    ])>{{ __('courses::courses.' . $course->status) }}</em>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('courses::courses.actions') }}">
                                            <ul class="tb-action-icon">
                                                @if ($course->status == 'under_review')
                                                    <li>
                                                        <div class="am-custom-tooltip">
                                                            <span
                                                                class="am-tooltip-text">{{ __('courses::courses.approve_course') }}</span>
                                                            <a href="javascript:void(0);"
                                                                wire:click="approveCourse({{ $course->id }})">
                                                                <i class="icon-check"></i>
                                                            </a>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="am-custom-tooltip">
                                                            <span
                                                                class="am-tooltip-text">{{ __('courses::courses.reject_course') }}</span>
                                                            <a href="javascript:void(0);"
                                                                wire:click="rejectCourse({{ $course->id }})">
                                                                <i class="icon-x"></i>
                                                            </a>
                                                        </div>
                                                    </li>
                                                @endif
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span
                                                            class="am-tooltip-text">{{ __('courses::courses.view_details') }}</span>
                                                        <a href="{{ route('courses.course-detail', ['slug' => $course->slug]) }}"
                                                            target="_blank">
                                                            <i class="icon-eye"></i>
                                                        </a>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span
                                                            class="am-tooltip-text">{{ __('courses::courses.remove_course') }}</span>
                                                        <a href="javascript:void(0);"
                                                            @click="$wire.dispatch('showConfirm', { id : {{ $course->id }}, action : 'delete-course' })"
                                                            class="tb-delete"><i class="icon-trash-2"></i></a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $courses->links('pagination.custom') }}
                    @else
                        <x-no-record :image="asset('images/empty.png')" :title="__('courses::courses.no_records_found')" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
    <script type="text/javascript">
        document.addEventListener('livewire:initialized', function() {
            $(document).on('select2:select', '#category-select', function(e) {
                let selectedValue = e.params.data.id;
                @this.set('filters.status', selectedValue);
            });

        });
    </script>
@endpush
