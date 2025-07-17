<main class="tb-main am-dispute-system am-purchased-system">
    <div class ="row">
        <div class="col-lg-12 col-md-12">
            <div class="tb-dhb-mainheading">
                <h4> {{ __('subscriptions::subscription.purchased_subscriptions') .' ('. $subscriptions->total() .')'}}</h4>
                <div class="tb-sortby">
                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">
                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="@this" class="am-select2 form-control" data-searchable="false" data-live='true' id="status" data-wiremodel="filters.status" >
                                            <option value="" {{ $filters['status'] ?? '' == '' ? 'selected' : '' }} >{{ __('subscriptions::subscription.all')  }}</option>
                                            <option value="active" {{ ($filters['status'] ?? '') == 'active' ? 'selected' : '' }} >{{ __('subscriptions::subscription.active')  }}</option>
                                            <option value="expired" {{ ($filters['status'] ?? '') == 'expired' ? 'selected' : '' }} >{{ __('subscriptions::subscription.expired')  }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="tb-actionselect" wire:ignore>
                                    <div class="tb-select">
                                        <select data-componentid="@this" class="am-select2 form-control" data-searchable="false" data-live='true' id="sort_by" data-wiremodel="filters.sortby" >
                                            <option value="asc" {{ $filters['sortby'] ?? '' == 'asc' ? 'selected' : '' }} >{{ __('general.asc')  }}</option>
                                            <option value="desc" {{ $filters['sortby'] ?? '' == 'desc' ? 'selected' : '' }} >{{ __('general.desc')  }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control" wire:model.live.debounce.500ms="filters.search"  autocomplete="off" placeholder="{{ __('general.search') }}">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
            <div class="am-disputelist_wrap">
                <div class="am-disputelist am-custom-scrollbar-y">
                    <table class="tb-table @if(setting('_general.table_responsive') == 'yes') tb-table-responsive @endif">
                        <thead>
                            <tr>
                                <th>{{ __('booking.id') }}</th>
                                <th>{{ __('booking.transaction_id') }}</th>
                                <th>{{ __('subscriptions::subscription.name') }}</th>
                                <th>{{ __('subscriptions::subscription.price') }}</th>
                                <th>{{ __('subscriptions::subscription.valid_till') }}</th>
                                <th>{{ __('subscriptions::subscription.credit_limits') }}</th>
                                <th>{{ __('subscriptions::subscription.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if( !$subscriptions->isEmpty() )
                                @foreach($subscriptions as $subscription)
                                    <tr>
                                        <td data-label="{{ __('booking.id') }}"><span>{{ $subscription?->order?->id }}</span></td>
                                        <td data-label="{{ __('booking.transaction_id') }}"><span>{{$subscription?->order?->transaction_id }}</span></td>
                                        <td data-label="{{ __('subscriptions::subscription.name' )}}">
                                            <div class="tb-varification_userinfo">
                                                <strong class="tb-adminhead__img">
                                                    @if (!empty($subscription->orderItem?->options['image']) && Storage::disk(getStorageDisk())->exists($subscription->orderItem?->options['image']))
                                                        <img src="{{ resizedImage($subscription->orderItem?->options['image'],34,34) }}" alt="{{ $subscription?->orderItem?->title }}" />
                                                    @else 
                                                        <img src="{{ resizedImage('placeholder.png',34,34) }}" alt="{{ $subscription?->orderItem?->title }}" />
                                                    @endif
                                                </strong>
                                                <span>
                                                    {!! $subscription?->orderItem?->title !!}
                                                    <small>{{ $subscription?->orderItem?->options['period'] }}</small>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('subscriptions::subscription.price' )}}">
                                            <span>{!! formatAmount($subscription?->orderItem?->price) !!}</span>
                                        </td>
                                        <td data-label="{{ __('subscriptions::subscription.valid_till' )}}">
                                            <span>{{ $subscription?->expires_at ? Carbon\Carbon::parse($subscription?->expires_at)->format(setting('_general.date_format') ?? 'd M Y') : '' }}</span>
                                        </td>
                                        <td data-label="{{ __('subscriptions::subscription.credit_limits' )}}">
                                            @if(!empty($subscription->credit_limits))
                                                @foreach($subscription->credit_limits as $key => $creditLimit)
                                                    <strong>{{ __('subscriptions::subscription.'.$key.'_quota') }}:</strong> {{ $creditLimit .' / '. $subscription?->remaining_credits[$key] ?? 0 }} {{ __('subscriptions::subscription.left') }} <br />
                                                @endforeach
                                            @endif
                                        </td>
                                        <td data-label="{{ __('subscriptions::subscription.status' )}}">
                                            <em class="tk-project-tag {{ $subscription?->status == 'active' ? 'tk-hourly-tag' : 'tk-fixed-tag' }}">{{ $subscription?->status}}</em>
                                        </td>

                                    </tr>
                                @endforeach
                            @endif    
                        </tbody>
                    </table>
                    @if($subscriptions->isEmpty())
                        <div class="am-disputelist-empty">
                            <x-no-record :image="asset('images/empty.png')" :title="__('general.no_record_title')" />
                        </div>
                    @endif
                </div>
                @if(!$subscriptions->isEmpty())
                    {{ $subscriptions->links('pagination.custom') }}
                @endif
            </div>
        </div>
    </div>
</main>