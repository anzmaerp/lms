<main class="tb-main tb-blogwrap" wire:init="loadData">
    <div class="row">
        @include('livewire.pages.admin.offline-payments.update')
        <div class="col-lg-8 col-md-12 tb-md-60">
            <div class="tb-dhb-mainheading">
                <h4>{{ __('admin/payment.offline_payment_methods') }}</h4>
                <div class="tb-sortby">
                    <form class="tb-themeform tb-displistform">
                        <fieldset>
                            <div class="tb-themeform__wrap">
                                <div class="tb-actionselect">
                                    <a href="javascript:;" class="tb-btn btnred {{ $selectedPayments ? '' : 'd-none' }}"
                                        @click="$wire.dispatch('showConfirm', { action : 'delete-payment' })">{{ __('general.delete_selected') }}</a>
                                </div>
                                <div class="tb-actionselect">
                                    <div class="tb-select" wire:ignore>
                                        <select class="am-select2" id="sortBy" data-searchable="false">
                                            <option value="asc">{{ __('general.asc') }}</option>
                                            <option value="desc" selected>{{ __('general.desc') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="tb-actionselect">
                                    <div class="tb-select" wire:ignore>
                                        <select class="form-control am-select2" id="perPage" data-searchable="false">
                                            @foreach ($per_page_opt as $opt)
                                                <option value="{{ $loop->index }}"
                                                    {{ $per_page == $loop->index ? 'selected' : '' }}>
                                                    {{ $opt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group tb-inputicon tb-inputheight">
                                    <i class="icon-search"></i>
                                    <input type="text" class="form-control" wire:model.live.debounce.500ms="search"
                                        autocomplete="off" placeholder="{{ __('general.search') }}">
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
            <div class="tb-disputetable tb-db-categoriestable">
                @if ($offline_payments->count() > 0)
                    <div class="tb-table-wrap">
                        <table class="table tb-table tb-dbholder">
                            <thead>
                                <tr>
                                    <th>
                                        <div class="tb-checkbox">
                                            <input id="checkAll" wire:model.lazy="selectAll" type="checkbox">
                                            <label for="checkAll">{{ __('admin/payment.payment_name') }}</label>
                                        </div>
                                    </th>
                                    <th>{{ __('general.description') }}</th>
                                    <th>{{ __('general.status') }}</th>
                                    <th>{{ __('general.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($offline_payments as $single)
                                    <tr>
                                        <td data-label="{{ __('admin/payment.payment_name') }}">
                                            <div class="tb-namewrapper">
                                                <div class="tb-checkbox">
                                                    <input id="payment_id{{ $single->id }}"
                                                        wire:model.lazy="selectedPayments" value="{{ $single->id }}"
                                                        type="checkbox">
                                                    <label for="payment_id{{ $single->id }}">
                                                        <span>
                                                            {{ $single->name }}
                                                        </span>
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('general.description') }}">
                                            <span>{!! $single->description !!}</span>
                                        </td>
                                        <td data-label="{{ __('general.status') }}">
                                            <div class="am-status-tag">
                                                <em
                                                    class="tk-project-tag tk-{{ $single->status == 'active' ? 'active' : 'disabled' }}">{{ $single->status }}</em>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('general.actions') }}">
                                            <ul class="tb-action-icon">
                                                <li><a href="javascript:void(0);"
                                                        wire:click="edit({{ $single->id }})"><i
                                                            class="icon-edit-3"></i></a></li>
                                                <li>
                                                    <a href="javascript:void(0);"
                                                        @click="$wire.dispatch('showConfirm', { id: {{ $single->id }}, action: 'delete-payment' })"
                                                        class="tb-delete">
                                                        <i class="icon-trash-2"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $offline_payments->links('pagination.custom') }}
                @else
                    <x-no-record :image="asset('images/empty.png')" :title="__('general.no_record_title')" />
                @endif
            </div>
        </div>
    </div>
</main>
@push('scripts')
    <script>
        document.addEventListener('livewire:navigated', function() {

            jQuery('#sortBy').on('change', function() {
                let sortByValue = jQuery(this).val();
                @this.set('sortby', sortByValue);
            });

            jQuery('#perPage').on('change', function() {
                let perPageValue = jQuery(this).val();
                @this.set('per_page', perPageValue);
            });

            jQuery('.am-select2').each((index, item) => {
                let _this = jQuery(item);
                searchable = _this.data('searchable');
                let params = {
                    dropdownCssClass: _this.data('class'),
                    placeholder: _this.data('placeholder'),
                    allowClear: true
                }
                if (!searchable) {
                    params['minimumResultsForSearch'] = Infinity;
                }
                _this.select2(params);

            });

        });
    </script>
@endpush
