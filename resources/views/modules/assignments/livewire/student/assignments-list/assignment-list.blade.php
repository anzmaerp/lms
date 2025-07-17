<div class="am-assignlistpage"  wire:init="loadData">
    <div class="am-title_wrap">
        <div class="am-title">
       <h2>{{ __('assignments::assignments.'.$statuses[$filters['studentStatus']]) }} {{ __('assignments::assignments.upcoming_assignments') }}</h2>
            <p>{{ __('assignments::assignments.upcoming_assignments_description') }}</p>
        </div>
        @if($assignments->isNotEmpty() || !empty($filters))
            <div class="am-assignsearch_header">
                <div class="am-assignsearch_search">
                    <input type="text"  wire:model.live.debounce.500ms="filters.keyword" class="form-control" placeholder="{{ __('assignments::assignments.search_placeholder') }}">
                    <i class="am-icon-search-02"></i>
                </div>
                <div class="am-slots_wrap">
                    <ul class="am-category-slots">
                        @foreach ($statuses as $key => $status)
                            <li>
                                <button {{($filters['studentStatus']??'') == $key ? 'class=active' : ''}} wire:click="filterStatus('{{ $key }}')">
                                    {{    __('assignments::assignments.'.$status) }}  
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endIf
    </div>
    @if($isLoading)
        <div>
            @include('assignments::skeletons.assignments-listing', ['total' => $perPage])
        </div>
    @else
        <div class="d-none tutors-skeleton" wire:target="filters, loadData,filterStatus,perPage" wire:loading.class.remove="d-none">
            @include('assignments::skeletons.assignments-listing', ['total' => $perPage])
        </div>
        <div class="am-assignlist_wrap" wire:loading.class="d-none" wire:target="filters, loadData,filterStatus,perPage">
            @if($assignments->isNotEmpty())
                <ul>
                    @foreach($assignments as $assignmentData)
                        <li>
                            <div class="am-assignlist_item">
                                <figure>
                                    <img src="{{ !empty($assignmentData->assignment->thumbnail) ? url(Storage::url($assignmentData->assignment->thumbnail)) : asset('demo-content/placeholders/placeholder.png') }}" alt="{{ $assignmentData->assignment?->title }}" />
                                    <figcaption>
                                        <span class="am-status am-status_{{ $filters['studentStatus'] == 'overdue' ? 'overdue' : $assignmentData->result }}">
                                            @php
                                            $statusMap = [
                                                'pass'   => __('assignments::assignments.pass'),
                                                'fail' => __('assignments::assignments.fail'),
                                                'in_review'    => __('assignments::assignments.in_review'),
                                                'assigned'    => __('assignments::assignments.assigned'),
                                                'overdue'   => __('assignments::assignments.overdue'),
                                            ];
                                            @endphp
                                            {{ ($assignmentData->ended_at <= now() && $filters['studentStatus'] == 'overdue') ? $statusMap['overdue'] : $statusMap[$assignmentData->result] }}
                                        </span>
                                    </figcaption>
                                </figure>
                                <div class="am-assignlist_item_content">
                                    <div class="am-assignlist_coursename">
                                        <div class="am-assignlist_coursetitle">
                                            @if(!empty($assignmentData->assignment?->title) || !empty($assignmentData->assignment?->related_type))
                                                <a href="{{ $assignmentData->result == 'assigned' ? route('assignments.student.attempt-assignment', ['id' => $assignmentData->id]) : route('assignments.student.assignment-result', ['submissionId' => $assignmentData->id]) }}">
                                                    <h3>{{ $assignmentData->assignment?->title }}</h3>
                                                </a>
                                                @if($assignmentData->assignment?->related_type == 'Modules\Courses\Models\Course')
                                                    <span>{{ __('assignments::assignments.course') }}</span>
                                                @else
                                                    <span>{{ __('assignments::assignments.subject') }}</span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    <ul class="am-assignlist_item_footer">
                                        @if(!empty($assignmentData?->ended_at))
                                            <li>
                                                @php
                                                if(empty($assignmentData->submitted_at)){
                                                    $title = __('assignments::assignments.deadline');  
                                                    $date = $assignmentData->ended_at;
                                                }else{
                                                    $title = __('assignments::assignments.submitted');  
                                                    $date = $assignmentData->submitted_at;
                                                }
                                            @endphp
                                                <span>
                                                    <i class="am-icon-calender-day"></i>
                                                    {{ $title }}
                                                </span>
                                                <em>{{ date($dateFormat, strtotime($date)) }} {{ date('h:i a', strtotime($date)) }}</em>
                                            </li>
                                        @endif
                                        @if(!empty($assignmentData->assignment?->total_marks))
                                            <li>
                                                <span>
                                                    <i class="am-icon-trophy-04"></i>
                                                    {{ __('assignments::assignments.total_marks') }}
                                                </span> 
                                                <em>{{ $assignmentData?->assignment?->total_marks }}</em>
                                            </li>
                                        @endif
                                        @if(!empty($assignmentData?->assignment?->passing_percentage))
                                            <li>
                                                <span>
                                                    <i class="am-icon-shield-check"></i>
                                                    {{ __('assignments::assignments.passing_grade') }}
                                                </span> 
                                                <em>{{ $assignmentData?->assignment?->passing_percentage }}%</em>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
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
                            <img src="{{asset('modules/assignments/images/no-record.png')}}" alt="img description">
                        </figure>
                        <div class="cr-bundle-emptycase_content">
                            <h3>{{__('assignments::assignments.no_assignments') }}</h3>
                            <p>{{__('assignments::assignments.no_assignments_description') }}</p>
                        </div>
                    </div>
                </div>
            @endIf
        </div>
    @endif
    <div class="modal fade am-confirm-popup" id="course_completed_popup" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="am-modal-body">
                    <span data-bs-dismiss="modal" class="am-closepopup">
                        <i class="am-icon-multiply-01"></i>
                    </span>
                    <div class="am-deletepopup_icon warning-icon">
                        <span>
                            <i class="am-icon-exclamation-01"></i>
                        </span>
                    </div>
                    <div class="am-confirm-popup_title">
                        <h3>{{ __('assignments::assignments.publish_assignment_confirmation_title') }}</h3>
                        <p>{{ __('assignments::assignments.publish_assignment_confirmation_desc') }}</p>
                    </div>
                    <div class="am-confirm-popup_btns">
                        <a href="{{ route('assignments.tutor.update-assignment', ['id' => $assignmentId]) }}" class="am-white-btn am-btnsmall">{{ __('assignments::assignments.edit_assignment') }}</a>
                        <a  
                            wire:loading.class="am-btn_disable" 
                            wire:target="publishAssignment"
                            wire:click="publishAssignment" class="am-btn am-btnsmall">{{ __('assignments::assignments.publish') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>
@push('styles')
    <link rel="stylesheet" href="{{ asset('modules/assignments/css/main.css') }}">
@endpush