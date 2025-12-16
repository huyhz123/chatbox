<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\File;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = User::role('customer')->get();

        if ($customers->isEmpty()) {
            echo "No customer users found. Creating orders for first 10 customers...\n";
            return;
        }

        // Get available items
        $services = Service::active()->get();
        $products = Product::active()->get();
        $files = File::active()->get();
        $courses = Course::active()->get();

        foreach ($customers->take(20) as $customer) {
            // Create 2-4 orders per customer
            $orderCount = fake()->numberBetween(2, 4);

            for ($i = 0; $i < $orderCount; $i++) {
                $type = fake()->randomElement(['service', 'product', 'file', 'course']);
                $itemCount = fake()->numberBetween(1, 3);

                $subtotal = 0;
                $items = [];

                // Select items based on type
                for ($j = 0; $j < $itemCount; $j++) {
                    $item = null;
                    $quantity = 1;

                    if ($type === 'service' && !$services->isEmpty()) {
                        $item = $services->random();
                        $quantity = fake()->numberBetween($item->min_quantity, min($item->max_quantity, $item->min_quantity + 100));
                    } elseif ($type === 'product' && !$products->isEmpty()) {
                        $item = $products->random();
                        $quantity = fake()->numberBetween(1, 5);
                    } elseif ($type === 'file' && !$files->isEmpty()) {
                        $item = $files->random();
                        $quantity = 1;
                    } elseif ($type === 'course' && !$courses->isEmpty()) {
                        $item = $courses->random();
                        $quantity = 1;
                    }

                    if ($item) {
                        $price = $item->getCurrentPrice();
                        $cost = $item->cost ?? 0;
                        $total = $price * $quantity;

                        $items[] = [
                            'itemable_type' => get_class($item),
                            'itemable_id' => $item->id,
                            'item_name' => $item->name,
                            'sku' => $item->sku ?? null,
                            'quantity' => $quantity,
                            'price' => $price,
                            'cost' => $cost,
                            'total' => $total,
                            'options' => [],
                        ];

                        $subtotal += $total;
                    }
                }

                if (empty($items)) {
                    continue;
                }

                // Calculate totals
                $discount = fake()->randomElement([0, $subtotal * 0.1, $subtotal * 0.15]);
                $tax = ($subtotal - $discount) * 0.1;
                $total = $subtotal - $discount + $tax;

                // Create order
                $order = Order::create([
                    'order_number' => 'ORD-' . date('Ymd') . '-' . str()->random(6),
                    'user_id' => $customer->id,
                    'type' => $type,
                    'status' => fake()->randomElement(['pending', 'processing', 'completed', 'completed']),
                    'payment_status' => fake()->randomElement(['pending', 'paid', 'paid', 'paid']),
                    'payment_method' => fake()->randomElement(['credit_card', 'bank_transfer', 'wallet']),
                    'payment_gateway' => fake()->randomElement(['stripe', 'paypal', 'bank']),
                    'transaction_id' => fake()->uuid(),
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'currency' => 'VND',
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'customer_phone' => $customer->phone,
                    'customer_address' => fake()->address(),
                    'notes' => fake()->optional(0.2)->sentence(),
                    'ip_address' => fake()->ipv4(),
                    'paid_at' => fake()->randomElement([null, fake()->dateTimeBetween('-60 days')]),
                ]);

                // Add order items
                foreach ($items as $item) {
                    OrderItem::create(array_merge($item, ['order_id' => $order->id]));
                }
            }
        }

        echo "Orders created successfully!\n";
    }
}
