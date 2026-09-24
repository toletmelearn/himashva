<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        PaymentGateway::updateOrCreate(['name' => 'razorpay'], [
            'display_name' => 'Razorpay',
            'driver' => 'razorpay',
            'credentials' => [],
            'is_active' => false,
            'is_test_mode' => true,
            'supported_methods' => ['upi', 'card', 'netbanking', 'wallet', 'emi'],
            'display_order' => 1,
        ]);

        PaymentGateway::updateOrCreate(['name' => 'cod'], [
            'display_name' => 'Cash on Delivery',
            'driver' => 'cod',
            'credentials' => [],
            'is_active' => true,
            'is_test_mode' => false,
            'supported_methods' => ['cod'],
            'display_order' => 10,
            'description' => 'Pay when you receive your order',
        ]);
    }
}
