<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Course;
use App\Models\CourseLesson;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courseCategories = Category::where('type', 'course')->get();

        $courses = [
            // Programming
            [
                'name' => 'PHP for Beginners',
                'category_name' => 'Programming',
                'price' => 299999,
                'level' => 'beginner',
                'description' => 'Learn PHP from scratch - perfect for beginners',
                'lessons_count' => 12,
            ],
            [
                'name' => 'Laravel Masterclass',
                'category_name' => 'Programming',
                'price' => 499999,
                'level' => 'intermediate',
                'description' => 'Master Laravel framework with real-world projects',
                'lessons_count' => 25,
            ],
            [
                'name' => 'Advanced JavaScript & TypeScript',
                'category_name' => 'Programming',
                'price' => 399999,
                'level' => 'advanced',
                'description' => 'Master advanced JavaScript and TypeScript concepts',
                'lessons_count' => 20,
            ],
            [
                'name' => 'React.js Complete Guide',
                'category_name' => 'Programming',
                'price' => 449999,
                'level' => 'intermediate',
                'description' => 'Build modern web applications with React',
                'lessons_count' => 18,
            ],
            [
                'name' => 'Python for Data Science',
                'category_name' => 'Programming',
                'price' => 379999,
                'level' => 'intermediate',
                'description' => 'Data analysis and visualization with Python',
                'lessons_count' => 16,
            ],

            // Business
            [
                'name' => 'Entrepreneurship Fundamentals',
                'category_name' => 'Business',
                'price' => 249999,
                'level' => 'beginner',
                'description' => 'Start your business journey with proven strategies',
                'lessons_count' => 10,
            ],
            [
                'name' => 'Business Strategy & Leadership',
                'category_name' => 'Business',
                'price' => 349999,
                'level' => 'intermediate',
                'description' => 'Develop effective business strategies and leadership skills',
                'lessons_count' => 15,
            ],
            [
                'name' => 'Financial Management for Entrepreneurs',
                'category_name' => 'Business',
                'price' => 299999,
                'level' => 'intermediate',
                'description' => 'Manage finances effectively for your business',
                'lessons_count' => 12,
            ],

            // Design
            [
                'name' => 'UI/UX Design Fundamentals',
                'category_name' => 'Design',
                'price' => 279999,
                'level' => 'beginner',
                'description' => 'Learn UI/UX design principles and tools',
                'lessons_count' => 14,
            ],
            [
                'name' => 'Figma Design System Course',
                'category_name' => 'Design',
                'price' => 329999,
                'level' => 'intermediate',
                'description' => 'Master Figma for professional design work',
                'lessons_count' => 16,
            ],
            [
                'name' => 'Web Design Masterclass',
                'category_name' => 'Design',
                'price' => 399999,
                'level' => 'intermediate',
                'description' => 'Create stunning modern websites from scratch',
                'lessons_count' => 20,
            ],

            // Marketing
            [
                'name' => 'Digital Marketing Basics',
                'category_name' => 'Marketing',
                'price' => 229999,
                'level' => 'beginner',
                'description' => 'Introduction to digital marketing channels',
                'lessons_count' => 10,
            ],
            [
                'name' => 'SEO & Content Marketing',
                'category_name' => 'Marketing',
                'price' => 319999,
                'level' => 'intermediate',
                'description' => 'Master SEO and create compelling content',
                'lessons_count' => 14,
            ],
            [
                'name' => 'Social Media Marketing Strategy',
                'category_name' => 'Marketing',
                'price' => 279999,
                'level' => 'intermediate',
                'description' => 'Build effective social media marketing campaigns',
                'lessons_count' => 12,
            ],

            // Languages
            [
                'name' => 'English for Beginners',
                'category_name' => 'Languages',
                'price' => 199999,
                'level' => 'beginner',
                'description' => 'Learn English from basics to conversational level',
                'lessons_count' => 24,
            ],
            [
                'name' => 'Spanish Conversational Course',
                'category_name' => 'Languages',
                'price' => 249999,
                'level' => 'intermediate',
                'description' => 'Speak Spanish fluently in everyday situations',
                'lessons_count' => 20,
            ],
            [
                'name' => 'French Language Complete',
                'category_name' => 'Languages',
                'price' => 279999,
                'level' => 'beginner',
                'description' => 'Complete French language learning course',
                'lessons_count' => 22,
            ],
        ];

        foreach ($courses as $course) {
            $category = $courseCategories->firstWhere('name', $course['category_name']);
            if (!$category) {
                continue;
            }

            $createdCourse = Course::firstOrCreate(
                ['name' => $course['name']],
                [
                    'category_id' => $category->id,
                    'slug' => str()->slug($course['name']),
                    'description' => $course['description'],
                    'details' => fake()->paragraphs(3, true),
                    'image' => 'https://picsum.photos/600/400?random=' . fake()->randomNumber(5),
                    'price' => $course['price'],
                    'special_price' => fake()->randomElement([null, $course['price'] * 0.8]),
                    'level' => $course['level'],
                    'duration' => fake()->numberBetween(4, 24) . ' weeks',
                    'instructor_name' => fake()->name(),
                    'access_days' => fake()->randomElement([30, 60, 90, 180, 365, null]),
                    'certificate' => true,
                    'order' => fake()->randomNumber(2),
                    'is_active' => true,
                    'is_featured' => fake()->boolean(25),
                    'enrolled_count' => fake()->randomNumber(3),
                ]
            );

            // Create course lessons
            $lessonTitles = [
                'Introduction and Course Overview',
                'Getting Started and Setup',
                'Core Concepts and Fundamentals',
                'Practical Examples and Implementation',
                'Advanced Techniques and Best Practices',
                'Real-World Projects',
                'Troubleshooting and Debugging',
                'Performance Optimization',
                'Security and Best Practices',
                'Integration and Deployment',
                'Case Studies and Analysis',
                'Final Project',
                'Q&A and Support',
                'Advanced Topics Deep Dive',
                'Industry Standards and Tools',
                'Next Steps and Continuing Learning',
                'Recap and Key Takeaways',
                'Resources and Further Study',
                'Community and Networking',
                'Professional Development',
                'Hands-on Workshop',
                'Expert Interview',
                'Challenge Exercises',
                'Certification Prep',
            ];

            for ($i = 1; $i <= $course['lessons_count']; $i++) {
                $title = $lessonTitles[($i - 1) % count($lessonTitles)] . ' - Part ' . $i;
                CourseLesson::firstOrCreate(
                    [
                        'course_id' => $createdCourse->id,
                        'order' => $i,
                    ],
                    [
                        'title' => $title,
                        'description' => fake()->paragraph(),
                        'content' => fake()->paragraphs(5, true),
                        'video_url' => 'https://www.youtube.com/embed/' . str()->random(11),
                        'attachments' => [
                            ['name' => 'Lesson ' . $i . ' Notes', 'url' => '/storage/lessons/notes-' . $i . '.pdf'],
                            ['name' => 'Code Examples', 'url' => '/storage/lessons/code-' . $i . '.zip'],
                        ],
                        'duration' => fake()->numberBetween(15, 120) . ' minutes',
                        'is_free' => $i === 1, // First lesson is free
                        'is_active' => true,
                    ]
                );
            }
        }

        echo "Courses and lessons created successfully!\n";
    }
}
