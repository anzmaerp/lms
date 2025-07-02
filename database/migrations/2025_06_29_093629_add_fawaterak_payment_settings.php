<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $settingsExist = DB::table('optionbuilder__settings')->where('key', 'fawaterk_enabled')->exists();

        if (!$settingsExist) {
            $settings = [
                ['key' => 'fawaterk_enabled', 'value' => '1', 'section' => 'payment_settings'],
                ['key' => 'fawaterk_vendor_key', 'value' => '', 'section' => 'payment_settings'],
                ['key' => 'fawaterk_provider_key', 'value' => '', 'section' => 'payment_settings'],
                ['key' => 'fawaterk_production_mode', 'value' => '0', 'section' => 'payment_settings'],
            ];

            DB::table('optionbuilder__settings')->insert($settings);
        }

        $adminSettings = DB::table('optionbuilder__settings')->where('key', 'admin_settings.payment_method')->first();
        if ($adminSettings) {
            $paymentMethods = json_decode($adminSettings->value, true) ?? [];

            if (!isset($paymentMethods['fawaterk'])) {
                $paymentMethods['fawaterk'] = ['status' => 'on'];
                DB::table('optionbuilder__settings')
                    ->where('key', 'admin_settings.payment_method')
                    ->update(['value' => json_encode($paymentMethods)]);
            }
        } else {
            DB::table('optionbuilder__settings')->insert([
                'key' => 'admin_settings.payment_method',
                'value' => json_encode(['fawaterk' => ['status' => 'on']]),
                'section' => 'payment_settings'
            ]);
        }
    }

    public function down(): void
    {
        DB::table('optionbuilder__settings')->where('key', 'like', 'fawaterk_%')->delete();

        $adminSettings = DB::table('optionbuilder__settings')->where('key', 'admin_settings.payment_method')->first();
        if ($adminSettings) {
            $paymentMethods = json_decode($adminSettings->value, true);
            unset($paymentMethods['fawaterk']);

            DB::table('optionbuilder__settings')
                ->where('key', 'admin_settings.payment_method')
                ->update(['value' => json_encode($paymentMethods)]);
        }
    }
};
