<div>
    <x-admin.settings.card :title="__('settings.hesabe_title')" :description="__('settings.hesabe_description')">
        <div class="space-y-4">
            <x-admin.settings.toggle
                wire:model="enabled"
                :label="__('general.enabled')"
            />

            <x-admin.settings.input
                wire:model="merchant_code"
                type="text"
                :label="__('settings.hesabe_merchant_code')"
            />

            <x-admin.settings.input
                wire:model="access_code"
                type="text"
                :label="__('settings.hesabe_access_code')"
            />

            <x-admin.settings.input
                wire:model="secret_key"
                type="text"
                :label="__('settings.hesabe_secret_key')"
            />

            <x-admin.settings.input
                wire:model="iv_key"
                type="text"
                :label="__('settings.hesabe_iv_key')"
            />

            <x-admin.settings.toggle
                wire:model="production_mode"
                :label="__('settings.hesabe_production_mode')"
            />

            <div class="flex justify-end">
                <x-button wire:click="save">{{ __('general.save') }}</x-button>
            </div>
        </div>
    </x-admin.settings.card>
</div> 