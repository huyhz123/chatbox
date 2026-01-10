<template>
  <aside class="w-64 bg-base-100 shadow-xl h-full flex flex-col">
    <!-- User Profile Summary -->
    <div class="p-4 border-b border-base-300">
      <router-link to="/profile" class="flex items-center gap-3 hover:bg-base-200 p-2 rounded-lg transition-colors">
        <div class="avatar" :class="{ 'online': currentUser?.is_online }">
          <div class="w-12 rounded-full">
            <img :src="currentUser?.avatar || '/default-avatar.png'" :alt="currentUser?.username" />
          </div>
        </div>
        <div class="flex-1 min-w-0">
          <p class="font-bold truncate">{{ currentUser?.username }}</p>
          <div class="flex items-center gap-1 text-xs">
            <span class="badge badge-primary badge-xs">Lv {{ currentUser?.level || 1 }}</span>
            <span v-if="currentUser?.is_vip" class="badge badge-warning badge-xs">VIP</span>
          </div>
        </div>
      </router-link>

      <!-- Balance & Coin -->
      <div class="mt-3 p-2 bg-base-200 rounded-lg">
        <div class="flex items-center justify-between text-sm">
          <span class="text-base-content/70">💰 Balance</span>
          <span class="font-bold text-primary">{{ formatNumber(currentUser?.balance || 0) }}</span>
        </div>
      </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto p-4">
      <ul class="menu menu-compact w-full">
        <li v-for="item in menuItems" :key="item.name">
          <router-link
            :to="item.path"
            class="gap-3"
            :class="{ 'active': isActive(item.path) }"
          >
            <component :is="item.icon" class="w-5 h-5" />
            <span>{{ $t(`nav.${item.name}`) }}</span>
            <span v-if="item.badge" class="badge badge-sm badge-primary">{{ item.badge }}</span>
          </router-link>
        </li>
      </ul>

      <div class="divider"></div>

      <!-- Quick Actions -->
      <div class="space-y-2">
        <h3 class="px-4 text-xs font-semibold text-base-content/60 uppercase">Quick Actions</h3>
        <button class="btn btn-sm btn-block btn-primary gap-2" @click="goLive">
          <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
          </svg>
          Go Live
        </button>
        <button class="btn btn-sm btn-block btn-secondary gap-2" @click="buyCoins">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Buy Coins
        </button>
      </div>
    </nav>

    <!-- Footer Links -->
    <div class="p-4 border-t border-base-300">
      <div class="flex flex-wrap gap-2 text-xs text-base-content/60">
        <a href="#" class="hover:text-primary">Help</a>
        <span>•</span>
        <a href="#" class="hover:text-primary">Terms</a>
        <span>•</span>
        <a href="#" class="hover:text-primary">Privacy</a>
      </div>
      <p class="text-xs text-base-content/50 mt-2">© 2024 Multilingual Chat</p>
    </div>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const currentUser = computed(() => authStore.currentUser);

const menuItems = [
  {
    name: 'home',
    path: '/',
    icon: 'svg',
  },
  {
    name: 'chat',
    path: '/chat',
    icon: 'svg',
    badge: 5,
  },
  {
    name: 'discover',
    path: '/discover',
    icon: 'svg',
  },
  {
    name: 'live',
    path: '/live',
    icon: 'svg',
  },
  {
    name: 'rooms',
    path: '/rooms',
    icon: 'svg',
  },
  {
    name: 'games',
    path: '/games',
    icon: 'svg',
  },
  {
    name: 'karaoke',
    path: '/karaoke',
    icon: 'svg',
  },
  {
    name: 'shop',
    path: '/shop',
    icon: 'svg',
  },
  {
    name: 'ranking',
    path: '/ranking',
    icon: 'svg',
  },
];

const isActive = (path) => {
  return route.path === path;
};

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num);
};

const goLive = () => {
  notificationStore.info('Live streaming feature coming soon!');
};

const buyCoins = () => {
  router.push('/shop');
};
</script>
