<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shippingMethods = [
            [
                'name' => 'Standard Shipping',
                'description' => 'Delivery in 5-7 business days',
                'calculation_type' => 'fixed',
                'base_cost' => 5.00,
                'calculation_rules' => json_encode([]),
                'estimated_days_min' => 5,
                'estimated_days_max' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Express Shipping',
                'description' => 'Delivery in 2-3 business days',
                'calculation_type' => 'fixed',
                'base_cost' => 15.00,
                'calculation_rules' => json_encode([]),
                'estimated_days_min' => 2,
                'estimated_days_max' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Free Shipping',
                'description' => 'Free delivery on orders over $50',
                'calculation_type' => 'order_percentage',
                'base_cost' => 0,
                'calculation_rules' => json_encode([
                    'min_order_amount' => 50,
                    'percentage' => 0,
                ]),
                'estimated_days_min' => 7,
                'estimated_days_max' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Weight-based Shipping',
                'description' => 'Calculated based on package weight',
                'calculation_type' => 'weight_based',
                'base_cost' => 2.00,
                'calculation_rules' => json_encode([
                    'cost_per_kg' => 1.50,
                ]),
                'estimated_days_min' => 5,
                'estimated_days_max' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Next Day Delivery',
                'description' => 'Get it tomorrow!',
                'calculation_type' => 'fixed',
                'base_cost' => 25.00,
                'calculation_rules' => json_encode([]),
                'estimated_days_min' => 1,
                'estimated_days_max' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($shippingMethods as $method) {
            ShippingMethod::create($method);
        }

        $this->command->info('Shipping methods seeded successfully!');
    }
}
