<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $productCategories = Category::where('type', 'product')->get();

        $products = [
            // Electronics
            [
                'name' => 'Wireless Bluetooth Headphones',
                'category_name' => 'Electronics',
                'price' => 1499999,
                'cost' => 700000,
                'stock' => 150,
                'description' => 'Premium wireless headphones with noise cancellation',
            ],
            [
                'name' => 'USB-C Fast Charger',
                'category_name' => 'Electronics',
                'price' => 499999,
                'cost' => 200000,
                'stock' => 300,
                'description' => 'High-speed USB-C charger compatible with all devices',
            ],
            [
                'name' => 'Smart Watch Pro',
                'category_name' => 'Electronics',
                'price' => 2999999,
                'cost' => 1500000,
                'stock' => 100,
                'description' => 'Advanced fitness and health tracking smartwatch',
            ],
            [
                'name' => 'Portable Power Bank',
                'category_name' => 'Electronics',
                'price' => 799999,
                'cost' => 350000,
                'stock' => 250,
                'description' => '20000mAh portable power bank with fast charging',
            ],
            [
                'name' => '4K Webcam',
                'category_name' => 'Electronics',
                'price' => 1999999,
                'cost' => 900000,
                'stock' => 75,
                'description' => 'Professional 4K webcam for streaming and recording',
            ],

            // Clothing
            [
                'name' => 'Premium Cotton T-Shirt',
                'category_name' => 'Clothing',
                'price' => 299999,
                'cost' => 100000,
                'stock' => 500,
                'description' => 'High-quality 100% cotton t-shirt',
            ],
            [
                'name' => 'Slim Fit Jeans',
                'category_name' => 'Clothing',
                'price' => 699999,
                'cost' => 250000,
                'stock' => 300,
                'description' => 'Comfortable slim fit denim jeans',
            ],
            [
                'name' => 'Winter Jacket',
                'category_name' => 'Clothing',
                'price' => 1899999,
                'cost' => 800000,
                'stock' => 100,
                'description' => 'Warm winter jacket with waterproof coating',
            ],
            [
                'name' => 'Sports Shoes',
                'category_name' => 'Clothing',
                'price' => 1299999,
                'cost' => 500000,
                'stock' => 200,
                'description' => 'Professional athletic shoes for running',
            ],
            [
                'name' => 'Casual Sneakers',
                'category_name' => 'Clothing',
                'price' => 899999,
                'cost' => 350000,
                'stock' => 250,
                'description' => 'Comfortable everyday sneakers',
            ],

            // Books
            [
                'name' => 'Learn Laravel Guide',
                'category_name' => 'Books',
                'price' => 199999,
                'cost' => 50000,
                'stock' => 1000,
                'description' => 'Comprehensive guide to Laravel framework',
            ],
            [
                'name' => 'Advanced PHP Development',
                'category_name' => 'Books',
                'price' => 299999,
                'cost' => 80000,
                'stock' => 800,
                'description' => 'Master advanced PHP concepts and techniques',
            ],
            [
                'name' => 'Web Design Best Practices',
                'category_name' => 'Books',
                'price' => 249999,
                'cost' => 60000,
                'stock' => 600,
                'description' => 'Modern web design principles and practices',
            ],
            [
                'name' => 'Digital Marketing Handbook',
                'category_name' => 'Books',
                'price' => 269999,
                'cost' => 70000,
                'stock' => 500,
                'description' => 'Complete guide to digital marketing strategies',
            ],

            // Home & Garden
            [
                'name' => 'Electric Kettle',
                'category_name' => 'Home & Garden',
                'price' => 449999,
                'cost' => 150000,
                'stock' => 200,
                'description' => 'Fast boiling electric kettle with auto-off',
            ],
            [
                'name' => 'Robot Vacuum Cleaner',
                'category_name' => 'Home & Garden',
                'price' => 3499999,
                'cost' => 1500000,
                'stock' => 40,
                'description' => 'Smart robot vacuum with app control',
            ],
            [
                'name' => 'LED Desk Lamp',
                'category_name' => 'Home & Garden',
                'price' => 599999,
                'cost' => 200000,
                'stock' => 150,
                'description' => 'Adjustable LED desk lamp with USB charging',
            ],
            [
                'name' => 'Stainless Steel Kitchen Knife Set',
                'category_name' => 'Home & Garden',
                'price' => 899999,
                'cost' => 350000,
                'stock' => 100,
                'description' => 'Professional 5-piece kitchen knife set',
            ],

            // Sports & Outdoors
            [
                'name' => 'Yoga Mat Premium',
                'category_name' => 'Sports & Outdoors',
                'price' => 599999,
                'cost' => 200000,
                'stock' => 300,
                'description' => 'Non-slip yoga mat with carrying strap',
            ],
            [
                'name' => 'Camping Tent 4-Person',
                'category_name' => 'Sports & Outdoors',
                'price' => 1599999,
                'cost' => 600000,
                'stock' => 80,
                'description' => 'Waterproof camping tent for 4 persons',
            ],
            [
                'name' => 'Mountain Bike',
                'category_name' => 'Sports & Outdoors',
                'price' => 4999999,
                'cost' => 2000000,
                'stock' => 30,
                'description' => '21-speed mountain bike with suspension',
            ],
            [
                'name' => 'Gym Dumbbell Set',
                'category_name' => 'Sports & Outdoors',
                'price' => 2299999,
                'cost' => 900000,
                'stock' => 60,
                'description' => 'Adjustable dumbbell set 2-20kg',
            ],
            [
                'name' => 'Swimming Goggles',
                'category_name' => 'Sports & Outdoors',
                'price' => 349999,
                'cost' => 100000,
                'stock' => 400,
                'description' => 'Comfortable swimming goggles with UV protection',
            ],
        ];

        foreach ($products as $product) {
            $category = $productCategories->firstWhere('name', $product['category_name']);
            if (!$category) {
                continue;
            }

            Product::firstOrCreate(
                ['name' => $product['name']],
                [
                    'category_id' => $category->id,
                    'slug' => str()->slug($product['name']),
                    'sku' => strtoupper(str()->random(8)),
                    'description' => $product['description'],
                    'details' => fake()->paragraphs(2, true),
                    'image' => 'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
                    'images' => [
                        'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
                        'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
                        'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
                    ],
                    'price' => $product['price'],
                    'cost' => $product['cost'],
                    'special_price' => fake()->randomElement([null, $product['price'] * 0.75, $product['price'] * 0.80]),
                    'stock' => $product['stock'],
                    'low_stock_alert' => 20,
                    'stock_status' => $product['stock'] > 0 ? 'in_stock' : 'out_of_stock',
                    'order' => fake()->randomNumber(2),
                    'is_active' => true,
                    'is_featured' => fake()->boolean(25),
                    'sold_count' => fake()->randomNumber(3),
                    'weight' => fake()->randomFloat(2, 0.5, 50),
                    'attributes' => [
                        'color' => fake()->randomElement(['Red', 'Blue', 'Green', 'Black', 'White', 'Gray']),
                        'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL', 'One Size']),
                    ],
                ]
            );
        }

        echo "Products created successfully!\n";
    }
}
