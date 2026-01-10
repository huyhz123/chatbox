<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <!-- Search and Filters -->
      <div class="card bg-base-100 shadow-xl mb-6">
        <div class="card-body">
          <div class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
              <input
                type="text"
                v-model="searchQuery"
                :placeholder="$t('common.search')"
                class="input input-bordered w-full"
              />
            </div>
            <div class="flex gap-2">
              <select v-model="filters.gender" class="select select-bordered">
                <option value="">All Genders</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
              <select v-model="filters.country" class="select select-bordered">
                <option value="">All Countries</option>
                <option value="VN">Vietnam</option>
                <option value="US">USA</option>
                <option value="KR">Korea</option>
                <option value="JP">Japan</option>
                <option value="TH">Thailand</option>
              </select>
              <select v-model="filters.online" class="select select-bordered">
                <option value="">All Users</option>
                <option value="true">Online Only</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs tabs-boxed mb-6 bg-base-100 shadow-xl p-2">
        <a
          class="tab"
          :class="{ 'tab-active': activeTab === 'nearby' }"
          @click="activeTab = 'nearby'"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Nearby
        </a>
        <a
          class="tab"
          :class="{ 'tab-active': activeTab === 'trending' }"
          @click="activeTab = 'trending'"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
          </svg>
          Trending
        </a>
        <a
          class="tab"
          :class="{ 'tab-active': activeTab === 'new' }"
          @click="activeTab = 'new'"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          New Users
        </a>
        <a
          class="tab"
          :class="{ 'tab-active': activeTab === 'match' }"
          @click="activeTab = 'match'"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          Match Me
        </a>
      </div>

      <!-- Users Grid -->
      <div v-if="loading" class="flex justify-center py-12">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        <div
          v-for="user in filteredUsers"
          :key="user.id"
          class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow cursor-pointer"
          @click="viewProfile(user.id)"
        >
          <figure class="relative">
            <img :src="user.cover_photo || user.avatar" class="w-full h-48 object-cover" :alt="user.username" />
            <div class="absolute top-2 right-2">
              <div v-if="user.is_online" class="badge badge-success gap-1">
                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                Online
              </div>
              <div v-else class="badge badge-ghost">Offline</div>
            </div>
            <div class="absolute top-2 left-2 flex gap-1">
              <div v-if="user.is_vip" class="badge badge-warning">VIP {{ user.vip_level }}</div>
              <div v-if="user.is_verified" class="badge badge-info">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
          </figure>
          <div class="card-body p-4">
            <div class="flex items-center gap-2">
              <div class="avatar">
                <div class="w-12 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                  <img :src="user.avatar" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="font-bold truncate">{{ user.full_name || user.username }}</h3>
                <p class="text-xs text-base-content/70 truncate">@{{ user.username }}</p>
              </div>
            </div>
            <div class="flex items-center justify-between mt-2">
              <div class="badge badge-primary badge-sm">Lv {{ user.level }}</div>
              <div class="flex items-center gap-1 text-xs text-base-content/70">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ user.distance }}km
              </div>
            </div>
            <div class="card-actions justify-between mt-3">
              <button class="btn btn-primary btn-sm flex-1" @click.stop="sendMessage(user)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
              </button>
              <button class="btn btn-secondary btn-sm flex-1" @click.stop="addFriend(user)">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
              </button>
              <button class="btn btn-ghost btn-sm btn-circle" @click.stop="sendGift(user)">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Load More -->
      <div v-if="!loading && filteredUsers.length > 0" class="text-center mt-6">
        <button class="btn btn-outline btn-wide" @click="loadMore">
          Load More
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredUsers.length === 0" class="card bg-base-100 shadow-xl">
        <div class="card-body text-center py-12">
          <svg class="w-24 h-24 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <p class="mt-4 text-base-content/50">No users found. Try adjusting your filters.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '@/stores/notification';
import Header from '@/components/common/Header.vue';

const router = useRouter();
const notificationStore = useNotificationStore();

const loading = ref(true);
const searchQuery = ref('');
const activeTab = ref('nearby');
const users = ref([]);
const filters = ref({
  gender: '',
  country: '',
  online: '',
});

const filteredUsers = computed(() => {
  let filtered = users.value;

  // Search filter
  if (searchQuery.value) {
    filtered = filtered.filter(user =>
      user.username.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.full_name?.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  // Gender filter
  if (filters.value.gender) {
    filtered = filtered.filter(user => user.gender === filters.value.gender);
  }

  // Country filter
  if (filters.value.country) {
    filtered = filtered.filter(user => user.country_code === filters.value.country);
  }

  // Online filter
  if (filters.value.online === 'true') {
    filtered = filtered.filter(user => user.is_online);
  }

  return filtered;
});

const viewProfile = (userId) => {
  router.push(`/profile/${userId}`);
};

const sendMessage = (user) => {
  router.push({ name: 'chat', query: { userId: user.id } });
};

const addFriend = async (user) => {
  // TODO: Send friend request API
  notificationStore.success(`Friend request sent to ${user.username}`);
};

const sendGift = (user) => {
  // TODO: Open gift modal
  notificationStore.info('Gift sending feature coming soon!');
};

const loadMore = () => {
  // TODO: Load more users from API
  notificationStore.info('Loading more users...');
};

onMounted(async () => {
  loading.value = true;

  // TODO: Fetch users from API based on activeTab
  // Mock data for now
  users.value = Array.from({ length: 20 }, (_, i) => ({
    id: i + 10,
    username: `user${i + 10}`,
    full_name: `User ${i + 10}`,
    avatar: `https://i.pravatar.cc/150?img=${i + 10}`,
    cover_photo: `https://picsum.photos/400/300?random=${i + 10}`,
    level: Math.floor(Math.random() * 50) + 1,
    vip_level: Math.random() > 0.7 ? Math.floor(Math.random() * 7) + 1 : 0,
    is_vip: Math.random() > 0.7,
    is_verified: Math.random() > 0.8,
    is_online: Math.random() > 0.5,
    gender: ['male', 'female', 'other'][Math.floor(Math.random() * 3)],
    country_code: ['VN', 'US', 'KR', 'JP', 'TH'][Math.floor(Math.random() * 5)],
    distance: Math.floor(Math.random() * 100),
  }));

  loading.value = false;
});
</script>
