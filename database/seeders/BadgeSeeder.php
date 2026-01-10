<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    public function run(): void
    {
        $badges = [
            ['name' => 'Tân Thủ', 'name_en' => 'Newbie', 'description' => 'Hoàn thành đăng ký', 'description_en' => 'Complete registration', 'icon' => '/badges/newbie.png', 'requirement_type' => 'registration', 'requirement_value' => 1, 'rarity' => 'common'],
            ['name' => 'Người Nói Chuyện', 'name_en' => 'Chatterbox', 'description' => 'Gửi 1000 tin nhắn', 'description_en' => 'Send 1000 messages', 'icon' => '/badges/chatterbox.png', 'requirement_type' => 'messages', 'requirement_value' => 1000, 'rarity' => 'common'],
            ['name' => 'Người Nổi Tiếng', 'name_en' => 'Popular', 'description' => 'Có 100 người theo dõi', 'description_en' => 'Get 100 followers', 'icon' => '/badges/popular.png', 'requirement_type' => 'followers', 'requirement_value' => 100, 'rarity' => 'rare'],
            ['name' => 'Streamer Tài Năng', 'name_en' => 'Talented Streamer', 'description' => 'Livestream 50 giờ', 'description_en' => '50 hours of livestreaming', 'icon' => '/badges/streamer.png', 'requirement_type' => 'stream_hours', 'requirement_value' => 50, 'rarity' => 'rare'],
            ['name' => 'Ca Sĩ Xuất Sắc', 'name_en' => 'Excellent Singer', 'description' => 'Đạt 90+ điểm karaoke 10 lần', 'description_en' => 'Score 90+ in karaoke 10 times', 'icon' => '/badges/singer.png', 'requirement_type' => 'karaoke_high_score', 'requirement_value' => 10, 'rarity' => 'epic'],
            ['name' => 'Tay Chơi Huyền Thoại', 'name_en' => 'Legendary Gamer', 'description' => 'Thắng 100 trận game', 'description_en' => 'Win 100 games', 'icon' => '/badges/gamer.png', 'requirement_type' => 'game_wins', 'requirement_value' => 100, 'rarity' => 'legendary'],
            ['name' => 'Người Hào Phóng', 'name_en' => 'Generous', 'description' => 'Tặng 10,000 coins quà', 'description_en' => 'Send 10,000 coins worth of gifts', 'icon' => '/badges/generous.png', 'requirement_type' => 'gifts_sent', 'requirement_value' => 10000, 'rarity' => 'rare'],
            ['name' => 'VIP Đặc Biệt', 'name_en' => 'VIP Member', 'description' => 'Mua VIP lần đầu', 'description_en' => 'Purchase VIP for the first time', 'icon' => '/badges/vip.png', 'requirement_type' => 'vip_purchase', 'requirement_value' => 1, 'rarity' => 'epic'],
            ['name' => 'Hoàng Đế', 'name_en' => 'Emperor', 'description' => 'Đạt VIP 7', 'description_en' => 'Reach VIP level 7', 'icon' => '/badges/emperor.png', 'requirement_type' => 'vip_level', 'requirement_value' => 7, 'rarity' => 'legendary'],
            ['name' => 'Trùm Bảng Xếp Hạng', 'name_en' => 'Leaderboard King', 'description' => 'Top 1 bất kỳ bảng xếp hạng nào', 'description_en' => 'Reach #1 on any leaderboard', 'icon' => '/badges/rank1.png', 'requirement_type' => 'leaderboard_top', 'requirement_value' => 1, 'rarity' => 'legendary'],
            ['name' => 'Người Bạn Tốt', 'name_en' => 'Good Friend', 'description' => 'Có 50 bạn bè', 'description_en' => 'Have 50 friends', 'icon' => '/badges/friend.png', 'requirement_type' => 'friends', 'requirement_value' => 50, 'rarity' => 'common'],
            ['name' => 'Lãnh Đạo Guild', 'name_en' => 'Guild Leader', 'description' => 'Tạo một guild', 'description_en' => 'Create a guild', 'icon' => '/badges/guild-leader.png', 'requirement_type' => 'guild_created', 'requirement_value' => 1, 'rarity' => 'rare'],
            ['name' => 'Người Hoàn Thành Nhiệm Vụ', 'name_en' => 'Quest Master', 'description' => 'Hoàn thành 100 nhiệm vụ', 'description_en' => 'Complete 100 missions', 'icon' => '/badges/quest.png', 'requirement_type' => 'missions', 'requirement_value' => 100, 'rarity' => 'epic'],
            ['name' => 'Người Yêu Đầu Đời', 'name_en' => 'First Love', 'description' => 'Match đầu tiên', 'description_en' => 'Get your first match', 'icon' => '/badges/first-love.png', 'requirement_type' => 'matches', 'requirement_value' => 1, 'rarity' => 'common'],
            ['name' => 'Trái Tim Vàng', 'name_en' => 'Golden Heart', 'description' => 'Nhận 1000 like', 'description_en' => 'Receive 1000 likes', 'icon' => '/badges/likes.png', 'requirement_type' => 'likes_received', 'requirement_value' => 1000, 'rarity' => 'rare'],
            ['name' => 'Video Creator', 'name_en' => 'Video Creator', 'description' => 'Đăng 50 video', 'description_en' => 'Post 50 videos', 'icon' => '/badges/video-creator.png', 'requirement_type' => 'videos', 'requirement_value' => 50, 'rarity' => 'rare'],
            ['name' => 'Sự Kiện Đặc Biệt', 'name_en' => 'Event Participant', 'description' => 'Tham gia 10 sự kiện', 'description_en' => 'Join 10 events', 'icon' => '/badges/event.png', 'requirement_type' => 'events', 'requirement_value' => 10, 'rarity' => 'epic'],
            ['name' => 'Ngày Đầu Tiên', 'name_en' => 'Day One', 'description' => 'Thành viên từ ngày đầu', 'description_en' => 'Member since day one', 'icon' => '/badges/day-one.png', 'requirement_type' => 'early_adopter', 'requirement_value' => 1, 'rarity' => 'legendary'],
            ['name' => 'Level 50', 'name_en' => 'Level 50', 'description' => 'Đạt level 50', 'description_en' => 'Reach level 50', 'icon' => '/badges/level50.png', 'requirement_type' => 'level', 'requirement_value' => 50, 'rarity' => 'epic'],
            ['name' => 'Level 99', 'name_en' => 'Level 99', 'description' => 'Đạt level tối đa', 'description_en' => 'Reach max level', 'icon' => '/badges/level99.png', 'requirement_type' => 'level', 'requirement_value' => 99, 'rarity' => 'legendary'],
        ];

        foreach ($badges as $badge) {
            Badge::create(array_merge($badge, [
                'is_active' => true,
                'color' => $this->getRarityColor($badge['rarity']),
            ]));
        }

        $this->command->info('✓ Created ' . count($badges) . ' achievement badges');
    }

    private function getRarityColor(string $rarity): string
    {
        return match($rarity) {
            'common' => '#9CA3AF',
            'rare' => '#3B82F6',
            'epic' => '#8B5CF6',
            'legendary' => '#F59E0B',
            default => '#6B7280',
        };
    }
}
