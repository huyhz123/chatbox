<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            [
                'name' => 'Cờ Caro',
                'type' => 'board',
                'min_players' => 2,
                'max_players' => 2,
                'rules' => ['grid_size' => 15, 'win_condition' => 5],
                'thumbnail' => '/games/caro.png',
            ],
            [
                'name' => 'Ludo',
                'type' => 'board',
                'min_players' => 2,
                'max_players' => 4,
                'rules' => ['board_size' => 4, 'tokens_per_player' => 4],
                'thumbnail' => '/games/ludo.png',
            ],
            [
                'name' => 'Poker',
                'type' => 'card',
                'min_players' => 2,
                'max_players' => 8,
                'rules' => ['deck_size' => 52, 'min_bet' => 100, 'max_bet' => 10000],
                'thumbnail' => '/games/poker.png',
            ],
            [
                'name' => 'Tài Xỉu',
                'type' => 'dice',
                'min_players' => 1,
                'max_players' => 100,
                'rules' => ['dice_count' => 3, 'min_bet' => 10, 'max_bet' => 50000],
                'thumbnail' => '/games/taixiu.png',
            ],
            [
                'name' => 'Xì Dách (Blackjack)',
                'type' => 'card',
                'min_players' => 1,
                'max_players' => 7,
                'rules' => ['target_score' => 21, 'min_bet' => 50, 'max_bet' => 5000],
                'thumbnail' => '/games/blackjack.png',
            ],
            [
                'name' => 'Quiz/Trivia',
                'type' => 'quiz',
                'min_players' => 1,
                'max_players' => 100,
                'rules' => ['questions_count' => 10, 'time_per_question' => 30, 'categories' => ['general', 'music', 'movies', 'sports']],
                'thumbnail' => '/games/quiz.png',
            ],
            [
                'name' => 'Vẽ & Đoán',
                'type' => 'drawing',
                'min_players' => 2,
                'max_players' => 8,
                'rules' => ['time_per_round' => 60, 'words_count' => 3],
                'thumbnail' => '/games/draw-guess.png',
            ],
            [
                'name' => 'Bầu Cua Tôm Cá',
                'type' => 'dice',
                'min_players' => 1,
                'max_players' => 100,
                'rules' => ['dice_count' => 3, 'symbols' => ['bau', 'cua', 'tom', 'ca', 'ga', 'nai'], 'min_bet' => 10, 'max_bet' => 10000],
                'thumbnail' => '/games/baucua.png',
            ],
            [
                'name' => 'Vòng Quay May Mắn',
                'type' => 'wheel',
                'min_players' => 1,
                'max_players' => 1,
                'rules' => ['segments' => 12, 'rewards' => [10, 20, 50, 100, 200, 500, 1000, 2000, 5000], 'spin_cost' => 100],
                'thumbnail' => '/games/lucky-wheel.png',
            ],
            [
                'name' => 'Cờ Vua',
                'type' => 'board',
                'min_players' => 2,
                'max_players' => 2,
                'rules' => ['board_size' => 8, 'time_limit' => 1800],
                'thumbnail' => '/games/chess.png',
            ],
            [
                'name' => 'Xếp Hình',
                'type' => 'puzzle',
                'min_players' => 1,
                'max_players' => 1,
                'rules' => ['pieces' => [9, 16, 25, 36, 49], 'time_limit' => 300],
                'thumbnail' => '/games/puzzle.png',
            ],
            [
                'name' => 'Đua Xe',
                'type' => 'racing',
                'min_players' => 1,
                'max_players' => 4,
                'rules' => ['laps' => 3, 'obstacles' => true],
                'thumbnail' => '/games/racing.png',
            ],
        ];

        foreach ($games as $game) {
            Game::create(array_merge($game, [
                'is_active' => true,
            ]));
        }

        $this->command->info('✓ Created ' . count($games) . ' mini games');
    }
}
