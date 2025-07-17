<div class="am-assignments_submitted" wire:init="loadData">
    <div class="am-userbox am-userbox_vtwo">
        <figure>
            <img src="{{ !empty($assignment?->thumbnail) ? url(Storage::url($assignment?->thumbnail)) : asset('demo-content/placeholders/placeholder.png') }}" alt="{{ $assignment?->title }}" />
        </figure>
        <div class="am-userbox_detail">
            @if(!empty($assignment?->title)) <h3>{{ $assignment?->title }}</h3> @endIf
            @if(!empty($assignment?->description))
                <div class="am-toggle-text">
                    <div class="am-addmore">
                        @php
                            $fullDescription  = $assignment?->description;
                            $shortDescription = Str::limit(strip_tags($fullDescription), 250, preserveWords: true);
                        @endphp
                        @if (Str::length(strip_tags($fullDescription)) > 250)
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
            @endIf
            <ul>
                @if(!empty($assignment?->related_type))
                    @if($assignment?->related_type == 'Modules\Courses\Models\Course')
                        <li>
                            <i class="am-icon-layer-01"></i>
                            <span>{{ __('assignments::assignments.course') }}</span>
                        </li>
                    @else
                        <li>
                            <i class="am-icon-layer-01"></i>
                            <span>{{ __('assignments::assignments.subject') }}</span>
                        </li>
                    @endif
                @endif
                <li>
                    <i class="am-icon-check-circle03"></i>
                    <span>
                        <em>{{ $assignment?->submissions_assignments_count }}</em>
                        {{ __('assignments::assignments.attempted') }}
                    </span>
                </li>
            </ul>
        </div>
        <img src="{{asset ('modules/assignments/images/bg-shape2.png')}}" alt="user image">
    </div>
    <div class="am-filter-area"  wire:init="loadData">
        <h2>{{ __('assignments::assignments.submitted_assignments') }}</h2>
        <div class="am-filters">
            <div class="am-form-group">
                <input type="text" wire:model.live.debounce.500ms="filters.keyword" class="form-control" class="am-form-control" placeholder="{{ __('courses::courses.search_by_keyword') }}">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M13.9997 14L11.5756 11.576M11.5756 11.576C12.6614 10.4902 13.333 8.99023 13.333 7.33337C13.333 4.01967 10.6467 1.33337 7.33301 1.33337C4.0193 1.33337 1.33301 4.01967 1.33301 7.33337C1.33301 10.6471 4.0193 13.3334 7.33301 13.3334C8.98986 13.3334 10.4899 12.6618 11.5756 11.576Z" stroke="#585858" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
            <div class="am-form-group" wire:ignore>
                <select class="am-select2" data-componentid="@this" id="status" data-live="true" data-searchable="false" data-wiremodel="filters.status">
                    <option value="">{{ __('assignments::assignments.select_status') }}</option>
                    @if (!empty($statuses))
                        @foreach ($statuses as $key => $status)
                            <option value="{{ $key }}">{{ __('assignments::assignments.'. $status) }}</option>
                        @endforeach
                    @endif
                </select>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 6L8 10L12 6" stroke="#585858" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="am-form-group" wire:ignore>
                <select class="am-select2" data-componentid="@this" id="sortby" data-live="true" data-searchable="false" data-wiremodel="filters.sort_by">
                    <option value="desc">{{ __('assignments::assignments.newest_first') }}</option>
                    <option value="asc">{{ __('assignments::assignments.oldest_first') }}</option>
                </select>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4 6L8 10L12 6" stroke="#585858" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>
    @if($isLoading)
        <div>
            @include('assignments::skeletons.submissions-assignments-list', ['total' => $perPage])
        </div>
    @else
        <div class="d-none tutors-skeleton" wire:target="filters, loadData,filterStatus,perPage" wire:loading.class.remove="d-none">
            @include('assignments::skeletons.submissions-assignments-list', ['total' => $perPage])
        </div>
        <div class="am-assignments_table" wire:loading.class="d-none" wire:target="filters, loadData,filterStatus,perPage">
            @if($assignments->isNotEmpty())
                <table class="am-table am-table_submitted">
                    <thead>
                        <tr>
                            <th>{{ __('assignments::assignments.student') }}</th>
                            <th>{{ __('assignments::assignments.obtained_marks') }}</th>
                            <th>{{ __('assignments::assignments.deadline') }}</th>
                            <th>{{ __('assignments::assignments.submit_date') }}</th>
                            <th>{{ __('assignments::assignments.status') }}</th>
                            <th>{{ __('assignments::assignments.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($assignments as $assignmentData)
                            <tr>
                                <td data-label="{{ __('assignments::assignments.student') }}">    
                                    <div class="am-user_info">
                                        <figure>
                                            <img src="{{ !empty($assignmentData?->student?->profile?->image) ? url(Storage::url($assignmentData?->student?->profile?->image)) : asset('demo-content/placeholders/placeholder.png') }}" alt="user image">
                                        </figure>
                                        <div class="am-user_detail">
                                            @if($assignmentData?->student?->profile?->full_name) <h6>{{ $assignmentData->student?->profile?->full_name }}</h6> @endif
                                            @if($assignmentData?->student?->email) <span>{{ $assignmentData->student?->email }}</span> @endif
                                        </div>
                                    </div>
                                </td>
                                <td data-label="{{ __('assignments::assignments.obtained_marks') }}">
                                    <span>
                                        @if($assignmentData?->marks_awarded && $assignmentData?->assignment?->total_marks)
                                            {{ $assignmentData?->marks_awarded }}/{{ $assignmentData?->assignment?->total_marks }}
                                        @else
                                            N/A
                                        @endIf
                                    </span>
                                </td>
                                <td data-label="{{ __('assignments::assignments.deadline') }}">
                                    <span>
                                        {{ date('M d, Y', strtotime($assignmentData->ended_at)) }}
                                    </span>
                                </td>
                                <td data-label="{{ __('assignments::assignments.submit_date') }}">
                                    <div class="am-user_detail">
                                        <h6>{{ date('M d, Y', strtotime($assignmentData?->submitted_at)) }}</h6>
                                        <span>{{ date('h:i a', strtotime($assignmentData?->submitted_at)) }}</span>
                                    </div>
                                </td>
                                <td data-label="{{ __('assignments::assignments.status') }}">
                                    <span class="am-pass-tag">
                                        <span style="background-color: {{ $assignmentData->result_color ?? '#008000' }};" class="am-pass-tag_dott active"></span>
                                        {{ __('assignments::assignments.'.$assignmentData->result) }}
                                    </span>
                                </td>
                                <td data-label="{{ __('assignments::assignments.actions') }}">
                                    <a href="{{ route('assignments.tutor.mark-assignment', $assignmentData->id) }}" class="am-btn">
                                        {{ __('assignments::assignments.view_details') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if (!$isLoading && $assignments->links()->paginator->hasPages())
                    <div class='am-pagination cr-bundle-pagination'>
                        @if (!empty($parPageList))
                            <div class="am-pagination-filter" wire:ignore>
                                <em>{{ __('assignments::assignments.show') }}</em>
                                <span class="am-select">
                                    <select wire:model.live="perPage" x-init="$wire.dispatch('initSelect2', {target: '#per-page-select'});" class="am-select2" id="per-page-select" data-componentid="@this" data-live="true" data-searchable="false" data-wiremodel="perPage">
                                        @if (!empty($perPage) && !in_array($perPage, $parPageList))
                                            <option value="{{ $perPage }}">{{ $perPage }}</option>
                                        @endif
                                        @foreach ($parPageList as $option)
                                            <option {{ $perPage == $option ? 'selected' : '' }} value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </span>
                                <em>{{ __('assignments::assignments.listing_per_page') }}</em>
                            </div>
                        @endif
                        {{ $assignments->links('assignments::pagination.pagination') }}
                    </div>
                @endif
            @else
                <div class="cr-bundle-emptycase">
                    <div class="cr-bundle-emptycase_wrap">
                        <figure>
                            <img src="{{asset('modules/assignments/images/empty.png')}}" alt="img description">
                        </figure>
                        <div class="cr-bundle-emptycase_content">
                            <h3>{{__('assignments::assignments.no_records_found') }}</h3>
                            <p>{{__('assignments::assignments.no_assignments_desc') }}</p>
                        </div>
                    </div>
                </div>
            @endIf
        </div>
    @endif
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