<?php

namespace Database\Seeders;

use App\Models\Mission;
use Illuminate\Database\Seeder;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        $missions = [
            // Daily Missions
            ['name' => 'Đăng nhập hàng ngày', 'name_en' => 'Daily Login', 'description' => 'Đăng nhập vào ứng dụng', 'type' => 'daily', 'category' => 'login', 'target_value' => 1, 'reward_coins' => 100, 'reward_exp' => 50],
            ['name' => 'Gửi tin nhắn', 'name_en' => 'Send Messages', 'description' => 'Gửi 50 tin nhắn', 'type' => 'daily', 'category' => 'messages', 'target_value' => 50, 'reward_coins' => 50, 'reward_exp' => 100],
            ['name' => 'Tặng quà', 'name_en' => 'Send Gifts', 'description' => 'Tặng 3 món quà', 'type' => 'daily', 'category' => 'gifts', 'target_value' => 3, 'reward_coins' => 200, 'reward_exp' => 150],
            ['name' => 'Tham gia phòng', 'name_en' => 'Join Rooms', 'description' => 'Tham gia 1 phòng chat', 'type' => 'daily', 'category' => 'rooms', 'target_value' => 1, 'reward_coins' => 100, 'reward_exp' => 80],
            ['name' => 'Xem livestream', 'name_en' => 'Watch Livestream', 'description' => 'Xem 1 livestream', 'type' => 'daily', 'category' => 'livestream', 'target_value' => 1, 'reward_coins' => 150, 'reward_exp' => 100],
            ['name' => 'Online 2 giờ', 'name_en' => 'Online 2 Hours', 'description' => 'Online ít nhất 2 giờ', 'type' => 'daily', 'category' => 'online_time', 'target_value' => 120, 'reward_coins' => 300, 'reward_exp' => 200],
            ['name' => 'Đăng bài viết', 'name_en' => 'Create Post', 'description' => 'Đăng 1 bài viết', 'type' => 'daily', 'category' => 'posts', 'target_value' => 1, 'reward_coins' => 80, 'reward_exp' => 60],
            ['name' => 'Like bài viết', 'name_en' => 'Like Posts', 'description' => 'Like 10 bài viết', 'type' => 'daily', 'category' => 'likes', 'target_value' => 10, 'reward_coins' => 50, 'reward_exp' => 40],
            ['name' => 'Bình luận', 'name_en' => 'Comment', 'description' => 'Bình luận 5 lần', 'type' => 'daily', 'category' => 'comments', 'target_value' => 5, 'reward_coins' => 60, 'reward_exp' => 50],
            ['name' => 'Chơi game', 'name_en' => 'Play Games', 'description' => 'Chơi 3 trận game', 'type' => 'daily', 'category' => 'games', 'target_value' => 3, 'reward_coins' => 150, 'reward_exp' => 100],

            // Weekly Missions
            ['name' => 'Hoàn thành nhiệm vụ hàng ngày', 'name_en' => 'Complete Daily Missions', 'description' => 'Hoàn thành 20 nhiệm vụ hàng ngày', 'type' => 'weekly', 'category' => 'missions', 'target_value' => 20, 'reward_coins' => 1000, 'reward_exp' => 500],
            ['name' => 'Gửi nhiều tin nhắn', 'name_en' => 'Send Many Messages', 'description' => 'Gửi 500 tin nhắn trong tuần', 'type' => 'weekly', 'category' => 'messages', 'target_value' => 500, 'reward_coins' => 500, 'reward_exp' => 300],
            ['name' => 'Kết bạn mới', 'name_en' => 'Make New Friends', 'description' => 'Kết bạn với 10 người mới', 'type' => 'weekly', 'category' => 'friends', 'target_value' => 10, 'reward_coins' => 800, 'reward_exp' => 400],
            ['name' => 'Tham gia nhiều phòng', 'name_en' => 'Join Many Rooms', 'description' => 'Tham gia 20 phòng chat', 'type' => 'weekly', 'category' => 'rooms', 'target_value' => 20, 'reward_coins' => 600, 'reward_exp' => 350],
            ['name' => 'Hát karaoke', 'name_en' => 'Sing Karaoke', 'description' => 'Hát 10 bài karaoke', 'type' => 'weekly', 'category' => 'karaoke', 'target_value' => 10, 'reward_coins' => 700, 'reward_exp' => 400],
            ['name' => 'Livestream', 'name_en' => 'Go Live', 'description' => 'Livestream ít nhất 3 lần', 'type' => 'weekly', 'category' => 'livestream', 'target_value' => 3, 'reward_coins' => 1500, 'reward_exp' => 800],
            ['name' => 'Đăng video', 'name_en' => 'Post Videos', 'description' => 'Đăng 5 video', 'type' => 'weekly', 'category' => 'videos', 'target_value' => 5, 'reward_coins' => 500, 'reward_exp' => 300],
            ['name' => 'Thắng game', 'name_en' => 'Win Games', 'description' => 'Thắng 20 trận game', 'type' => 'weekly', 'category' => 'game_wins', 'target_value' => 20, 'reward_coins' => 1000, 'reward_exp' => 600],
            ['name' => 'Tặng nhiều quà', 'name_en' => 'Send Many Gifts', 'description' => 'Tặng quà trị giá 5000 coins', 'type' => 'weekly', 'category' => 'gifts', 'target_value' => 5000, 'reward_coins' => 2000, 'reward_exp' => 1000],
            ['name' => 'Hoạt động tích cực', 'name_en' => 'Active Participation', 'description' => 'Online ít nhất 20 giờ trong tuần', 'type' => 'weekly', 'category' => 'online_time', 'target_value' => 1200, 'reward_coins' => 2000, 'reward_exp' => 1000],

            // Event Missions (Special)
            ['name' => 'Sự kiện Tết', 'name_en' => 'Lunar New Year Event', 'description' => 'Tham gia sự kiện Tết', 'type' => 'event', 'category' => 'special', 'target_value' => 1, 'reward_coins' => 5000, 'reward_exp' => 2000],
            ['name' => 'Sự kiện Valentine', 'name_en' => 'Valentine Event', 'description' => 'Tham gia sự kiện Valentine', 'type' => 'event', 'category' => 'special', 'target_value' => 1, 'reward_coins' => 3000, 'reward_exp' => 1500],
            ['name' => 'Sự kiện Giáng Sinh', 'name_en' => 'Christmas Event', 'description' => 'Tham gia sự kiện Giáng Sinh', 'type' => 'event', 'category' => 'special', 'target_value' => 1, 'reward_coins' => 5000, 'reward_exp' => 2000],
            ['name' => 'Sự kiện Sinh Nhật App', 'name_en' => 'App Birthday Event', 'description' => 'Tham gia sự kiện sinh nhật', 'type' => 'event', 'category' => 'special', 'target_value' => 1, 'reward_coins' => 10000, 'reward_exp' => 5000],
        ];

        foreach ($missions as $mission) {
            Mission::create(array_merge($mission, [
                'is_active' => true,
            ]));
        }

        $this->command->info('✓ Created ' . count($missions) . ' missions (Daily, Weekly, Event)');
    }
}
