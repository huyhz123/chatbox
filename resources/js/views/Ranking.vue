<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold mb-2">🏆 Rankings</h1>
        <p class="text-base-content/70">See who's leading the community</p>
      </div>

      <!-- Tabs -->
      <div class="tabs tabs-boxed mb-6 bg-base-100 shadow-xl p-2 justify-center overflow-x-auto">
        <a
          v-for="tab in tabs"
          :key="tab.value"
          class="tab whitespace-nowrap"
          :class="{ 'tab-active': activeTab === tab.value }"
          @click="activeTab = tab.value"
        >
          {{ tab.icon }} {{ tab.label }}
        </a>
      </div>

      <!-- Time Filter -->
      <div class="flex justify-center gap-2 mb-6">
        <button
          v-for="period in periods"
          :key="period.value"
          class="btn btn-sm"
          :class="activePeriod === period.value ? 'btn-primary' : 'btn-ghost'"
          @click="activePeriod = period.value"
        >
          {{ period.label }}
        </button>
      </div>

      <!-- Top 3 Podium -->
      <div class="flex justify-center items-end gap-4 mb-8">
        <!-- 2nd Place -->
        <div v-if="rankings[1]" class="flex flex-col items-center">
          <div class="text-5xl mb-2">🥈</div>
          <div class="card bg-base-100 shadow-xl w-32">
            <figure class="pt-4">
              <div class="avatar">
                <div class="w-20 rounded-full ring ring-secondary ring-offset-base-100 ring-offset-4">
                  <img :src="rankings[1].avatar" />
                </div>
              </div>
            </figure>
            <div class="card-body p-4 text-center">
              <p class="font-bold text-sm truncate">{{ rankings[1].username }}</p>
              <p class="text-xs text-base-content/70">{{ formatNumber(rankings[1].value) }}</p>
            </div>
          </div>
        </div>

        <!-- 1st Place -->
        <div v-if="rankings[0]" class="flex flex-col items-center -mt-8">
          <div class="text-6xl mb-2 animate-bounce">🥇</div>
          <div class="card bg-gradient-to-br from-warning to-yellow-600 text-warning-content shadow-2xl w-36">
            <figure class="pt-4">
              <div class="avatar">
                <div class="w-24 rounded-full ring ring-warning ring-offset-base-100 ring-offset-4">
                  <img :src="rankings[0].avatar" />
                </div>
              </div>
            </figure>
            <div class="card-body p-4 text-center">
              <p class="font-bold truncate">{{ rankings[0].username }}</p>
              <p class="text-sm">{{ formatNumber(rankings[0].value) }}</p>
            </div>
          </div>
        </div>

        <!-- 3rd Place -->
        <div v-if="rankings[2]" class="flex flex-col items-center">
          <div class="text-5xl mb-2">🥉</div>
          <div class="card bg-base-100 shadow-xl w-32">
            <figure class="pt-4">
              <div class="avatar">
                <div class="w-20 rounded-full ring ring-accent ring-offset-base-100 ring-offset-4">
                  <img :src="rankings[2].avatar" />
                </div>
              </div>
            </figure>
            <div class="card-body p-4 text-center">
              <p class="font-bold text-sm truncate">{{ rankings[2].username }}</p>
              <p class="text-xs text-base-content/70">{{ formatNumber(rankings[2].value) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Rankings Table -->
      <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
          <div class="overflow-x-auto">
            <table class="table">
              <thead>
                <tr>
                  <th>Rank</th>
                  <th>User</th>
                  <th>{{ getValueLabel() }}</th>
                  <th>Change</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(user, index) in rankings.slice(3)"
                  :key="user.id"
                  class="hover"
                  :class="{ 'bg-primary/10': user.is_me }"
                >
                  <td class="font-bold text-lg">{{ index + 4 }}</td>
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar">
                        <div class="w-12 rounded-full">
                          <img :src="user.avatar" :alt="user.username" />
                        </div>
                      </div>
                      <div>
                        <div class="font-bold flex items-center gap-2">
                          {{ user.username }}
                          <span v-if="user.is_vip" class="badge badge-warning badge-xs">VIP {{ user.vip_level }}</span>
                          <span v-if="user.is_verified" class="badge badge-info badge-xs">✓</span>
                        </div>
                        <div class="text-sm text-base-content/70">Lv {{ user.level }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="font-bold text-primary">{{ formatNumber(user.value) }}</td>
                  <td>
                    <div v-if="user.change > 0" class="flex items-center gap-1 text-success">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>{{ user.change }}</span>
                    </div>
                    <div v-else-if="user.change < 0" class="flex items-center gap-1 text-error">
                      <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                      </svg>
                      <span>{{ Math.abs(user.change) }}</span>
                    </div>
                    <div v-else class="text-base-content/50">-</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- My Rank Card -->
      <div v-if="myRank" class="card bg-primary text-primary-content shadow-xl mt-6">
        <div class="card-body">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="text-3xl font-bold">#{{ myRank.rank }}</div>
              <div class="avatar">
                <div class="w-16 rounded-full ring ring-primary-content ring-offset-base-100 ring-offset-2">
                  <img :src="myRank.avatar" />
                </div>
              </div>
              <div>
                <p class="font-bold text-lg">Your Ranking</p>
                <p class="text-sm opacity-80">Keep going to reach the top!</p>
              </div>
            </div>
            <div class="text-right">
              <p class="text-3xl font-bold">{{ formatNumber(myRank.value) }}</p>
              <p class="text-sm opacity-80">{{ getValueLabel() }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Header from '@/components/common/Header.vue';

const activeTab = ref('level');
const activePeriod = ref('all');
const rankings = ref([]);
const myRank = ref(null);

const tabs = [
  { label: 'Level', value: 'level', icon: '⭐' },
  { label: 'Gifts Sent', value: 'gifts_sent', icon: '🎁' },
  { label: 'Gifts Received', value: 'gifts_received', icon: '💝' },
  { label: 'Followers', value: 'followers', icon: '👥' },
  { label: 'Streams', value: 'streams', icon: '📺' },
];

const periods = [
  { label: 'All Time', value: 'all' },
  { label: 'This Month', value: 'month' },
  { label: 'This Week', value: 'week' },
  { label: 'Today', value: 'today' },
];

const formatNumber = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num?.toString() || '0';
};

const getValueLabel = () => {
  const labels = {
    level: 'Level',
    gifts_sent: 'Gifts Sent',
    gifts_received: 'Gifts Received',
    followers: 'Followers',
    streams: 'Total Views',
  };
  return labels[activeTab.value] || 'Value';
};

onMounted(() => {
  // TODO: Fetch rankings from API
  rankings.value = Array.from({ length: 50 }, (_, i) => ({
    id: i + 1,
    rank: i + 1,
    username: `User${i + 1}`,
    avatar: `https://i.pravatar.cc/150?img=${i + 1}`,
    level: 99 - i,
    vip_level: i < 10 ? 7 - Math.floor(i / 2) : 0,
    is_vip: i < 10,
    is_verified: i < 5,
    value: 50000 - (i * 1000),
    change: Math.floor(Math.random() * 20) - 10,
    is_me: i === 15,
  }));

  myRank.value = {
    rank: 16,
    avatar: 'https://i.pravatar.cc/150?img=16',
    value: 35000,
  };
});
</script>
