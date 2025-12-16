<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\File;
use Illuminate\Database\Seeder;

class FileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fileCategories = Category::where('type', 'file')->get();

        $files = [
            // Software
            [
                'name' => 'Laravel Starter Kit',
                'category_name' => 'Software',
                'price' => 299999,
                'file_type' => 'zip',
                'description' => 'Complete Laravel project starter kit with all dependencies',
            ],
            [
                'name' => 'PHP Web Framework Bundle',
                'category_name' => 'Software',
                'price' => 199999,
                'file_type' => 'zip',
                'description' => 'Bundle of popular PHP frameworks and tools',
            ],
            [
                'name' => 'Database Migration Tool',
                'category_name' => 'Software',
                'price' => 149999,
                'file_type' => 'exe',
                'description' => 'Professional database migration and backup tool',
            ],
            [
                'name' => 'Code Snippet Library',
                'category_name' => 'Software',
                'price' => 99999,
                'file_type' => 'zip',
                'description' => '1000+ useful code snippets for developers',
            ],
            [
                'name' => 'API Documentation Generator',
                'category_name' => 'Software',
                'price' => 249999,
                'file_type' => 'exe',
                'description' => 'Automatic API documentation generator tool',
            ],

            // Templates
            [
                'name' => 'Professional Business Website Template',
                'category_name' => 'Templates',
                'price' => 179999,
                'file_type' => 'zip',
                'description' => 'Responsive HTML/CSS website template for business',
            ],
            [
                'name' => 'E-Commerce Store Template',
                'category_name' => 'Templates',
                'price' => 249999,
                'file_type' => 'zip',
                'description' => 'Complete e-commerce website template',
            ],
            [
                'name' => 'Portfolio Website Template',
                'category_name' => 'Templates',
                'price' => 129999,
                'file_type' => 'zip',
                'description' => 'Modern portfolio showcase template',
            ],
            [
                'name' => 'Landing Page Template Pack',
                'category_name' => 'Templates',
                'price' => 99999,
                'file_type' => 'zip',
                'description' => '10 premium landing page templates',
            ],

            // E-books
            [
                'name' => 'Complete Web Development Guide PDF',
                'category_name' => 'E-books',
                'price' => 79999,
                'file_type' => 'pdf',
                'description' => 'Comprehensive guide to modern web development',
            ],
            [
                'name' => 'SEO Mastery E-Book',
                'category_name' => 'E-books',
                'price' => 89999,
                'file_type' => 'pdf',
                'description' => 'Master SEO techniques and strategies',
            ],
            [
                'name' => 'Freelance Success Blueprint',
                'category_name' => 'E-books',
                'price' => 69999,
                'file_type' => 'pdf',
                'description' => 'Complete guide to starting a freelance career',
            ],
            [
                'name' => 'Cloud Computing Basics',
                'category_name' => 'E-books',
                'price' => 84999,
                'file_type' => 'pdf',
                'description' => 'Introduction to cloud computing and services',
            ],

            // Graphics
            [
                'name' => 'Icon Pack Professional (5000+ icons)',
                'category_name' => 'Graphics',
                'price' => 129999,
                'file_type' => 'zip',
                'description' => 'Comprehensive icon pack for design projects',
            ],
            [
                'name' => 'Stock Vectors Bundle',
                'category_name' => 'Graphics',
                'price' => 149999,
                'file_type' => 'zip',
                'description' => '1000+ high-quality vector graphics',
            ],
            [
                'name' => 'UI Kit Pro',
                'category_name' => 'Graphics',
                'price' => 199999,
                'file_type' => 'zip',
                'description' => 'Complete UI kit for designers and developers',
            ],
        ];

        foreach ($files as $file) {
            $category = $fileCategories->firstWhere('name', $file['category_name']);
            if (!$category) {
                continue;
            }

            File::firstOrCreate(
                ['name' => $file['name']],
                [
                    'category_id' => $category->id,
                    'slug' => str()->slug($file['name']),
                    'description' => $file['description'],
                    'details' => fake()->paragraphs(2, true),
                    'image' => 'https://picsum.photos/400/400?random=' . fake()->randomNumber(5),
                    'file_path' => '/storage/files/' . str()->random(32) . '.' . $file['file_type'],
                    'file_type' => $file['file_type'],
                    'file_size' => fake()->numberBetween(5000000, 500000000),
                    'version' => fake()->numerify('v#.#.#'),
                    'price' => $file['price'],
                    'special_price' => fake()->randomElement([null, $file['price'] * 0.8, $file['price'] * 0.85]),
                    'download_limit' => fake()->randomElement([1, 5, 10, 50, null]),
                    'is_encrypted' => fake()->boolean(20),
                    'preview_url' => 'https://example.com/preview/' . str()->random(20),
                    'order' => fake()->randomNumber(2),
                    'is_active' => true,
                    'is_featured' => fake()->boolean(20),
                    'sold_count' => fake()->randomNumber(3),
                    'download_count' => fake()->randomNumber(3),
                ]
            );
        }

        echo "Files created successfully!\n";
    }
}
