<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-2">🎮 Game Center</h1>
        <p class="text-base-content/70">Play games and win rewards!</p>
      </div>

      <!-- Categories -->
      <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button
          v-for="category in categories"
          :key="category.value"
          class="btn btn-sm"
          :class="activeCategory === category.value ? 'btn-primary' : 'btn-ghost'"
          @click="activeCategory = category.value"
        >
          {{ category.icon }} {{ category.label }}
        </button>
      </div>

      <!-- Games Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6">
        <div
          v-for="game in filteredGames"
          :key="game.id"
          class="card bg-base-100 shadow-xl hover:shadow-2xl transition-all hover:-translate-y-1 cursor-pointer"
          @click="playGame(game)"
        >
          <figure class="relative">
            <div class="w-full h-40 bg-gradient-to-br flex items-center justify-center text-6xl" :style="{ background: game.gradient }">
              {{ game.icon }}
            </div>
            <div v-if="game.is_new" class="absolute top-2 right-2">
              <div class="badge badge-error font-bold">NEW</div>
            </div>
            <div v-if="game.players_online" class="absolute bottom-2 left-2">
              <div class="badge badge-sm bg-base-100/80 backdrop-blur gap-1">
                <div class="w-2 h-2 rounded-full bg-success animate-pulse"></div>
                {{ game.players_online }} playing
              </div>
            </div>
          </figure>
          <div class="card-body p-4">
            <h3 class="font-bold">{{ game.name }}</h3>
            <p class="text-xs text-base-content/70">{{ game.description }}</p>
            <div class="flex items-center justify-between mt-2">
              <div class="badge badge-sm badge-outline">{{ game.category }}</div>
              <div class="text-xs text-base-content/70">
                ⭐ {{ game.rating }}
              </div>
            </div>
            <button class="btn btn-primary btn-sm mt-2" @click.stop="playGame(game)">
              Play Now
            </button>
          </div>
        </div>
      </div>

      <!-- Leaderboard Section -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <h2 class="card-title">🏆 Today's Leaderboard</h2>
          <div class="overflow-x-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>Rank</th>
                  <th>Player</th>
                  <th>Game</th>
                  <th>Score</th>
                  <th>Reward</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(entry, index) in leaderboard" :key="index" class="hover">
                  <td>
                    <div class="flex items-center gap-2">
                      <span v-if="index === 0" class="text-2xl">🥇</span>
                      <span v-else-if="index === 1" class="text-2xl">🥈</span>
                      <span v-else-if="index === 2" class="text-2xl">🥉</span>
                      <span v-else class="font-bold">#{{ index + 1 }}</span>
                    </div>
                  </td>
                  <td>
                    <div class="flex items-center gap-2">
                      <div class="avatar">
                        <div class="w-8 rounded-full">
                          <img :src="entry.avatar" />
                        </div>
                      </div>
                      <span class="font-semibold">{{ entry.username }}</span>
                    </div>
                  </td>
                  <td>{{ entry.game }}</td>
                  <td class="font-bold text-primary">{{ formatNumber(entry.score) }}</td>
                  <td>
                    <div class="badge badge-warning">💰 {{ entry.reward }} coins</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useNotificationStore } from '@/stores/notification';
import Header from '@/components/common/Header.vue';

const notificationStore = useNotificationStore();

const activeCategory = ref('all');
const games = ref([]);
const leaderboard = ref([]);

const categories = [
  { label: 'All Games', value: 'all', icon: '🎮' },
  { label: 'Card', value: 'card', icon: '🃏' },
  { label: 'Board', value: 'board', icon: '🎲' },
  { label: 'Casual', value: 'casual', icon: '🎯' },
  { label: 'Puzzle', value: 'puzzle', icon: '🧩' },
];

const filteredGames = computed(() => {
  if (activeCategory.value === 'all') return games.value;
  return games.value.filter(game => game.category === activeCategory.value);
});

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};

const playGame = (game) => {
  notificationStore.info(`Loading ${game.name}...`);
  // TODO: Navigate to game room or open game modal
};

onMounted(() => {
  // TODO: Fetch games from API
  games.value = [
    {
      id: 1,
      name: 'Ludo King',
      icon: '🎲',
      description: 'Classic board game',
      category: 'board',
      gradient: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
      is_new: false,
      players_online: 234,
      rating: 4.5,
    },
    {
      id: 2,
      name: 'Poker',
      icon: '🃏',
      description: 'Texas Hold\'em',
      category: 'card',
      gradient: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
      is_new: false,
      players_online: 567,
      rating: 4.7,
    },
    {
      id: 3,
      name: 'Tai Xiu',
      icon: '🎰',
      description: 'Dice betting game',
      category: 'casual',
      gradient: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
      is_new: false,
      players_online: 892,
      rating: 4.3,
    },
    {
      id: 4,
      name: 'Uno',
      icon: '🎴',
      description: 'Popular card game',
      category: 'card',
      gradient: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
      is_new: true,
      players_online: 123,
      rating: 4.8,
    },
    {
      id: 5,
      name: 'Chess',
      icon: '♟️',
      description: 'Strategy board game',
      category: 'board',
      gradient: 'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
      is_new: false,
      players_online: 345,
      rating: 4.9,
    },
    {
      id: 6,
      name: 'Checkers',
      icon: '⚫',
      description: 'Classic checkers',
      category: 'board',
      gradient: 'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
      is_new: false,
      players_online: 89,
      rating: 4.2,
    },
    {
      id: 7,
      name: 'Slot Machine',
      icon: '🎰',
      description: 'Spin to win!',
      category: 'casual',
      gradient: 'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
      is_new: true,
      players_online: 456,
      rating: 4.4,
    },
    {
      id: 8,
      name: 'Puzzle Match',
      icon: '🧩',
      description: 'Match 3 puzzle',
      category: 'puzzle',
      gradient: 'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
      is_new: false,
      players_online: 234,
      rating: 4.6,
    },
  ];

  leaderboard.value = [
    { username: 'ProGamer', avatar: 'https://i.pravatar.cc/150?img=11', game: 'Poker', score: 125000, reward: 5000 },
    { username: 'LuckyDice', avatar: 'https://i.pravatar.cc/150?img=12', game: 'Tai Xiu', score: 98000, reward: 3000 },
    { username: 'ChessKing', avatar: 'https://i.pravatar.cc/150?img=13', game: 'Chess', score: 87500, reward: 2000 },
    { username: 'CardMaster', avatar: 'https://i.pravatar.cc/150?img=14', game: 'Uno', score: 76000, reward: 1000 },
    { username: 'BoardLord', avatar: 'https://i.pravatar.cc/150?img=15', game: 'Ludo King', score: 65000, reward: 800 },
  ];
});
</script>
