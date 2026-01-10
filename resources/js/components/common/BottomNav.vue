<template>
  <div class="btm-nav btm-nav-lg md:hidden z-50 shadow-2xl border-t border-base-300">
    <router-link
      v-for="item in navItems"
      :key="item.name"
      :to="item.path"
      class="relative"
      :class="{ 'active': isActive(item.path) }"
    >
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.iconPath"/>
      </svg>
      <span class="btm-nav-label text-xs">{{ $t(`nav.${item.name}`) }}</span>
      <span v-if="item.badge" class="badge badge-xs badge-error absolute top-1 right-4">
        {{ item.badge }}
      </span>
    </router-link>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const route = useRoute();

const navItems = [
  {
    name: 'home',
    path: '/',
    iconPath: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  },
  {
    name: 'chat',
    path: '/chat',
    iconPath: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
    badge: 5,
  },
  {
    name: 'discover',
    path: '/discover',
    iconPath: 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
  },
  {
    name: 'live',
    path: '/live',
    iconPath: 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
  },
  {
    name: 'profile',
    path: '/profile',
    iconPath: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
  },
];

const isActive = (path) => {
  if (path === '/') {
    return route.path === path;
  }
  return route.path.startsWith(path);
};
</script>

<style scoped>
.btm-nav > .active {
  border-top-width: 2px;
}
</style>
