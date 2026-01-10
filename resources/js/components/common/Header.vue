<template>
  <div class="navbar bg-base-100 shadow-lg sticky top-0 z-40">
    <div class="navbar-start">
      <!-- Mobile Menu -->
      <div class="dropdown">
        <label tabindex="0" class="btn btn-ghost lg:hidden">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </label>
        <ul tabindex="0" class="menu menu-sm dropdown-content mt-3 z-[1] p-2 shadow bg-base-100 rounded-box w-52">
          <li><router-link to="/">{{ $t('nav.home') }}</router-link></li>
          <li><router-link to="/chat">{{ $t('nav.chat') }}</router-link></li>
          <li><router-link to="/discover">{{ $t('nav.discover') }}</router-link></li>
          <li><router-link to="/live">{{ $t('nav.live') }}</router-link></li>
          <li><router-link to="/rooms">{{ $t('nav.rooms') }}</router-link></li>
          <li><router-link to="/games">{{ $t('nav.games') }}</router-link></li>
          <li><router-link to="/karaoke">{{ $t('nav.karaoke') }}</router-link></li>
          <li><router-link to="/ranking">{{ $t('nav.ranking') }}</router-link></li>
        </ul>
      </div>

      <!-- Logo -->
      <router-link to="/" class="btn btn-ghost normal-case text-xl font-bold">
        <span class="text-primary">Multi</span>
        <span class="text-secondary">Chat</span>
      </router-link>
    </div>

    <!-- Desktop Navigation -->
    <div class="navbar-center hidden lg:flex">
      <ul class="menu menu-horizontal px-1">
        <li><router-link to="/" active-class="active">{{ $t('nav.home') }}</router-link></li>
        <li><router-link to="/chat" active-class="active">{{ $t('nav.chat') }}</router-link></li>
        <li><router-link to="/discover" active-class="active">{{ $t('nav.discover') }}</router-link></li>
        <li><router-link to="/live" active-class="active">{{ $t('nav.live') }}</router-link></li>
        <li><router-link to="/rooms" active-class="active">{{ $t('nav.rooms') }}</router-link></li>
        <li><router-link to="/games" active-class="active">{{ $t('nav.games') }}</router-link></li>
        <li><router-link to="/karaoke" active-class="active">{{ $t('nav.karaoke') }}</router-link></li>
      </ul>
    </div>

    <div class="navbar-end gap-2">
      <!-- Search -->
      <div class="form-control hidden md:block">
        <input type="text" :placeholder="$t('common.search')" class="input input-bordered input-sm w-full max-w-xs" />
      </div>

      <!-- Notifications -->
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle">
          <div class="indicator">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="badge badge-xs badge-primary indicator-item">3</span>
          </div>
        </label>
        <div tabindex="0" class="mt-3 z-[1] card card-compact dropdown-content w-80 bg-base-100 shadow-xl">
          <div class="card-body">
            <h3 class="card-title">Notifications</h3>
            <div class="space-y-2">
              <div class="alert alert-info">
                <span>New friend request from User123</span>
              </div>
              <div class="alert">
                <span>Someone sent you a gift 🎁</span>
              </div>
              <div class="alert">
                <span>New message in Global Chat</span>
              </div>
            </div>
            <div class="card-actions">
              <button class="btn btn-sm btn-block">View All</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Messages -->
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle">
          <div class="indicator">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            <span class="badge badge-xs badge-error indicator-item">5</span>
          </div>
        </label>
        <div tabindex="0" class="mt-3 z-[1] card card-compact dropdown-content w-80 bg-base-100 shadow-xl">
          <div class="card-body">
            <h3 class="card-title">Messages</h3>
            <div class="space-y-2">
              <div class="flex items-center gap-2 p-2 hover:bg-base-200 rounded cursor-pointer">
                <div class="avatar online">
                  <div class="w-10 rounded-full">
                    <img src="https://i.pravatar.cc/150?img=1" />
                  </div>
                </div>
                <div class="flex-1">
                  <p class="font-semibold text-sm">User1</p>
                  <p class="text-xs text-base-content/70 truncate">Hey, how are you?</p>
                </div>
                <div class="badge badge-primary badge-sm">2</div>
              </div>
            </div>
            <div class="card-actions">
              <router-link to="/chat" class="btn btn-sm btn-block">View All</router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- User Menu -->
      <div class="dropdown dropdown-end">
        <label tabindex="0" class="btn btn-ghost btn-circle avatar">
          <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
            <img :src="user?.avatar || '/default-avatar.png'" />
          </div>
        </label>
        <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52">
          <li class="menu-title">
            <span>{{ user?.username }}</span>
          </li>
          <li>
            <router-link to="/profile" class="justify-between">
              {{ $t('common.profile') }}
              <span class="badge badge-primary">L{{ user?.level }}</span>
            </router-link>
          </li>
          <li>
            <a class="justify-between">
              {{ $t('profile.balance') }}
              <span class="badge">{{ formatNumber(user?.balance) }} coins</span>
            </a>
          </li>
          <li><router-link to="/shop">{{ $t('nav.shop') }}</router-link></li>
          <li><router-link to="/ranking">{{ $t('nav.ranking') }}</router-link></li>
          <li><router-link to="/settings">{{ $t('common.settings') }}</router-link></li>
          <li>
            <a>
              {{ $t('common.language') }}
            </a>
          </li>
          <div class="divider my-0"></div>
          <li><a @click="handleLogout">{{ $t('common.logout') }}</a></li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const user = computed(() => authStore.currentUser);

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0);
};

const handleLogout = async () => {
  await authStore.logout();
  notificationStore.success(t('auth.logout_success'));
  router.push('/login');
};
</script>
