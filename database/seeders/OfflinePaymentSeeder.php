<?php

namespace Database\Seeders;

use App\Models\OfflinePayment;
use Illuminate\Database\Seeder;

class OfflinePaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'name' => 'Bank Transfer',
                'description' => 'Pay via direct bank transfer',
                'instructions' => 'Please transfer the amount to our bank account and send us the receipt via email. Bank details: Bank Name: XYZ Bank, Account Number: 1234567890, IBAN: XYZ1234567890',
                'status' => 'active',
            ],
            [
                'name' => 'Check Payment',
                'description' => 'Pay via check',
                'instructions' => 'Please make your check payable to "Company Name" and send it to our address: 123 Business Street, City, Country. Your order will be processed once the check clears.',
                'status' => 'active',
            ],
            [
                'name' => 'Cash on Delivery',
                'description' => 'Pay when you receive the product',
                'instructions' => 'Pay in cash when you receive your products.',
                'status' => 'active',
            ],
        ];

        foreach ($paymentMethods as $method) {
            OfflinePayment::firstOrCreate(
                ['name' => $method['name']],
                [
                    'description' => $method['description'],
                    'instructions' => $method['instructions'],
                    'status' => $method['status'],
                ]
            );
        }
    }
} 