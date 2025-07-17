<div class="am-assignlistpage am-assignlistpage_vtwo"  wire:init="loadData">
    <div class="am-title_wrap">
        <div class="am-title">
            <h2>{{ $filters['status'] != '' ? __('assignments::assignments.'.$statuses[$filters['status']]) : __('assignments::assignments.all') }} {{ __('assignments::assignments.upcoming_assignments') }}</h2>
            <p>{{ __('assignments::assignments.assignments_description') }}</p>
        </div>
        @if($assignments->isNotEmpty() || !empty($filters))
            <div class="am-assignsearch_header">
                <div class="am-assignsearch_search">
                    <input type="text"  wire:model.live.debounce.500ms="filters.keyword" class="form-control" placeholder="{{ __('assignments::assignments.search_placeholder') }}">
                    <i class="am-icon-search-02"></i>
                </div>
                <div class="am-slots_wrap">
                    <ul class="am-category-slots">
                        <li>
                            <button {{($filters['status']) == '' ? 'class=active' : ''}} wire:click="filterStatus('')">
                                {{ __('assignments::assignments.all') }}  
                            </button>
                        </li>
                        @foreach ($statuses as $key => $status)
                            <li>
                                <button {{($filters['status']??'') == $key ? 'class=active' : ''}} wire:click="filterStatus('{{ $key }}')">
                                    {{ __('assignments::assignments.'.$status) }}  
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <a href="{{route('assignments.tutor.create-assignment')}}">
                    <button class="am-btn">
                        {{ __('assignments::assignments.create_assignment') }}
                        <i class="am-icon-plus-02"></i>
                    </button>
                </a>
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
                                    <img src="{{ !empty($assignmentData?->thumbnail) ? url(Storage::url($assignmentData?->thumbnail)) : asset('demo-content/placeholders/placeholder.png') }}" alt="{{ $assignmentData?->title }}" />
                                    <figcaption>
                                        @if(in_array($assignmentData->status, ['archived','published']) && $assignmentData?->submissions_assignments_count > 0)
                                            <span class="am-quizattempted">{{ number_format($assignmentData?->submissions_assignments_count) }} {{ __('assignments::assignments.attempted')}}<i class="am-icon-check-circle03"></i></span>
                                        @else
                                            <span class="cr-status">
                                                <span style="background-color: {{ $assignmentData->status_color ?? '#008000' }};" class="cr-dot active"></span>
                                                {{ __('assignments::assignments.'.$assignmentData->status) }}
                                            </span>
                                        @endIf
                                    </figcaption>
                                </figure>

                                <div class="am-assignlist_item_content">
                                    <div class="am-assignlist_coursename">
                                        <div class="am-assignlist_coursetitle">
                                            @if(!empty($assignmentData->title) || !empty($assignmentData?->related_type))
                                                <a href="#">
                                                    <h3>{{ $assignmentData->title }}</h3>
                                                </a>
                                                @if($assignmentData?->related_type == 'Modules\Courses\Models\Course')
                                                    <span>{{ __('assignments::assignments.course') }}</span>
                                                @else
                                                    <span>{{ __('assignments::assignments.subject') }}</span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="am-itemdropdown">
                                            <a href="javascript:void(0);" id="am-itemdropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="am-icon-ellipsis-vertical-02"></i>
                                            </a>
                                            <ul class="am-itemdropdown_list dropdown-menu" aria-labelledby="dropdownMenuLink">
                                                @if(in_array($assignmentData->status, ['draft']))
                                                    <li>
                                                        <a href="{{ route('assignments.tutor.update-assignment', ['id' => $assignmentData->id]) }}">
                                                            <i class="am-icon-pencil-02"></i>
                                                            {{ __('assignments::assignments.edit_assignment') }}
                                                        </a>
                                                    </li>
                                                    <li @click="$wire.dispatch('showConfirm', { id: {{$assignmentData->id}}, action: 'delete-assignment'})" >
                                                        <a href="javascript:void(0);">
                                                            <i class="am-icon-trash-02"></i>
                                                            {{__('assignments::assignments.delete_assignment') }}
                                                        </a>
                                                    </li>
                                                @endif
                                                @if(in_array($assignmentData->status, ['draft', 'archived']))     
                                                    <li wire:click="openPublishModal({{$assignmentData->id}},'{{ $assignmentData->status }}')">
                                                        <a href="javascript:void(0);">
                                                            <i class="am-icon-check-circle03"></i>
                                                            {{__('assignments::assignments.publish_assignment') }}    
                                                        </a>
                                                    </li>
                                                @endif      
                                                @if(in_array($assignmentData->status, ['published']))
                                                    <li @click="$wire.dispatch('showConfirm', { id: {{$assignmentData->id}}, action: 'archive-assignment' })">
                                                        <a href="#">
                                                            <i class="am-icon-archive-01"></i>
                                                            {{__('assignments::assignments.archive_assignment') }}
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('assignments.tutor.submissions-assignments-list', ['assignmentId' => $assignmentData->id]) }}">
                                                            <i class="am-icon-eye-open-01"></i>
                                                            {{__('assignments::assignments.view_submited_assignment') }}
                                                        </a>
                                                    </li>
                                                @endif      
                                            </ul>
                                        </div>
                                    </div>
                                    <ul class="am-assignlist_item_footer">
                                        @if(!empty($assignmentData?->title))
                                            <li>
                                                <span>
                                                    <i class="am-icon-calender-day"></i>
                                                    {{ __('assignments::assignments.created_at') }}
                                                </span>
                                                <em>{{ date((setting('_general.date_format') ?? 'M d, Y'), strtotime($assignmentData?->created_at)) }} {{ date('h:i a', strtotime($assignmentData?->created_at)) }}</em>
                                            </li>
                                        @endif
                                        @if(!empty($assignmentData?->total_marks))
                                            <li>
                                                <span>
                                                    <i class="am-icon-trophy-04"></i>
                                                    {{ __('assignments::assignments.total_marks') }}
                                                </span> 
                                                <em>{{ $assignmentData?->total_marks }}</em>
                                            </li>
                                        @endif
                                        @if(!empty($assignmentData?->passing_percentage))
                                            <li>
                                                <span>
                                                    <i class="am-icon-shield-check"></i>
                                                    {{ __('assignments::assignments.passing_grade') }}
                                                </span> 
                                                <em>{{ $assignmentData?->passing_percentage }}%</em>
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
                            <p>{{__('assignments::assignments.no_assignments_desc') }}</p>
                        </div>
                        <div class="cr-bundle-emptycase_btns">
                            <a href="{{ route('assignments.tutor.create-assignment') }}" class="am-btn">
                                {{__('assignments::assignments.create_assignment') }}
                                <i class="am-icon-plus-02"></i>
                            </a>
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
                        @if($assignmentStatus == 'archived')
                            <a href="#" data-bs-dismiss="modal" class="am-white-btn am-btnsmall">{{ __('assignments::assignments.cancel') }}</a>
                        @else
                            <a href="{{ route('assignments.tutor.update-assignment', ['id' => $assignmentId]) }}" class="am-white-btn am-btnsmall">{{ __('assignments::assignments.edit_assignment') }}</a>
                        @endif
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