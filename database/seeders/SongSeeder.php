<?php

namespace Database\Seeders;

use App\Models\Song;
use Illuminate\Database\Seeder;

class SongSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $songs = [
            // Vietnamese Pop Songs
            ['title' => 'Nơi Này Có Anh', 'artist' => 'Sơn Tùng M-TP', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 245],
            ['title' => 'Lạc Trôi', 'artist' => 'Sơn Tùng M-TP', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 234],
            ['title' => 'Chúng Ta Không Thuộc Về Nhau', 'artist' => 'Sơn Tùng M-TP', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 267],
            ['title' => 'Anh Ơi Ở Lại', 'artist' => 'Chi Pu', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 223],
            ['title' => 'Em Của Ngày Hôm Qua', 'artist' => 'Sơn Tùng M-TP', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 276],
            ['title' => 'Anh Sai Rồi', 'artist' => 'Sơn Tùng M-TP', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 254],
            ['title' => 'Bùa Yêu', 'artist' => 'Bích Phương', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 198],
            ['title' => 'Đi Đu Đưa Đi', 'artist' => 'Bích Phương', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 206],
            ['title' => 'Một Cú Lừa', 'artist' => 'Bích Phương', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 213],
            ['title' => 'Có Chàng Trai Viết Lên Cây', 'artist' => 'Phan Mạnh Quỳnh', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 267],
            ['title' => 'Cưới Thôi', 'artist' => 'Masew ft. Masiu', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 189],
            ['title' => 'Hẹn Yêu', 'artist' => 'Mỹ Tâm', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 245],
            ['title' => 'Làm Gì Phải Hốt', 'artist' => 'Đen ft. JustaTee', 'language' => 'vi', 'genre' => 'Rap', 'duration' => 234],
            ['title' => 'Hai Triệu Năm', 'artist' => 'Đen ft. Biên', 'language' => 'vi', 'genre' => 'Rap', 'duration' => 256],
            ['title' => 'Bài Này Chill Phết', 'artist' => 'Đen ft. MIN', 'language' => 'vi', 'genre' => 'Rap', 'duration' => 243],
            ['title' => 'Thanh Xuân', 'artist' => 'Da LAB', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 289],
            ['title' => 'Mượn Rượu Tỏ Tình', 'artist' => 'BigDaddy ft. Emily', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 267],
            ['title' => 'Yêu Là Tha Thu', 'artist' => 'OnlyC ft. Karik', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 234],
            ['title' => 'Hong Kong 1', 'artist' => 'Ngô Kiến Huy', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 223],
            ['title' => '3107', 'artist' => 'Noo Phước Thịnh ft. W/n', 'language' => 'vi', 'genre' => 'Pop', 'duration' => 256],

            // English Pop Songs
            ['title' => 'Shape of You', 'artist' => 'Ed Sheeran', 'language' => 'en', 'genre' => 'Pop', 'duration' => 234],
            ['title' => 'Blinding Lights', 'artist' => 'The Weeknd', 'language' => 'en', 'genre' => 'Pop', 'duration' => 200],
            ['title' => 'Someone Like You', 'artist' => 'Adele', 'language' => 'en', 'genre' => 'Ballad', 'duration' => 285],
            ['title' => 'Perfect', 'artist' => 'Ed Sheeran', 'language' => 'en', 'genre' => 'Ballad', 'duration' => 263],
            ['title' => 'Thinking Out Loud', 'artist' => 'Ed Sheeran', 'language' => 'en', 'genre' => 'Pop', 'duration' => 281],
            ['title' => 'All of Me', 'artist' => 'John Legend', 'language' => 'en', 'genre' => 'Ballad', 'duration' => 269],
            ['title' => 'Stay', 'artist' => 'Justin Bieber ft. The Kid LAROI', 'language' => 'en', 'genre' => 'Pop', 'duration' => 141],
            ['title' => 'As It Was', 'artist' => 'Harry Styles', 'language' => 'en', 'genre' => 'Pop', 'duration' => 167],
            ['title' => 'Anti-Hero', 'artist' => 'Taylor Swift', 'language' => 'en', 'genre' => 'Pop', 'duration' => 200],
            ['title' => 'Flowers', 'artist' => 'Miley Cyrus', 'language' => 'en', 'genre' => 'Pop', 'duration' => 200],
            ['title' => 'Die For You', 'artist' => 'The Weeknd', 'language' => 'en', 'genre' => 'Pop', 'duration' => 260],
            ['title' => 'Levitating', 'artist' => 'Dua Lipa', 'language' => 'en', 'genre' => 'Pop', 'duration' => 203],
            ['title' => 'Happier Than Ever', 'artist' => 'Billie Eilish', 'language' => 'en', 'genre' => 'Pop', 'duration' => 298],
            ['title' => 'Easy On Me', 'artist' => 'Adele', 'language' => 'en', 'genre' => 'Ballad', 'duration' => 224],
            ['title' => 'Heat Waves', 'artist' => 'Glass Animals', 'language' => 'en', 'genre' => 'Alternative', 'duration' => 238],
            ['title' => 'Starboy', 'artist' => 'The Weeknd ft. Daft Punk', 'language' => 'en', 'genre' => 'Pop', 'duration' => 230],
            ['title' => 'Bad Guy', 'artist' => 'Billie Eilish', 'language' => 'en', 'genre' => 'Pop', 'duration' => 194],
            ['title' => 'Watermelon Sugar', 'artist' => 'Harry Styles', 'language' => 'en', 'genre' => 'Pop', 'duration' => 174],
            ['title' => 'Señorita', 'artist' => 'Shawn Mendes ft. Camila Cabello', 'language' => 'en', 'genre' => 'Pop', 'duration' => 191],
            ['title' => 'Dance Monkey', 'artist' => 'Tones and I', 'language' => 'en', 'genre' => 'Pop', 'duration' => 210],

            // K-Pop Songs
            ['title' => 'Dynamite', 'artist' => 'BTS', 'language' => 'en', 'genre' => 'K-Pop', 'duration' => 199],
            ['title' => 'Butter', 'artist' => 'BTS', 'language' => 'en', 'genre' => 'K-Pop', 'duration' => 164],
            ['title' => 'How You Like That', 'artist' => 'BLACKPINK', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 182],
            ['title' => 'Kill This Love', 'artist' => 'BLACKPINK', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 193],
            ['title' => 'Gangnam Style', 'artist' => 'PSY', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 219],
            ['title' => 'Love Scenario', 'artist' => 'iKON', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 216],
            ['title' => 'Spring Day', 'artist' => 'BTS', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 237],
            ['title' => 'Fancy', 'artist' => 'TWICE', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 218],
            ['title' => 'Lovesick Girls', 'artist' => 'BLACKPINK', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 196],
            ['title' => 'Ddu-Du Ddu-Du', 'artist' => 'BLACKPINK', 'language' => 'ko', 'genre' => 'K-Pop', 'duration' => 209],

            // Vietnamese Ballad
            ['title' => 'Bạc Phận', 'artist' => 'Jack', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 287],
            ['title' => 'Hồng Nhan', 'artist' => 'Jack', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 254],
            ['title' => 'Sóng Gió', 'artist' => 'K-ICM ft. Jack', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 243],
            ['title' => 'Anh Đang Ở Đâu Đấy Anh', 'artist' => 'Hương Giang', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 312],
            ['title' => 'Yêu Một Người Có Lẽ', 'artist' => 'Lou Hoàng', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 278],
            ['title' => 'Buồn Của Anh', 'artist' => 'K-ICM ft. Đạt G', 'language' => 'vi', 'genre' => 'Ballad', 'duration' => 267],
        ];

        foreach ($songs as $song) {
            Song::create(array_merge($song, [
                'audio_url' => '/songs/audio/' . md5($song['title']) . '.mp3',
                'lyrics_url' => '/songs/lyrics/' . md5($song['title']) . '.lrc',
                'instrumental_url' => '/songs/instrumental/' . md5($song['title']) . '.mp3',
                'thumbnail' => '/songs/thumbnails/' . md5($song['title']) . '.jpg',
                'plays_count' => rand(100, 10000),
                'favorites_count' => rand(10, 1000),
                'is_active' => true,
            ]));
        }

        $this->command->info('✓ Created ' . count($songs) . ' karaoke songs');
    }
}
