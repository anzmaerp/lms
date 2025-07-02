<div class="col-lg-4 col-md-12 tb-md-40">
    <div class="tb-dbholder tb-packege-setting">
        <div class="tb-dbbox tb-dbboxtitle">
            <h5>{{ $editableId ? __('general.update_payment_method') : __('general.add_payment_method') }}</h5>
        </div>
        <div class="tb-dbbox">
            {{-- <form class="tb-themeform" wire:submit.prevent="{{ $editableId ? 'update' : 'store' }}"> --}}
            <form class="tk-themeform tk-form-blogcategories">
                <fieldset>
                    <div class="tb-themeform__wrap">
                        <div class="form-group">
                            <label class="tb-label">{{ __('admin/payment.payment_name') }}</label>
                            <input type="text" class="form-control @error('name') tk-invalid @enderror"
                                wire:model="name" placeholder="{{ __('admin/payment.name_placeholder') }}">
                            @error('name')
                                <div class="tk-errormsg">
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="tb-label">{{ __('general.description') }}</label>
                            <textarea class="form-control @error('description') tk-invalid @enderror" wire:model="description"
                                placeholder="{{ __('general.description_placeholder') }}"></textarea>
                            @error('description')
                                <div class="tk-errormsg">
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="tb-label">{{ __('admin/payment.payment_instructions') }}</label>
                            <textarea class="form-control @error('instructions') tk-invalid @enderror" wire:model="instructions"
                                placeholder="{{ __('admin/payment.instructions_placeholder') }}"></textarea>
                            @error('instructions')
                                <div class="tk-errormsg">
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="tb-label">{{ __('general.status') }}</label>
                            <div class="tb-select">
                                <select data-placeholder="{{ __('general.status') }}"
                                    class="form-control @error('status') tk-invalid @enderror" wire:model="status">
                                    <option value="active">{{ __('general.active') }}</option>
                                    <option value="inactive">{{ __('general.inactive') }}</option>
                                </select>
                            </div>
                            @error('status')
                                <div class="tk-errormsg">
                                    <span>{{ $message }}</span>
                                </div>
                            @enderror
                        </div>
                        <div class="form-group tb-dbtnarea">
                            <a href="javascript:void(0);" wire:click.prevent="update" class="tb-btn">
                                {{ $editableId ? __('general.update') : __('general.add') }}
                            </a>
                            @if ($editableId)
                                <a href="javascript:void(0);" wire:click.prevent="create" class="tb-dbbox">
                                    {{ __('general.cancel') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</div>
