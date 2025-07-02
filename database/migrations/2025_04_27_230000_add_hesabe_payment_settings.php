<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add Hesabe payment settings
        $settingsExist = DB::table('optionbuilder__settings')->where('key', 'hesabe_enabled')->exists();
        
        if (!$settingsExist) {
            $settings = [
                // Hesabe payment gateway status
                [
                    'key' => 'hesabe_enabled',
                    'value' => '1',
                    'section' => 'payment_settings'
                ],
                // Hesabe merchant code
                [
                    'key' => 'hesabe_merchant_code',
                    'value' => '109142025',
                    'section' => 'payment_settings'
                ],
                // Hesabe access code
                [
                    'key' => 'hesabe_access_code',
                    'value' => '0dce0280-727e-446c-9247-21b2d082df7c',
                    'section' => 'payment_settings'
                ],
                // Hesabe secret key
                [
                    'key' => 'hesabe_secret_key',
                    'value' => 'nDreP2JpOqEkaEN0zm7o0x9vXdWAGyL3',
                    'section' => 'payment_settings'
                ],
                // Hesabe IV key
                [
                    'key' => 'hesabe_iv_key',
                    'value' => 'OqEkaEN0zm7o0x9v',
                    'section' => 'payment_settings'
                ],
                // Hesabe production mode (off by default for testing)
                [
                    'key' => 'hesabe_production_mode',
                    'value' => '0',
                    'section' => 'payment_settings'
                ],
            ];
            
            DB::table('optionbuilder__settings')->insert($settings);
        }
        
        // Add Hesabe to available payment methods
        $adminSettings = DB::table('optionbuilder__settings')->where('key', 'admin_settings.payment_method')->first();
        
        if ($adminSettings) {
            $paymentMethods = json_decode($adminSettings->value, true) ?? [];
            
            if (!isset($paymentMethods['hesabe'])) {
                $paymentMethods['hesabe'] = [
                    'status' => 'on'
                ];
                
                DB::table('optionbuilder__settings')
                    ->where('key', 'admin_settings.payment_method')
                    ->update([
                        'value' => json_encode($paymentMethods)
                    ]);
            }
        } else {
            DB::table('optionbuilder__settings')->insert([
                'key' => 'admin_settings.payment_method',
                'value' => json_encode(['hesabe' => ['status' => 'on']]),
                'section' => 'payment_settings'
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove Hesabe payment settings
        DB::table('optionbuilder__settings')->where('key', 'like', 'hesabe_%')->delete();
        
        // Remove Hesabe from available payment methods
        $adminSettings = DB::table('optionbuilder__settings')->where('key', 'admin_settings.payment_method')->first();
        
        if ($adminSettings) {
            $paymentMethods = json_decode($adminSettings->value, true) ?? [];
            
            if (isset($paymentMethods['hesabe'])) {
                unset($paymentMethods['hesabe']);
                
                DB::table('optionbuilder__settings')
                    ->where('key', 'admin_settings.payment_method')
                    ->update([
                        'value' => json_encode($paymentMethods)
                    ]);
            }
        }
    }
}; 