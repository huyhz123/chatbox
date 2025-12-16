<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Service categories
        $serviceCategories = [
            ['name' => 'Social Media Services', 'type' => 'service', 'description' => 'Boost your social media presence with our services'],
            ['name' => 'Instagram Services', 'type' => 'service', 'description' => 'Grow your Instagram followers and engagement'],
            ['name' => 'YouTube Services', 'type' => 'service', 'description' => 'Increase YouTube subscribers and views'],
            ['name' => 'TikTok Services', 'type' => 'service', 'description' => 'Boost TikTok followers and likes'],
            ['name' => 'Website Services', 'type' => 'service', 'description' => 'Web traffic and SEO services'],
        ];

        foreach ($serviceCategories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                array_merge($category, [
                    'slug' => str()->slug($category['name']),
                    'image' => 'https://picsum.photos/300/300?random=' . fake()->randomNumber(5),
                    'is_active' => true,
                    'order' => 1,
                ])
            );
        }

        // Product categories
        $productCategories = [
            ['name' => 'Electronics', 'description' => 'Electronic gadgets and devices'],
            ['name' => 'Clothing', 'description' => 'Fashion and apparel items'],
            ['name' => 'Books', 'description' => 'Books and reading materials'],
            ['name' => 'Home & Garden', 'description' => 'Home appliances and garden tools'],
            ['name' => 'Sports & Outdoors', 'description' => 'Sports equipment and outdoor gear'],
        ];

        foreach ($productCategories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'type' => 'product'],
                array_merge($category, [
                    'slug' => str()->slug($category['name']),
                    'image' => 'https://picsum.photos/300/300?random=' . fake()->randomNumber(5),
                    'is_active' => true,
                    'order' => 2,
                ])
            );
        }

        // File categories
        $fileCategories = [
            ['name' => 'Software', 'description' => 'Professional software and tools'],
            ['name' => 'Templates', 'description' => 'Design and business templates'],
            ['name' => 'E-books', 'description' => 'Digital books and guides'],
            ['name' => 'Graphics', 'description' => 'Design files and graphics'],
        ];

        foreach ($fileCategories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'type' => 'file'],
                array_merge($category, [
                    'slug' => str()->slug($category['name']),
                    'image' => 'https://picsum.photos/300/300?random=' . fake()->randomNumber(5),
                    'is_active' => true,
                    'order' => 3,
                ])
            );
        }

        // Course categories
        $courseCategories = [
            ['name' => 'Programming', 'description' => 'Learn programming languages and frameworks'],
            ['name' => 'Business', 'description' => 'Business and entrepreneurship courses'],
            ['name' => 'Design', 'description' => 'UI/UX and graphic design courses'],
            ['name' => 'Marketing', 'description' => 'Digital marketing and SEO courses'],
            ['name' => 'Languages', 'description' => 'Learn foreign languages online'],
        ];

        foreach ($courseCategories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name'], 'type' => 'course'],
                array_merge($category, [
                    'slug' => str()->slug($category['name']),
                    'image' => 'https://picsum.photos/300/300?random=' . fake()->randomNumber(5),
                    'is_active' => true,
                    'order' => 4,
                ])
            );
        }

        echo "Categories created successfully!\n";
    }
}
