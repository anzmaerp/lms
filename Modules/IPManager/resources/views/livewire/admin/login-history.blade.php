<main class="tb-main am-dispute-system am-loginhistory-system">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="tb-dhb-mainheading">
                <h4>{{ __('ipmanager::ipmanager.all_logins') . ' (' . $userLogs->total() . ')' }}</h4>
                <div class="tb-sortby">
                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select" wire:ignore>
                                        <select data-componentid="@this" class="am-select2 form-control" placeholder="{{ __('ipmanager::ipmanager.session_status') }}" data-searchable="false" data-live='true' id="category-select" data-wiremodel="filters.status">
                                            <option value="" >{{ __('ipmanager::ipmanager.all') }}</option>
                                            <option value="open" {{ $filters['status'] == 'open' ? 'selected' : '' }}>{{ __('ipmanager::ipmanager.open') }}</option>
                                            <option value="ended" {{ $filters['status'] == 'ended' ? 'selected' : '' }}>{{ __('ipmanager::ipmanager.ended') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect">
                                    <div @class(['form-control_wrap', 'am-invalid' => $errors->has('startDate')])>
                                        <x-text-input 
                                            class="flat-date" 
                                            id="datepicker" 
                                            data-format="F-d-Y" 
                                            wire:model.live="filters.start_date" 
                                            placeholder="{{ __('ipmanager::ipmanager.start_date') }}" 
                                            type="text" 
                                            autofocus 
                                            autocomplete="name" 
                                        />
                                        <x-input-error field_name="startDate" />
                                    </div>
                                </div>
                                <div class="tb-actionselect">
                                    <div @class(['form-control_wrap', 'am-invalid' => $errors->has('endDate')])>
                                        <x-text-input 
                                            class="flat-date" 
                                            id="datepicker" 
                                            data-format="F-d-Y" 
                                            wire:model.live="filters.end_date" 
                                            placeholder="{{ __('ipmanager::ipmanager.end_date') }}" 
                                            type="text" 
                                            autofocus 
                                            autocomplete="name" 
                                        />
                                        <x-input-error field_name="endDate" />
                                    </div>
                                </div>
                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control" wire:model.live.debounce.500ms="filters.search"
                                        autocomplete="off" placeholder="{{ __('ipmanager::ipmanager.search_by_keyword') }}">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
            <div class="am-disputelist_wrap">
                <div class="am-disputelist am-custom-scrollbar-y">
                    @if (!$userLogs->isEmpty())
                        <table class="tb-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ipmanager::ipmanager.id') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.user') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.os') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.browser') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.device') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.ip_address') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.country') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.city') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.lat_long') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.session_start') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.session_end') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.duration') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($userLogs as $userLog)
                                    <tr>
                                        <td data-label="{{ __('ipmanager::ipmanager.id') }}">
                                            <span>{{ $userLog->id }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.user') }}">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    @if (!empty($userLog->user->profile->image) && Storage::disk(getStorageDisk())->exists($userLog->user->profile->image))
                                                        <img src="{{ resizedImage($userLog->user->profile->image,34,34) }}" alt="{{$userLog->user->profile->image}}" />
                                                    @else
                                                        <img src="{{ setting('_general.default_avatar_for_user') ? url(Storage::url(setting('_general.default_avatar_for_user')[0]['path'])) : resizedImage('placeholder.png', 34, 34) }}" alt="{{ $userLog->user->profile->image }}" />
                                                    @endif
                                                </strong>
                                                <span>{{ $userLog->user->profile->full_name }}</span>
                                                @if($userLog->user->roles()->first()->name == 'tutor')
                                                    <a href="{{ route('tutor-detail',['slug' => $userLog->user->profile->slug]) }}" class="am-custom-tooltip">
                                                        <span class="am-tooltip-text">
                                                            <span>{{ __('general.visit_profile') }}</span>
                                                        </span>
                                                        <i class="icon-external-link"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.os') }}">
                                            <span>{{ $userLog?->os }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.browser') }}">
                                            <span>{{ $userLog?->browser }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.device') }}">
                                            <span>{{ $userLog?->device }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.ip_address') }}">
                                            <span>{{ $userLog?->ip_address }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.country') }}">
                                            <span>{{ $userLog?->country }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.city') }}">
                                            <span>{{ $userLog?->city }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.lat_long') }}">
                                            <span>{{ $userLog?->latitude }} - {{ $userLog?->longitude }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.session_start') }}">
                                            <span>{{ $userLog?->session_start }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.session_end') }}">
                                            <span>{{ $userLog?->session_end ?? '-' }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.duration') }}">
                                            <span>
                                                @php
                                                    $duration = $userLog?->duration ?? 0;
                                                @endphp
                                                @if ($duration > 0)
                                                    @php
                                                        $hours = floor($duration / 3600);
                                                        $minutes = floor(($duration % 3600) / 60);
                                                        $seconds = $duration % 60;
                                                    @endphp
                                                    @if ($hours > 0)
                                                        {{ $hours }} Hr{{ $hours > 1 ? 's' : '' }}{{ $minutes > 0 ? ', ' . $minutes . ' Min' : '' }}
                                                    @elseif ($minutes > 0)
                                                        {{ $minutes }} Min
                                                    @else
                                                        {{ $seconds }} Second{{ $seconds > 1 ? 's' : '' }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.actions') }}">
                                            <ul class="tb-action-icon">
                                                @if(empty($userLog?->session_end))
                                                    <li>
                                                        <div class="am-custom-tooltip">
                                                            <span class="am-tooltip-text">{{ __('ipmanager::ipmanager.end_session') }}</span>
                                                            <a href="javascript:void(0);" @click="$wire.dispatch('showConfirm', { id : {{ $userLog->id }}, icon : 'warning', content : '{{ __('ipmanager::ipmanager.end_session_this_session') }}', action : 'end-session-user-log' })"><i class="icon-arrow-down"></i></a>
                                                        </div>
                                                    </li>
                                                @endif
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span class="am-tooltip-text">{{ __('ipmanager::ipmanager.block_ip') }}</span>
                                                         <a href="javascript:void(0);" wire:click="openModal('{{ $userLog->ip_address }}',{{ $userLog->id }},{{ $userLog->user_id }})"><i class="icon-slash"></i></a>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span class="am-tooltip-text">{{ __('ipmanager::ipmanager.remove') }}</span>
                                                         <a href="javascript:void(0);" @click="$wire.dispatch('showConfirm', { id : {{ $userLog->id }}, content : '{{ __('ipmanager::ipmanager.delete_user_log') }}', action : 'delete-user-log' })"  class="tb-delete"><i class="icon-trash-2"></i></a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <x-no-record :image="asset('images/empty.png')" :title="__('ipmanager::ipmanager.no_records_found')" />
                    @endif
                </div>
                {{ $userLogs->links('pagination.custom') }}
            </div>
        </div>
    </div>
    <div wire:ignore.self class="modal fade tb-addonpopup" id="tb-add-ip-restriction" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg tb-modaldialog" role="document">
            <div class="modal-content">
                <div class="tb-popuptitle">
                    <h5 id="tb_ip_restriction_info_label">{{__('ipmanager::ipmanager.add_reason')}}</h5>
                    <a href="javascript:void(0);" class="close"><i class="icon-x" data-bs-dismiss="modal"></i></a>
                </div>
                <div class="modal-body">
                    <form class="tb-themeform" wire:submit.prevent="blockIp" id="add_ip_restriction_form">
                        <fieldset>
                            <div class="form-group">
                                <label class="tb-label">{{__('ipmanager::ipmanager.reason')}}</label>
                                <textarea type="text"
                                    class="form-control @error('reason') tk-invalid @enderror"
                                    wire:model="reason"
                                        placeholder="{{__('ipmanager::ipmanager.reason_placeholder')}}">
                                    </textarea>
                                    @error('reason')
                                    <div class="tk-errormsg">
                                        <span>{{$message}}</span>
                                    </div>
                                    @enderror
                                </div>
                                <div class="form-group tb-formbtn">
                                    <button class="tb-btn" type="submit" wire:target="blockIp"
                                        wire:loading.class="tb-btn_disable">{{__('general.save')}}</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

@push('styles')
    @vite([
        'public/css/flatpicker.css',
        'public/css/flatpicker-month-year-plugin.css'
    ])
@endpush
@push('scripts')
    <script defer src="{{ asset('js/flatpicker.js') }}"></script>
    <script defer src="{{ asset('js/flatpicker-month-year-plugin.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            initializeDatePicker();
        });
    </script>
@endpush

