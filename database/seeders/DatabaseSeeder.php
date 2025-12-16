<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed in the correct order to respect dependencies

        // 1. Create roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // 2. Create users with roles
        $this->call(UserSeeder::class);

        // 3. Create categories for all types
        $this->call(CategorySeeder::class);

        // 4. Create services
        $this->call(ServiceSeeder::class);

        // 5. Create products
        $this->call(ProductSeeder::class);

        // 6. Create files
        $this->call(FileSeeder::class);

        // 7. Create courses with lessons
        $this->call(CourseSeeder::class);

        // 8. Create orders with order items
        $this->call(OrderSeeder::class);

        // 9. Create settings
        $this->call(SettingSeeder::class);

        echo "\n========================================\n";
        echo "Database seeding completed successfully!\n";
        echo "========================================\n\n";
        echo "Sample Credentials:\n";
        echo "  Superadmin Email: superadmin@example.com\n";
        echo "  Superadmin Password: superadmin123\n";
        echo "  Admin Email: admin@example.com\n";
        echo "  Admin Password: admin123\n";
        echo "  Customer Email: customer1@example.com (and customer2-10)\n";
        echo "  Customer Password: customer123\n\n";
        echo "Data Created:\n";
        echo "  - 3 Roles (superadmin, admin, customer)\n";
        echo "  - 1 Superadmin user\n";
        echo "  - 1 Admin user\n";
        echo "  - 30+ Customer users\n";
        echo "  - 20 Categories (5 service, 5 product, 4 file, 5 course)\n";
        echo "  - 22+ Services\n";
        echo "  - 25+ Products\n";
        echo "  - 17 Digital files\n";
        echo "  - 20+ Courses with lessons\n";
        echo "  - 60+ Sample orders with order items\n";
        echo "  - 50+ System settings\n";
        echo "========================================\n";
    }
}
