<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create superadmin user
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('superadmin123'),
                'phone' => '+1234567890',
                'avatar' => 'https://ui-avatars.com/api/?name=Super+Admin&background=FF6B6B',
                'balance' => 1000000,
                'preferred_language' => 'en',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $superadmin->assignRole('superadmin');

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin123'),
                'phone' => '+1234567891',
                'avatar' => 'https://ui-avatars.com/api/?name=Admin+User&background=4ECDC4',
                'balance' => 500000,
                'preferred_language' => 'en',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole('admin');

        // Create demo customer users
        $customerNames = [
            'John Doe',
            'Jane Smith',
            'Mike Johnson',
            'Sarah Williams',
            'David Brown',
            'Emily Davis',
            'Robert Miller',
            'Lisa Anderson',
            'James Taylor',
            'Maria Garcia',
        ];

        foreach ($customerNames as $index => $name) {
            $customer = User::firstOrCreate(
                ['email' => 'customer' . ($index + 1) . '@example.com'],
                [
                    'name' => $name,
                    'password' => Hash::make('customer123'),
                    'phone' => fake()->phoneNumber(),
                    'avatar' => 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=random',
                    'balance' => fake()->numberBetween(10000, 500000),
                    'preferred_language' => fake()->randomElement(['en', 'vi', 'fr']),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );
            $customer->assignRole('customer');
        }

        // Create additional random customer users using factory
        User::factory(20)->create()->each(function (User $user) {
            $user->assignRole('customer');
        });

        echo "Users created successfully!\n";
    }
}
