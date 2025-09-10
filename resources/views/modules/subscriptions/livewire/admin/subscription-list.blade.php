<main class="tb-main tb-pageslanguagewrap tb-subscriptionwrap" wire:loading.class="am-isloading"
    wire:target="setSubscription, preview, edit, filters">

    <head>
        @php
            $googleFont = setting('_general.google_font') ?? 'Roboto';
        @endphp

        @if (!empty($googleFont))
            <link href="https://fonts.googleapis.com/css2?family={{ str_replace(' ', '+', $googleFont) }}&display=swap"
                rel="stylesheet">
            <style>
                * {
                    font-family: '{{ $googleFont }}', sans-serif !important;
                }
            </style>
        @endif
    </head>
    <div class="am-section-load" wire:loading.flex wire:target="setSubscription, preview, edit, filters">
        <p>{{ __('general.loading') }}</p>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-12 tb-md-40">
            <div class="tb-dbholder tb-packege-setting">
                <div class="tb-dbbox tb-dbboxtitle">
                    @if($editMode)
                        <h5> {{ __('subscriptions::subscription.update_subscription') }}</h5>
                    @else
                        <h5> {{ __('subscriptions::subscription.add_subscription') }}</h5>
                    @endif
                </div>
                <div class="tb-dbbox">
                    <form class="tk-themeform">
                        <fieldset>
                            <div class="tk-themeform__wrap">
                                <div class="form-group tb-packagesfor">
                                    <h6>{{ __('subscriptions::subscription.for_role') }}</h6>
                                    <ul class="tb-payoutmethod tb-packagestype">
                                        @foreach($roles as $role_id => $role_name)
                                            <li class="tb-radiobox">
                                                <input wire:model.lazy="subscription.role_id" type="radio"
                                                    id="radio-{{ $role_id }}" {{ $subscription['role_id'] ?? '' == $role_id ? 'checked' : '' }} value="{{ $role_id }}">
                                                <div class="tb-radio">
                                                    <label for="radio-{{ $role_id }}" class="tb-radiolist">
                                                        <span class="tb-wininginfo">
                                                            <i>
                                                                {{ setting('_lernen.' . $role_name . '_display_name') ?? __('subscriptions::subscription.' . $role_name) }}
                                                            </i>
                                                        </span>
                                                    </label>
                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                    @error('subscription.role_id')
                                        <div class="tk-errormsg">
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label
                                        class="tb-label tb-label-star">{{ __('subscriptions::subscription.name') }}</label>
                                    <input type="text"
                                        class="form-control @error('subscription.name') tk-invalid @enderror "
                                        wire:model="subscription.name" required
                                        placeholder="{{ __('subscriptions::subscription.name_placeholder') }}">
                                    @error('subscription.name')
                                        <div class="tk-errormsg">
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label
                                        class="tb-label tb-label-star">{{ __('subscriptions::subscription.period') }}</label>
                                    <div class="tb-select @error('subscription.price') tk-invalid @enderror">
                                        <select wire:model="subscription.period" class="form-control am-select2"
                                            data-field="subscription.period">
                                            <option value="">{{ __('subscriptions::subscription.select_period') }}
                                            </option>
                                            @foreach($periods as $key => $period)
                                                <option value="{{ $key }}" {{ $subscription['period'] == $key ? 'selected' : '' }}>{{ $period }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('subscription.period')
                                        <div class="tk-errormsg">
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label
                                        class="tb-label tb-label-star">{{ __('subscriptions::subscription.price') }}</label>
                                    <input type="number" min="0"
                                        class="form-control @error('subscription.price') tk-invalid @enderror "
                                        wire:model="subscription.price" required
                                        placeholder="{{ __('subscriptions::subscription.price_placeholder') }}">
                                    @error('subscription.price')
                                        <div class="tk-errormsg">
                                            <span>{{ $message }}</span>
                                        </div>
                                    @enderror
                                </div>
                                @if(count($subscription['credit_limits']) > 0)
                                    @foreach($subscription['credit_limits'] as $key => $limit)
                                        <div class="form-group" wire:key="limit_options-{{ $key }}">
                                            <label
                                                class="tb-label tb-label-star">{{ __('subscriptions::subscription.max_' . $key . '_' . $roles[$subscription['role_id']]) }}</label>
                                            <input type="number" min="0"
                                                class="form-control @error('subscription.credit_limits.' . $key) tk-invalid @enderror "
                                                wire:model="subscription.credit_limits.{{ $key }}" required
                                                placeholder="{{ __('subscriptions::subscription.max_' . $key . '_' . $roles[$subscription['role_id']] . '_placeholder') }}">
                                            @error('subscription.credit_limits.' . $key)
                                                <div class="tk-errormsg">
                                                    <span>{{ $message }}</span>
                                                </div>
                                            @enderror
                                        </div>
                                    @endforeach
                                @endif
                                @if(count($subscription['revenue_share']) > 0)
                                    @foreach($subscription['revenue_share'] as $key => $value)
                                        <div class="form-group" wire:key="revenue_pool-{{ $key }}">
                                            <label
                                                class="tb-label tb-label-star">{{ __('subscriptions::subscription.' . $key) }}</label>
                                            <div class="tb-input-righticon">
                                                <a class="am-custom-tooltip">
                                                    <i class="icon-alert-circle"></i>
                                                    <span
                                                        class="am-tooltip-text">{{ __('subscriptions::subscription.' . $key . '_tooltip') }}</span>
                                                </a>
                                                <input type="number" min="0"
                                                    class="form-control @error('subscription.revenue_share.' . $key) tk-invalid @enderror "
                                                    wire:model="subscription.revenue_share.{{ $key }}" required
                                                    placeholder="{{ __('subscriptions::subscription.' . $key . '_placeholder') }}">
                                            </div>
                                            @error('subscription.revenue_share.' . $key)
                                                <div class="tk-errormsg">
                                                    <span>{{ $message }}</span>
                                                </div>
                                            @enderror
                                        </div>
                                    @endforeach
                                @endif
                                <div class="form-group">
                                    <label class="tb-label">{{ __('subscriptions::subscription.auto_renew') }}</label>
                                    <div class="tb-email-status">
                                        <span>{{ __('subscriptions::subscription.auto_renew_desc') }}</span>
                                        <div class="tb-switchbtn">
                                            <label for="tb-emailstatus" class="tb-textdes"><span
                                                    id="tb-textdes">{{ $subscription['auto_renew'] == 'yes' ? __('general.yes') : __('general.no') }}</span></label>
                                            <input class="tb-checkaction" data-field="auto_renew"
                                                wire:key="auto_renew-{{ time() }}" {{ $subscription['auto_renew'] == 'yes' ? 'checked' : '' }} type="checkbox" id="tb-emailstatus">
                                        </div>
                                    </div>
                                </div>
                                @if($editMode)
                                    <div class="form-group">
                                        <label class="tb-label">{{ __('general.status') }}:</label>
                                        <div class="tb-email-status">
                                            <span>{{__('subscriptions::subscription.status')}}</span>
                                            <div class="tb-switchbtn">
                                                <label for="tb-emailstatus" class="tb-textdes"><span
                                                        id="tb-textdes">{{ $subscription['status'] == 'active' ? __('general.active') : __('general.deactive') }}</span></label>
                                                <input class="tb-checkaction" data-field="status" {{ $subscription['status'] == 'active' ? 'checked' : '' }} type="checkbox"
                                                    id="tb-emailstatus">
                                            </div>
                                        </div>
                                        @error('status')
                                            <div class="tk-errormsg">
                                                <span>{{$message}}</span>
                                            </div>
                                        @enderror
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label
                                        class="tb-label tb-label-star">{{__('subscriptions::subscription.upload_image')}}</label>
                                    <div class="tb-uploadarea tb-uploadbartwo">
                                        <ul class="tb-uploadbar">
                                            <li wire:loading wire:target="package.image" style="display: none"
                                                class="tb-uploading">
                                                <span>{{ __('settings.uploading') }}</span>
                                            </li>
                                            @if (!empty($subscription['image']) && method_exists($subscription['image'], 'temporaryUrl'))
                                                <div wire:loading.remove class="tb-uploadel tb-upload-two">
                                                    <img src="{{ substr($subscription['image']->getMimeType(), 0, 5) == 'image' ? $subscription['image']->temporaryUrl() : asset('images/file-preview.png') }}"
                                                        alt="{{ $subscription['image']->getClientOriginalName() }}">
                                                    <span>{{$subscription['image']->getClientOriginalName()}} <a
                                                            href="javascript:void(0);" wire:click.prevent="removeImage"> <i
                                                                class="icon-trash-2"></i></a> </span>
                                                </div>
                                            @elseif(!empty($old_image))
                                                <div wire:loading.remove class="tb-uploadel tb-upload-two">
                                                    <img src="{{ Storage::url($old_image) }}"
                                                        alt="{{ $subscription['name'] }}">
                                                    <span>{{ $subscription['name'] }}<a href="javascript:void(0);"
                                                            wire:click.prevent="removeImage"> <i
                                                                class="icon-trash-2"></i></a></span>
                                                </div>
                                            @endif
                                        </ul>
                                        <span
                                            class="tb-upload-limit">{{ __('settings.image_option', ['extension' => join(',', $allowImageExt), 'size' => $allowImageSize . 'MB']) }}</span>
                                        <em>
                                            <label for="file2">
                                                <span>
                                                    <input id="file2" type="file"
                                                        accept="{{ !empty($allowImageExt) ? join(',', array_map(function ($ex) {
    return ('.' . $ex); }, $allowImageExt)) : '*' }}"
                                                        wire:model.lazy="subscription.image">
                                                    {{ __('settings.click_here_to_upload') }}
                                                </span>
                                            </label>
                                        </em>
                                    </div>
                                    @error('subscription.image')
                                        <div class="tk-errormsg">
                                            <span>{{$message}}</span>
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label class="tb-label">{{ __('subscriptions::subscription.description') }}</label>
                                    <textarea type="text" class="form-control" wire:model="subscription.description"
                                        placeholder="{{ __('subscriptions::subscription.description_placeholder') }}"></textarea>
                                </div>
                                <div class="form-group tb-dbtnarea">
                                    <a href="javascript:void(0);" wire:click.prevent="setSubscription" class="tb-btn"
                                        wire:loading.class="tb-btn_disable" wire:target="setSubscription">
                                        @if($editMode)
                                            {{ __('subscriptions::subscription.update_subscription') }}
                                        @else
                                            {{ __('subscriptions::subscription.add_subscription') }}
                                        @endif
                                    </a>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-12 tb-md-60">
            <div class="tb-dhb-mainheading">
                <h4> {{ __('subscriptions::subscription.subscriptions') }}</h4>
                <div class="tb-sortby">
                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">
                                <div class="tb-actionselect">
                                    <div class="form-group tb-actionselect">
                                        <a href="javascript:void(0)" wire:click="printUsersExcel"
                                            class="d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px; background:#1D6F42; color:#fff; border-radius:6px;">
                                            <i class="fa fa-file-excel" style="font-size:18px;"></i>
                                        </a>
                                    </div>
                                </div>
      
                                <div class="tb-actionselect">
                                    <div class="tb-select" wire:ignore>
                                        <select class="form-control am-select2" data-field="filters.role_id">
                                            <option value="">{{ __('subscriptions::subscription.both') }}</option>
                                            @foreach($roles as $role_id => $role_name)
                                                <option value="{{$role_id}}">
                                                    {{ setting('_lernen.' . $role_name . '_display_name') ?? __('subscriptions::subscription.' . $role_name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect">
                                    <div class="tb-select" wire:ignore>
                                        <select class="form-control am-select2" data-field="filters.sortby">
                                            <option value="asc">{{ __('general.asc')  }}</option>
                                            <option value="desc">{{ __('general.desc')  }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect">
                                    <div class="tb-select" wire:ignore>
                                        <select class="form-control am-select2" data-field="filters.perPage">
                                            @foreach($perPageOptions as $opt)
                                                <option value="{{$opt}}">{{$opt}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control"
                                        wire:model.live.debounce.500ms="filters.search" autocomplete="off"
                                        placeholder="{{ __('subscriptions::subscription.search_here') }}">
                                </div>
                                
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>

            <div class="tb-disputetable tb-pageslanguage">
                @if(!empty($subscriptions) && $subscriptions->count() > 0)
                    <table
                        class="table tb-table tb-dbholder @if(setting('_general.table_responsive') == 'yes') tb-table-responsive @endif">
                        <thead>
                            <tr>
                                <th>{{__('subscriptions::subscription.name')}}</th>
                                <th>{{__('subscriptions::subscription.price')}}</th>
                                <th>{{__('subscriptions::subscription.period')}}</th>
                                <th>{{__('subscriptions::subscription.for_role')}}</th>
                                <th>{{__('subscriptions::subscription.status')}}</th>
                                <th>{{__('subscriptions::subscription.actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subscriptions as $single)
                                <tr>
                                    <td data-label="{{ __('subscriptions::subscription.name') }}">
                                        <div class="tb-varification_userinfo">
                                            <strong class="tb-adminhead__img">
                                                @if (!empty($single->image) && Storage::disk(getStorageDisk())->exists($single?->image))
                                                    <img src="{{ resizedImage($single->image, 34, 34) }}" alt="{{$single->image}}" />
                                                @else
                                                    <img src="{{ resizedImage('placeholder.png', 34, 34) }}"
                                                        alt="{{ $single?->image }}" />
                                                @endif
                                            </strong>
                                            <span>{!! $single->name !!}</span>
                                        </div>
                                    </td>
                                    <td data-label="{{__('subscriptions::subscription.price')}}">
                                        <span>{!! formatAmount($single->price) !!}</span></td>
                                    <td data-label="{{__('subscriptions::subscription.price')}}">
                                        <span>{{ __('subscriptions::subscription.' . $single->period) }}</span></td>
                                    <td data-label="{{__('subscriptions::subscription.for_role')}}">
                                        <span>{{ __('subscriptions::subscription.' . $roles[$single->role_id]) }}</span></td>
                                    <td data-label="{{__('subscriptions::subscription.status')}}">
                                        <em
                                            class="tk-project-tag tk-{{ $single->status == 'active' ? 'active' : 'disabled' }}">{{ $single->status }}</em>
                                    </td>
                                    <td data-label="{{__('subscriptions::subscription.actions')}}">
                                        <ul class="tb-action-icon">
                                            <li> <a href="javascript:void(0);" wire:click="edit({{ $single->id }})"><i
                                                        class="icon-edit-3"></i></a> </li>
                                            <li> <a href="javascript:void(0);" wire:click="preview({{ $single->id }})"><i
                                                        class="icon-eye"></i></a> </li>
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $subscriptions->links('pagination.custom') }}
                @else
                    <x-no-record :image="asset('images/empty.png')" :title="__('general.no_record_title')" />
                @endif
            </div>
        </div>
    </div>
    </div>
    @if(!empty($subscriptionDetail))
        <div class="modal fade tb-taskdetailtitle ab-subscription-detail" tabindex="-1" role="dialog"
            id="subscription-modal">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="tb-popuptitle">
                        <h4>{{__('subscriptions::subscription.subscription_detail')}}</h4>
                        <a href="javascript:void(0);" class="close"><i class="ti-close" data-bs-dismiss="modal"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="tb-viewpackages-content">
                            @if(!empty($subscriptionDetail['image']) && Storage::disk(getStorageDisk())->exists($subscriptionDetail['image']))
                                <figure>
                                    <img src="{{ resizedImage($subscriptionDetail['image'], 50, 50) }}"
                                        alt="{{ $subscriptionDetail['name'] }}">
                                </figure>
                            @endif
                            <span>{!! formatAmount($subscriptionDetail['price']) !!}</span>
                            <div class="tb-img-description">
                                <span>Basic</span>
                                <a href="javascript:void(0);"
                                    class="tk-project-tag tk-{{ $subscriptionDetail['status'] == 'active' ? 'active' : 'disabled' }}">{{$subscriptionDetail['status']}}</a>
                            </div>
                            <p>{{$subscriptionDetail['description']}}</p>
                        </div>
                        <h4>{{$subscriptionDetail['name']}}</h4>
                        <ul class="tb-packege-list">
                            <li>
                                <div class="tb-view-pac-item">
                                    <span>{{__('subscriptions::subscription.period')}}</span>
                                    <h6>{{ __('subscriptions::subscription.' . $subscriptionDetail['period']) }}</h6>
                                </div>
                            </li>
                            <li>
                                <div class="tb-view-pac-item">
                                    <span>{{__('subscriptions::subscription.for_role')}}</span>
                                    <h6>{{ __('subscriptions::subscription.' . $roles[$subscriptionDetail['role_id']]) }}</h6>
                                </div>
                            </li>
                            @if($roles[$subscriptionDetail['role_id']] == 'tutor')
                                <li>
                                    <div class="tb-view-pac-item">
                                        <span>{{__('subscriptions::subscription.max_sessions_tutor')}}</span>
                                        <h6>{{ $subscriptionDetail['credit_limits']['sessions'] }}</h6>
                                    </div>
                                </li>
                                @if(\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))
                                    <li>
                                        <div class="tb-view-pac-item">
                                            <span>{{__('subscriptions::subscription.max_courses_tutor')}}</span>
                                            <h6>{{$subscriptionDetail['credit_limits']['courses']}}</h6>
                                        </div>
                                    </li>
                                @endif
                            @elseif($roles[$subscriptionDetail['role_id']] == 'student')
                                <li>
                                    <div class="tb-view-pac-item">
                                        <span>{{__('subscriptions::subscription.admin_share')}}</span>
                                        <h6>{{ $subscriptionDetail['revenue_share']['admin_share'] }}%</h6>
                                    </div>
                                </li>
                                <li>
                                    <div class="tb-view-pac-item">
                                        <span>{{__('subscriptions::subscription.max_sessions_student')}}</span>
                                        <h6>{{ $subscriptionDetail['credit_limits']['sessions'] }}</h6>
                                    </div>
                                </li>
                                @if(\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))
                                    <li>
                                        <div class="tb-view-pac-item">
                                            <span>{{__('subscriptions::subscription.max_courses_student')}}</span>
                                            <h6>{{$subscriptionDetail['credit_limits']['courses']}}</h6>
                                        </div>
                                    </li>
                                @endif
                            @endif
                            @if($roles[$subscriptionDetail['role_id']] == 'student')
                                <li>
                                    <div class="tb-view-pac-item">
                                        <span>{{__('subscriptions::subscription.sessions_share')}}</span>
                                        <h6>{{ $subscriptionDetail['revenue_share']['sessions_share'] }}%</h6>
                                    </div>
                                </li>
                                @if(\Nwidart\Modules\Facades\Module::has('courses') && \Nwidart\Modules\Facades\Module::isEnabled('courses'))
                                    <li>
                                        <div class="tb-view-pac-item">
                                            <span>{{__('subscriptions::subscription.courses_share')}}</span>
                                            <h6>{{ $subscriptionDetail['revenue_share']['courses_share'] }}%</h6>
                                        </div>
                                    </li>
                                @endif
                            @endif
                            <li>
                                <div class="tb-view-pac-item">
                                    <span>{{__('subscriptions::subscription.auto_renew')}}</span>
                                    <h6>{{ $subscriptionDetail['auto_renew'] == 'yes' ? __('general.yes') : __('general.no') }}
                                    </h6>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
</main>

@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function () {
            jQuery(document).on('click', '.tb-checkaction', function (event) {
                let _this = jQuery(this);
                let field = _this.data('field');
                let value = '';
                if (_this.is(':checked')) {
                    if (field == 'auto_renew') {
                        _this.parent().find('#tb-textdes').html("{{__('general.yes')}}");
                        value = 'yes';
                    } else {
                        _this.parent().find('#tb-textdes').html("{{__('general.active')}}");
                        value = 'active';
                    }
                } else {
                    if (field == 'auto_renew') {
                        _this.parent().find('#tb-textdes').html("{{__('general.no')}}");
                        value = 'no';
                    } else {
                        _this.parent().find('#tb-textdes').html("{{__('general.deactive')}}");
                        value = 'deactive';
                    }
                }
                @this.set(`subscription.${field}`, value, true);
            });

            jQuery(document).on('change', '.am-select2', function (event) {
                let _this = jQuery(this);
                let field = _this.data('field');
                let live = true;
                if (field == 'subscription.period') {
                    live = false;
                }
                @this.set(field, _this.val(), live);
            });

            jQuery(window).on('showSubscription', event => {
                setTimeout(() => {
                    var $target = jQuery('#subscription-modal').modal('show');
                }, 500);
            });
        });
    </script>
@endpush