<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $serviceCategory = Category::where('type', 'service')->first();

        $services = [
            [
                'name' => 'Instagram Followers Package',
                'description' => 'Get real and active Instagram followers',
                'price' => 99999,
                'cost' => 40000,
                'min_quantity' => 10,
                'max_quantity' => 5000,
            ],
            [
                'name' => 'Instagram Likes Service',
                'description' => 'Boost your Instagram post likes',
                'price' => 49999,
                'cost' => 20000,
                'min_quantity' => 50,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'YouTube Subscribers',
                'description' => 'Increase your YouTube channel subscribers',
                'price' => 149999,
                'cost' => 70000,
                'min_quantity' => 100,
                'max_quantity' => 50000,
            ],
            [
                'name' => 'YouTube Views',
                'description' => 'Get more views on your YouTube videos',
                'price' => 79999,
                'cost' => 35000,
                'min_quantity' => 500,
                'max_quantity' => 100000,
            ],
            [
                'name' => 'TikTok Followers',
                'description' => 'Fast TikTok followers delivery',
                'price' => 89999,
                'cost' => 45000,
                'min_quantity' => 100,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'TikTok Likes',
                'description' => 'Increase likes on your TikTok videos',
                'price' => 49999,
                'cost' => 20000,
                'min_quantity' => 500,
                'max_quantity' => 50000,
            ],
            [
                'name' => 'Website Traffic',
                'description' => 'Drive targeted traffic to your website',
                'price' => 199999,
                'cost' => 100000,
                'min_quantity' => 100,
                'max_quantity' => 50000,
            ],
            [
                'name' => 'Facebook Likes',
                'description' => 'Boost Facebook page likes',
                'price' => 69999,
                'cost' => 30000,
                'min_quantity' => 100,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'Facebook Followers',
                'description' => 'Increase Facebook followers',
                'price' => 99999,
                'cost' => 50000,
                'min_quantity' => 50,
                'max_quantity' => 5000,
            ],
            [
                'name' => 'Twitter Followers',
                'description' => 'Get Twitter followers instantly',
                'price' => 79999,
                'cost' => 40000,
                'min_quantity' => 100,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'LinkedIn Connections',
                'description' => 'Build your LinkedIn network',
                'price' => 109999,
                'cost' => 60000,
                'min_quantity' => 50,
                'max_quantity' => 5000,
            ],
            [
                'name' => 'Discord Members',
                'description' => 'Grow your Discord server',
                'price' => 59999,
                'cost' => 25000,
                'min_quantity' => 100,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'Spotify Plays',
                'description' => 'Increase your Spotify track plays',
                'price' => 89999,
                'cost' => 45000,
                'min_quantity' => 1000,
                'max_quantity' => 100000,
            ],
            [
                'name' => 'Twitch Followers',
                'description' => 'Grow your Twitch channel followers',
                'price' => 129999,
                'cost' => 70000,
                'min_quantity' => 100,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'Pinterest Followers',
                'description' => 'Increase Pinterest followers',
                'price' => 69999,
                'cost' => 30000,
                'min_quantity' => 100,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'Snapchat Followers',
                'description' => 'Boost Snapchat followers',
                'price' => 59999,
                'cost' => 25000,
                'min_quantity' => 100,
                'max_quantity' => 5000,
            ],
            [
                'name' => 'Reddit Upvotes',
                'description' => 'Get upvotes on Reddit posts',
                'price' => 39999,
                'cost' => 15000,
                'min_quantity' => 50,
                'max_quantity' => 1000,
            ],
            [
                'name' => 'Google Reviews',
                'description' => 'Boost your Google business reviews',
                'price' => 179999,
                'cost' => 90000,
                'min_quantity' => 10,
                'max_quantity' => 1000,
            ],
            [
                'name' => 'Email Subscribers',
                'description' => 'Grow your email list',
                'price' => 99999,
                'cost' => 50000,
                'min_quantity' => 100,
                'max_quantity' => 10000,
            ],
            [
                'name' => 'App Downloads',
                'description' => 'Increase app downloads on stores',
                'price' => 149999,
                'cost' => 75000,
                'min_quantity' => 100,
                'max_quantity' => 50000,
            ],
            [
                'name' => 'SEO Backlinks',
                'description' => 'Build quality backlinks for SEO',
                'price' => 249999,
                'cost' => 120000,
                'min_quantity' => 10,
                'max_quantity' => 500,
            ],
            [
                'name' => 'Video Marketing Package',
                'description' => 'Complete video marketing solution',
                'price' => 299999,
                'cost' => 150000,
                'min_quantity' => 1,
                'max_quantity' => 100,
            ],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                array_merge($service, [
                    'category_id' => $serviceCategory->id,
                    'slug' => str()->slug($service['name']),
                    'details' => fake()->paragraphs(2, true),
                    'image' => 'https://picsum.photos/400/300?random=' . fake()->randomNumber(5),
                    'images' => [
                        'https://picsum.photos/400/300?random=' . fake()->randomNumber(5),
                        'https://picsum.photos/400/300?random=' . fake()->randomNumber(5),
                    ],
                    'special_price' => fake()->randomElement([null, $service['price'] * 0.8]),
                    'api_provider' => 'smm_panel',
                    'api_service_id' => fake()->randomNumber(6),
                    'processing_time' => fake()->randomElement(['1 hour', '2 hours', '24 hours']),
                    'required_fields' => [
                        ['name' => 'target', 'label' => 'Target URL/Username', 'type' => 'text'],
                        ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
                    ],
                    'order' => fake()->randomNumber(2),
                    'is_active' => true,
                    'is_featured' => fake()->boolean(30),
                    'sold_count' => fake()->randomNumber(4),
                ])
            );
        }

        echo "Services created successfully!\n";
    }
}
