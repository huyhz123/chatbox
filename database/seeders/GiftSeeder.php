<?php

namespace Database\Seeders;

use App\Models\Gift;
use Illuminate\Database\Seeder;

class GiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $gifts = [
            // FREE GIFTS (Daily limit)
            ['name' => 'Hoa Hồng', 'name_en' => 'Rose', 'price' => 0, 'category' => 'free', 'image' => '/gifts/rose.png', 'animation_url' => '/animations/rose.json', 'animation_type' => 'lottie', 'duration_ms' => 2000],
            ['name' => 'Trái Tim', 'name_en' => 'Heart', 'price' => 0, 'category' => 'free', 'image' => '/gifts/heart.png', 'animation_url' => '/animations/heart.json', 'animation_type' => 'lottie', 'duration_ms' => 2000],
            ['name' => 'Nụ Cười', 'name_en' => 'Smile', 'price' => 0, 'category' => 'free', 'image' => '/gifts/smile.png', 'animation_url' => '/animations/smile.json', 'animation_type' => 'lottie', 'duration_ms' => 1500],
            ['name' => 'Like', 'name_en' => 'Thumbs Up', 'price' => 0, 'category' => 'free', 'image' => '/gifts/thumbsup.png', 'animation_url' => '/animations/thumbsup.json', 'animation_type' => 'lottie', 'duration_ms' => 1500],

            // BASIC GIFTS (10-100 coins)
            ['name' => 'Hoa Tulip', 'name_en' => 'Tulip', 'price' => 10, 'category' => 'basic', 'image' => '/gifts/tulip.png', 'animation_url' => '/animations/tulip.json', 'animation_type' => 'lottie', 'duration_ms' => 2500],
            ['name' => 'Cà Phê', 'name_en' => 'Coffee', 'price' => 20, 'category' => 'basic', 'image' => '/gifts/coffee.png', 'animation_url' => '/animations/coffee.json', 'animation_type' => 'lottie', 'duration_ms' => 2500],
            ['name' => 'Bánh Ngọt', 'name_en' => 'Cake', 'price' => 30, 'category' => 'basic', 'image' => '/gifts/cake.png', 'animation_url' => '/animations/cake.json', 'animation_type' => 'lottie', 'duration_ms' => 2500],
            ['name' => 'Kem', 'name_en' => 'Ice Cream', 'price' => 25, 'category' => 'basic', 'image' => '/gifts/icecream.png', 'animation_url' => '/animations/icecream.json', 'animation_type' => 'lottie', 'duration_ms' => 2500],
            ['name' => 'Pizza', 'name_en' => 'Pizza', 'price' => 50, 'category' => 'basic', 'image' => '/gifts/pizza.png', 'animation_url' => '/animations/pizza.json', 'animation_type' => 'lottie', 'duration_ms' => 2500],
            ['name' => 'Gấu Bông', 'name_en' => 'Teddy Bear', 'price' => 80, 'category' => 'basic', 'image' => '/gifts/bear.png', 'animation_url' => '/animations/bear.json', 'animation_type' => 'lottie', 'duration_ms' => 3000],
            ['name' => 'Sô Cô La', 'name_en' => 'Chocolate', 'price' => 40, 'category' => 'basic', 'image' => '/gifts/chocolate.png', 'animation_url' => '/animations/chocolate.json', 'animation_type' => 'lottie', 'duration_ms' => 2500],
            ['name' => 'Bó Hoa', 'name_en' => 'Bouquet', 'price' => 100, 'category' => 'basic', 'image' => '/gifts/bouquet.png', 'animation_url' => '/animations/bouquet.json', 'animation_type' => 'lottie', 'duration_ms' => 3000],

            // SPECIAL GIFTS (500-5,000 coins)
            ['name' => 'Nhẫn Kim Cương', 'name_en' => 'Diamond Ring', 'price' => 500, 'category' => 'special', 'image' => '/gifts/ring.png', 'animation_url' => '/animations/ring.json', 'animation_type' => 'lottie', 'duration_ms' => 4000],
            ['name' => 'Vương Miện', 'name_en' => 'Crown', 'price' => 800, 'category' => 'special', 'image' => '/gifts/crown.png', 'animation_url' => '/animations/crown.json', 'animation_type' => 'lottie', 'duration_ms' => 4000],
            ['name' => 'Xe Máy', 'name_en' => 'Motorcycle', 'price' => 1000, 'category' => 'special', 'image' => '/gifts/motorcycle.png', 'animation_url' => '/animations/motorcycle.json', 'animation_type' => 'lottie', 'duration_ms' => 5000],
            ['name' => 'Ô Tô', 'name_en' => 'Car', 'price' => 2000, 'category' => 'special', 'image' => '/gifts/car.png', 'animation_url' => '/animations/car.json', 'animation_type' => 'lottie', 'duration_ms' => 5000],
            ['name' => 'Hoa Anh Đào', 'name_en' => 'Cherry Blossom', 'price' => 1500, 'category' => 'special', 'image' => '/gifts/sakura.png', 'animation_url' => '/animations/sakura.json', 'animation_type' => 'lottie', 'duration_ms' => 4500],
            ['name' => 'Thiên Nga', 'name_en' => 'Swan', 'price' => 1800, 'category' => 'special', 'image' => '/gifts/swan.png', 'animation_url' => '/animations/swan.json', 'animation_type' => 'lottie', 'duration_ms' => 4500],
            ['name' => 'Pháo Hoa', 'name_en' => 'Fireworks', 'price' => 3000, 'category' => 'special', 'image' => '/gifts/fireworks.png', 'animation_url' => '/animations/fireworks.json', 'animation_type' => 'lottie', 'duration_ms' => 6000],
            ['name' => 'Bướm Bay', 'name_en' => 'Butterfly', 'price' => 1200, 'category' => 'special', 'image' => '/gifts/butterfly.png', 'animation_url' => '/animations/butterfly.json', 'animation_type' => 'lottie', 'duration_ms' => 4000],
            ['name' => 'Thiên Thần', 'name_en' => 'Angel', 'price' => 2500, 'category' => 'special', 'image' => '/gifts/angel.png', 'animation_url' => '/animations/angel.json', 'animation_type' => 'lottie', 'duration_ms' => 5000],
            ['name' => 'Cầu Vồng', 'name_en' => 'Rainbow', 'price' => 2200, 'category' => 'special', 'image' => '/gifts/rainbow.png', 'animation_url' => '/animations/rainbow.json', 'animation_type' => 'lottie', 'duration_ms' => 5000],
            ['name' => 'Mặt Trời', 'name_en' => 'Sun', 'price' => 1800, 'category' => 'special', 'image' => '/gifts/sun.png', 'animation_url' => '/animations/sun.json', 'animation_type' => 'lottie', 'duration_ms' => 4500],
            ['name' => 'Mặt Trăng', 'name_en' => 'Moon', 'price' => 1800, 'category' => 'special', 'image' => '/gifts/moon.png', 'animation_url' => '/animations/moon.json', 'animation_type' => 'lottie', 'duration_ms' => 4500],
            ['name' => 'Ngôi Sao', 'name_en' => 'Star', 'price' => 1600, 'category' => 'special', 'image' => '/gifts/star.png', 'animation_url' => '/animations/star.json', 'animation_type' => 'lottie', 'duration_ms' => 4000],
            ['name' => 'Bóng Bay', 'name_en' => 'Balloons', 'price' => 900, 'category' => 'special', 'image' => '/gifts/balloons.png', 'animation_url' => '/animations/balloons.json', 'animation_type' => 'lottie', 'duration_ms' => 4000],
            ['name' => 'Đàn Piano', 'name_en' => 'Piano', 'price' => 3500, 'category' => 'special', 'image' => '/gifts/piano.png', 'animation_url' => '/animations/piano.json', 'animation_type' => 'lottie', 'duration_ms' => 5500],

            // VIP GIFTS (10,000-100,000 coins)
            ['name' => 'Du Thuyền', 'name_en' => 'Yacht', 'price' => 10000, 'category' => 'vip', 'image' => '/gifts/yacht.png', 'animation_url' => '/animations/yacht.json', 'animation_type' => 'lottie', 'duration_ms' => 7000],
            ['name' => 'Biệt Thự', 'name_en' => 'Mansion', 'price' => 20000, 'category' => 'vip', 'image' => '/gifts/mansion.png', 'animation_url' => '/animations/mansion.json', 'animation_type' => 'lottie', 'duration_ms' => 8000],
            ['name' => 'Máy Bay Riêng', 'name_en' => 'Private Jet', 'price' => 50000, 'category' => 'vip', 'image' => '/gifts/jet.png', 'animation_url' => '/animations/jet.json', 'animation_type' => 'lottie', 'duration_ms' => 10000],
            ['name' => 'Đảo Riêng', 'name_en' => 'Private Island', 'price' => 100000, 'category' => 'vip', 'image' => '/gifts/island.png', 'animation_url' => '/animations/island.json', 'animation_type' => 'lottie', 'duration_ms' => 12000],
            ['name' => 'Rồng Bay', 'name_en' => 'Flying Dragon', 'price' => 80000, 'category' => 'vip', 'image' => '/gifts/dragon.png', 'animation_url' => '/animations/dragon.json', 'animation_type' => 'lottie', 'duration_ms' => 10000],
            ['name' => 'Phượng Hoàng', 'name_en' => 'Phoenix', 'price' => 80000, 'category' => 'vip', 'image' => '/gifts/phoenix.png', 'animation_url' => '/animations/phoenix.json', 'animation_type' => 'lottie', 'duration_ms' => 10000],
            ['name' => 'Lâu Đài', 'name_en' => 'Castle', 'price' => 60000, 'category' => 'vip', 'image' => '/gifts/castle.png', 'animation_url' => '/animations/castle.json', 'animation_type' => 'lottie', 'duration_ms' => 9000],
            ['name' => 'Siêu Xe', 'name_en' => 'Supercar', 'price' => 30000, 'category' => 'vip', 'image' => '/gifts/supercar.png', 'animation_url' => '/animations/supercar.json', 'animation_type' => 'lottie', 'duration_ms' => 8000],
            ['name' => 'Tên Lửa', 'name_en' => 'Rocket', 'price' => 70000, 'category' => 'vip', 'image' => '/gifts/rocket.png', 'animation_url' => '/animations/rocket.json', 'animation_type' => 'lottie', 'duration_ms' => 9000],
            ['name' => 'Thiên Hà', 'name_en' => 'Galaxy', 'price' => 90000, 'category' => 'vip', 'image' => '/gifts/galaxy.png', 'animation_url' => '/animations/galaxy.json', 'animation_type' => 'lottie', 'duration_ms' => 11000],

            // LUCKY BOXES
            ['name' => 'Hộp May Mắn Đồng', 'name_en' => 'Bronze Lucky Box', 'price' => 100, 'category' => 'lucky', 'image' => '/gifts/box-bronze.png', 'animation_url' => '/animations/box-bronze.json', 'animation_type' => 'lottie', 'duration_ms' => 3000],
            ['name' => 'Hộp May Mắn Bạc', 'name_en' => 'Silver Lucky Box', 'price' => 500, 'category' => 'lucky', 'image' => '/gifts/box-silver.png', 'animation_url' => '/animations/box-silver.json', 'animation_type' => 'lottie', 'duration_ms' => 3500],
            ['name' => 'Hộp May Mắn Vàng', 'name_en' => 'Gold Lucky Box', 'price' => 1000, 'category' => 'lucky', 'image' => '/gifts/box-gold.png', 'animation_url' => '/animations/box-gold.json', 'animation_type' => 'lottie', 'duration_ms' => 4000],
            ['name' => 'Hộp May Mắn Kim Cương', 'name_en' => 'Diamond Lucky Box', 'price' => 5000, 'category' => 'lucky', 'image' => '/gifts/box-diamond.png', 'animation_url' => '/animations/box-diamond.json', 'animation_type' => 'lottie', 'duration_ms' => 5000],

            // SEASONAL/SPECIAL
            ['name' => 'Cây Thông Noel', 'name_en' => 'Christmas Tree', 'price' => 2000, 'category' => 'special', 'image' => '/gifts/christmas-tree.png', 'animation_url' => '/animations/christmas-tree.json', 'animation_type' => 'lottie', 'duration_ms' => 5000],
            ['name' => 'Trái Tim Tình Yêu', 'name_en' => 'Love Heart', 'price' => 1400, 'category' => 'special', 'image' => '/gifts/love-heart.png', 'animation_url' => '/animations/love-heart.json', 'animation_type' => 'lottie', 'duration_ms' => 4500],
            ['name' => 'Hoa Sen', 'name_en' => 'Lotus', 'price' => 1600, 'category' => 'special', 'image' => '/gifts/lotus.png', 'animation_url' => '/animations/lotus.json', 'animation_type' => 'lottie', 'duration_ms' => 4500],
            ['name' => 'Gấu Panda', 'name_en' => 'Panda', 'price' => 1300, 'category' => 'special', 'image' => '/gifts/panda.png', 'animation_url' => '/animations/panda.json', 'animation_type' => 'lottie', 'duration_ms' => 4000],
            ['name' => 'Mèo Thần Tài', 'name_en' => 'Lucky Cat', 'price' => 888, 'category' => 'special', 'image' => '/gifts/lucky-cat.png', 'animation_url' => '/animations/lucky-cat.json', 'animation_type' => 'lottie', 'duration_ms' => 3500],
        ];

        $sortOrder = 1;
        foreach ($gifts as $gift) {
            Gift::create(array_merge($gift, [
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]));
        }

        $this->command->info('✓ Created ' . count($gifts) . ' gifts across all categories');
    }
}
