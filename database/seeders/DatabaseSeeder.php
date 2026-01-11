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
        echo "\n========================================\n";
        echo "MULTILINGUAL CHAT PLATFORM SEEDING\n";
        echo "========================================\n\n";

        // 1. Create roles and permissions first
        $this->call(RolePermissionSeeder::class);

        // 2. Create users with roles
        $this->call(UserSeeder::class);

        // 3. Create files (for file sharing)
        $this->call(FileSeeder::class);

        // 4. Create settings
        $this->call(SettingSeeder::class);

        // 5. Chat Platform Features
        $this->call(VipPackageSeeder::class);
        $this->call(GiftSeeder::class);
        $this->call(SongSeeder::class);
        $this->call(BadgeSeeder::class);
        $this->call(GameSeeder::class);
        $this->call(MissionSeeder::class);

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

        echo "Chat Platform Data Created:\n";
        echo "  - 3 Roles (superadmin, admin, user)\n";
        echo "  - 30+ Users\n";
        echo "  - 7 VIP Packages (Bronze → Emperor)\n";
        echo "  - 44 Gifts (Free, Basic, Special, VIP, Lucky)\n";
        echo "  - 60 Karaoke Songs (Vietnamese, English, K-Pop)\n";
        echo "  - 20 Achievement Badges (Common → Legendary)\n";
        echo "  - 12 Mini Games (Board, Card, Dice, Quiz, etc.)\n";
        echo "  - 24 Missions (10 Daily, 10 Weekly, 4 Event)\n";
        echo "  - Digital files for sharing\n";
        echo "  - System settings\n";
        echo "========================================\n";
    }
}
