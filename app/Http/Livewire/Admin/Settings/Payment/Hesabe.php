<?php

namespace App\Http\Livewire\Admin\Settings\Payment;

use App\Models\Setting;
use Livewire\Component;

class Hesabe extends Component
{
    public $enabled = false;
    public $merchant_code;
    public $access_code;
    public $secret_key;
    public $iv_key;
    public $production_mode = false;

    public function mount()
    {
        $this->enabled = (bool) setting('hesabe_enabled', false);
        $this->merchant_code = setting('hesabe_merchant_code');
        $this->access_code = setting('hesabe_access_code');
        $this->secret_key = setting('hesabe_secret_key');
        $this->iv_key = setting('hesabe_iv_key');
        $this->production_mode = (bool) setting('hesabe_production_mode', false);
    }

    public function save()
    {
        Setting::updateSettings([
            'hesabe_enabled' => $this->enabled,
            'hesabe_merchant_code' => $this->merchant_code,
            'hesabe_access_code' => $this->access_code,
            'hesabe_secret_key' => $this->secret_key,
            'hesabe_iv_key' => $this->iv_key,
            'hesabe_production_mode' => $this->production_mode,
        ]);

        $this->emit('refreshParent');
        $this->dispatchBrowserEvent('showToast', ['message' => __('general.settings_updated')]);
    }

    public function render()
    {
        return view('livewire.admin.settings.payment.hesabe');
    }
} 