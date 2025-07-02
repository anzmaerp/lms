<div>
    <x-admin.settings.card
        :title="__('settings.fawaterk_title')"
        :description="__('settings.fawaterk_description')"
    >
        <div class="space-y-4">
            <x-admin.settings.toggle
                wire:model="enabled"
                :label="__('general.enabled')"
            />

            <x-admin.settings.input
                wire:model.defer="vendor_key"
                type="password"
                :label="__('settings.fawaterk_vendor_key')"
            />

            <x-admin.settings.input
                wire:model.defer="provider_key"
                type="password"
                :label="__('settings.fawaterk_provider_key')"
            />

            <x-admin.settings.toggle
                wire:model="production_mode"
                :label="__('settings.fawaterk_production_mode')"
            />

            <div class="flex justify-end pt-4">
                <x-button wire:click="save">
                    {{ __('general.save') }}
                </x-button>
            </div>
        </div>
    </x-admin.settings.card>
</div>
