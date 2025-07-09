<?php

namespace App\Http\Livewire\Admin\Settings\Payment;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class Fawaterk extends Component
{
    public $vendor_key = '';
    public $provider_key = '';
    public $production_mode = false;
    public $enabled = true;

    public function mount()
    {
        $this->production_mode = setting('fawaterk_production_mode') == '1';
        $this->enabled = setting('fawaterk_enabled') == '1';
    }

    public function save()
    {
        $settings = [];

        if (!empty($this->vendor_key)) {
            $settings['fawaterk_vendor_key'] = encrypt($this->vendor_key);
        }

        if (!empty($this->provider_key)) {
            $settings['fawaterk_provider_key'] = encrypt($this->provider_key);
        }

        $settings['fawaterk_production_mode'] = $this->production_mode ? '1' : '0';
        
        $settings['fawaterk_enabled'] = $this->enabled ? '1' : '0';

        foreach ($settings as $key => $value) {
            DB::table('optionbuilder__settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'section' => 'payment_settings']
            );
        }

        $this->dispatchBrowserEvent('showToast', [
            'message' => __('general.settings_updated')
        ]);

        $this->vendor_key = '';
        $this->provider_key = '';
    }

    public function render()
    {
        return view('livewire.admin.settings.payment.fawaterk-settings');
    }
}
