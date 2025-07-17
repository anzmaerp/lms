<main class="tb-main am-dispute-system am-ip-restriction-system">
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="tb-dhb-mainheading">
                <h4>{{ __('ipmanager::ipmanager.all_ip_restrictions') . ' (' . $ipRestrictions->total() . ')' }}</h4>
                <div class="tb-actionselect">
                    <a href="javascript:void(0)" id="add_ip_restriction_click" class="tb-btn add-new"
                        wire:click="showModal">{{__('general.add_new')}}
                        <i class="icon-plus"></i></a>
                </div>
            </div>
            <div class="am-disputelist_wrap">
                <div class="am-disputelist am-custom-scrollbar-y">
                    @if (!$ipRestrictions->isEmpty())
                        <table class="tb-table">
                            <thead>
                                <tr>
                                    <th>{{ __('ipmanager::ipmanager.id') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.type') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.value') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.reason') }}</th>
                                    <th>{{ __('ipmanager::ipmanager.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ipRestrictions as $ipRestriction)
                                    <tr>
                                        <td data-label="{{ __('ipmanager::ipmanager.id') }}">
                                            <span>{{ $ipRestriction?->id }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.type') }}">
                                            <span>{{ __('ipmanager::ipmanager.' . $ipRestriction?->type) }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.value') }}">
                                            @if ($ipRestriction?->type == 'specific_ip')
                                                <span>{{ $ipRestriction?->ip_start }}</span>
                                            @elseif($ipRestriction?->type == 'ip_range')
                                                <span>{{ $ipRestriction?->ip_start }} - {{ $ipRestriction?->ip_end }}</span>
                                            @elseif($ipRestriction?->type == 'country')
                                                <span>{{ $ipRestriction?->country }}</span>
                                            @endif
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.reason') }}">
                                            <span>{{ $ipRestriction?->reason }}</span>
                                        </td>
                                        <td data-label="{{ __('ipmanager::ipmanager.actions') }}">
                                            <ul class="tb-action-icon">
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span class="am-tooltip-text">{{ __('ipmanager::ipmanager.edit') }}</span>
                                                         <a href="javascript:void(0);" wire:click="editRestriction({{ $ipRestriction?->id }})" class="tb-edit"><i class="icon-edit"></i></a>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="am-custom-tooltip">
                                                        <span class="am-tooltip-text">{{ __('ipmanager::ipmanager.remove') }}</span>
                                                         <a href="javascript:void(0);" @click="$wire.dispatch('showConfirm', { id : {{ $ipRestriction?->id }}, action : 'delete-ip-restriction' })"  class="tb-delete"><i class="icon-trash-2"></i></a>
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
                {{ $ipRestrictions->links('pagination.custom') }}
            </div>
        </div>
    </div>
    <div wire:ignore.self class="modal fade tb-addonpopup" id="tb-add-ip-restriction" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg tb-modaldialog" role="document">
            <div class="modal-content">
                <div class="tb-popuptitle">
                    <h5 id="tb_ip_restriction_info_label">{{ $id ? __('ipmanager::ipmanager.update_restriction') : __('ipmanager::ipmanager.create_restriction') }}</h5>
                    <a href="javascript:void(0);" class="close"><i class="icon-x" data-bs-dismiss="modal"></i></a>
                </div>
                <div class="modal-body">
                    <form class="tb-themeform" wire:submit.prevent="updateOrCreateIPRestriction" id="add_ip_restriction_form">
                        <fieldset>
                            <div class="form-group-wrap">
                                <div class="form-group">
                                    <label class="tb-label">{{__('ipmanager::ipmanager.type')}}</label>
                                    <div class="tk-error @error('type') tk-invalid @enderror">
                                        <div class="tb-select">
                                            <select data-componentid="@this" class="am-select2 form-control"
                                                data-searchable="false" data-parent="#tb-add-ip-restriction" data-live='true'
                                                id="type" data-wiremodel="type">
                                                <option value="specific_ip" {{ $type == 'specific_ip' ? 'selected' : '' }}>{{__('ipmanager::ipmanager.specific_ip')}}</option>
                                                <option value="ip_range" {{ $type == 'ip_range' ? 'selected' : '' }}>{{__('ipmanager::ipmanager.ip_range')}}</option>
                                                <option value="country" {{ $type == 'country' ? 'selected' : '' }}>{{__('ipmanager::ipmanager.country')}}</option>
                                            </select>
                                        </div>
                                    </div>
                                    @error('type')
                                    <div class="tk-errormsg">
                                        <span>{{$message}}</span>
                                    </div>
                                    @enderror
                                </div>
                                @if($type == 'specific_ip')
                                    <div class="form-group">
                                        <label class="tb-label">{{__('ipmanager::ipmanager.specific_ip')}}</label>
                                        <input type="text"
                                            class="form-control @error('ip_address') tk-invalid @enderror"
                                            wire:model="ip_address"
                                            placeholder="{{__('ipmanager::ipmanager.specific_ip_placeholder')}}">
                                        @error('ip_address')
                                        <div class="tk-errormsg">
                                            <span>{{$message}}</span>
                                        </div>
                                        @enderror
                                    </div>
                                @elseif($type == 'ip_range')
                                    <div class="form-group">
                                        <label class="tb-label">{{__('ipmanager::ipmanager.ip_range')}}</label>
                                        <input type="text"
                                            class="form-control @error('ip_range') tk-invalid @enderror"
                                            wire:model="ip_range"
                                            placeholder="{{__('ipmanager::ipmanager.ip_range_placeholder')}}">
                                        @error('ip_range')
                                        <div class="tk-errormsg">
                                            <span>{{$message}}</span>
                                        </div>
                                        @enderror
                                    </div>    
                                @elseif($type == 'country')
                                    <div class="form-group">
                                        <label class="tb-label">{{__('ipmanager::ipmanager.country')}}</label>
                                        <div class="tk-error @error('country') tk-invalid @enderror">
                                            <div class="tb-select">
                                                <select data-componentid="@this" class="am-select2 form-control" data-searchable="true" data-parent="#tb-add-ip-restriction" data-live='true' id="country" data-wiremodel="country">
                                                    <option value="">{{__('ipmanager::ipmanager.select_country')}}</option>
                                                    @foreach($countries as $ctry)
                                                        <option value="{{ $ctry->name }}" {{ $ctry->name == $country ? 'selected' : '' }}>{{ $ctry->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @error('country')
                                        <div class="tk-errormsg">
                                            <span>{{$message}}</span>
                                        </div>
                                        @enderror
                                    </div>
                                @endif
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
                                    <button class="tb-btn" type="submit" wire:target="updateOrCreateIPRestriction, type"
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

