<?php

namespace Database\Seeders;

use App\Models\Voucher;
use Illuminate\Database\Seeder;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vouchers = [
            [
                'code' => 'WELCOME10',
                'description' => 'Welcome discount - 10% off your first order',
                'type' => 'percentage',
                'discount_value' => 10,
                'min_order_amount' => 20,
                'max_discount_amount' => 50,
                'usage_limit' => 100,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'SAVE20',
                'description' => '$20 off on orders over $100',
                'type' => 'fixed',
                'discount_value' => 20,
                'min_order_amount' => 100,
                'max_discount_amount' => null,
                'usage_limit' => 50,
                'usage_limit_per_user' => 2,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(1),
                'is_active' => true,
            ],
            [
                'code' => 'BIGSALE30',
                'description' => 'Big Sale - 30% off (max $100 discount)',
                'type' => 'percentage',
                'discount_value' => 30,
                'min_order_amount' => 50,
                'max_discount_amount' => 100,
                'usage_limit' => 200,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addWeeks(2),
                'is_active' => true,
            ],
            [
                'code' => 'FREESHIP',
                'description' => 'Free shipping on all orders',
                'type' => 'fixed',
                'discount_value' => 5,
                'min_order_amount' => 30,
                'max_discount_amount' => null,
                'usage_limit' => null,
                'usage_limit_per_user' => 5,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'SEASONAL50',
                'description' => 'Seasonal sale - 50% off',
                'type' => 'percentage',
                'discount_value' => 50,
                'min_order_amount' => 100,
                'max_discount_amount' => 200,
                'usage_limit' => 500,
                'usage_limit_per_user' => 1,
                'used_count' => 0,
                'starts_at' => now(),
                'expires_at' => now()->addMonth(),
                'is_active' => true,
            ],
        ];

        foreach ($vouchers as $voucher) {
            Voucher::create($voucher);
        }

        $this->command->info('Vouchers seeded successfully!');
    }
}
