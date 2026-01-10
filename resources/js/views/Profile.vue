<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div v-if="loading" class="container mx-auto px-4 py-8 flex justify-center">
      <span class="loading loading-spinner loading-lg"></span>
    </div>

    <div v-else-if="profileUser" class="container mx-auto px-4 py-6">
      <!-- Cover Photo -->
      <div class="card bg-base-100 shadow-xl overflow-hidden">
        <div class="relative">
          <img
            :src="profileUser.cover_photo || 'https://picsum.photos/1200/400'"
            class="w-full h-64 object-cover"
            alt="Cover"
          />
          <div v-if="isOwnProfile" class="absolute bottom-4 right-4">
            <button class="btn btn-sm btn-circle btn-primary">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </button>
          </div>
        </div>

        <div class="card-body">
          <div class="flex flex-col md:flex-row gap-6">
            <!-- Avatar -->
            <div class="flex-shrink-0 -mt-20 md:-mt-24">
              <div class="avatar">
                <div class="w-32 md:w-40 rounded-full ring ring-primary ring-offset-base-100 ring-offset-4 shadow-xl">
                  <img :src="profileUser.avatar || '/default-avatar.png'" />
                </div>
              </div>
              <button v-if="isOwnProfile" class="btn btn-sm btn-circle btn-primary absolute translate-x-24 -translate-y-8">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
              </button>
            </div>

            <!-- Profile Info -->
            <div class="flex-1">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                  <h1 class="text-3xl font-bold">{{ profileUser.full_name || profileUser.username }}</h1>
                  <p class="text-base-content/70">@{{ profileUser.username }}</p>
                  <div class="flex items-center gap-2 mt-2">
                    <div class="badge badge-primary">{{ $t('profile.level') }} {{ profileUser.level }}</div>
                    <div v-if="profileUser.is_vip" class="badge badge-warning">VIP {{ profileUser.vip_level }}</div>
                    <div v-if="profileUser.is_verified" class="badge badge-info">
                      <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                      </svg>
                      Verified
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                  <button v-if="!isOwnProfile" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Add Friend
                  </button>
                  <button v-if="!isOwnProfile" class="btn btn-secondary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    Message
                  </button>
                  <button v-if="!isOwnProfile" class="btn btn-outline">Follow</button>
                  <router-link v-if="isOwnProfile" to="/settings" class="btn btn-outline">{{ $t('common.edit') }}</router-link>
                </div>
              </div>

              <!-- Bio -->
              <p v-if="profileUser.bio" class="mt-4 text-base-content/80">{{ profileUser.bio }}</p>

              <!-- Stats -->
              <div class="stats shadow mt-6">
                <div class="stat">
                  <div class="stat-title">{{ $t('profile.friends') }}</div>
                  <div class="stat-value text-primary">{{ formatNumber(stats.friends_count) }}</div>
                </div>
                <div class="stat">
                  <div class="stat-title">{{ $t('profile.followers') }}</div>
                  <div class="stat-value text-secondary">{{ formatNumber(stats.followers_count) }}</div>
                </div>
                <div class="stat">
                  <div class="stat-title">{{ $t('profile.posts') }}</div>
                  <div class="stat-value">{{ formatNumber(stats.posts_count) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="tabs tabs-boxed mt-6 bg-base-100 shadow-xl p-2">
        <a class="tab tab-active">Posts</a>
        <a class="tab">Videos</a>
        <a class="tab">Photos</a>
        <a class="tab">Friends</a>
      </div>

      <!-- Content -->
      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Posts -->
        <div class="md:col-span-2 space-y-4">
          <div v-for="post in posts" :key="post.id" class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <div class="flex items-center gap-3">
                <div class="avatar">
                  <div class="w-12 rounded-full">
                    <img :src="profileUser.avatar" />
                  </div>
                </div>
                <div>
                  <p class="font-bold">{{ profileUser.username }}</p>
                  <p class="text-xs text-base-content/70">{{ formatTime(post.created_at) }}</p>
                </div>
              </div>

              <p class="mt-4">{{ post.content }}</p>

              <div class="card-actions justify-between mt-4">
                <div class="flex gap-4">
                  <button class="btn btn-ghost btn-sm gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    {{ post.likes_count }}
                  </button>
                  <button class="btn btn-ghost btn-sm gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    {{ post.comments_count }}
                  </button>
                </div>
                <button class="btn btn-ghost btn-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>

          <div v-if="posts.length === 0" class="card bg-base-100 shadow-xl">
            <div class="card-body text-center py-12">
              <p class="text-base-content/50">No posts yet</p>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-4">
          <!-- About -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h3 class="card-title">About</h3>
              <div class="space-y-2 text-sm">
                <p v-if="profileUser.gender">
                  <span class="font-semibold">Gender:</span> {{ profileUser.gender }}
                </p>
                <p v-if="profileUser.country_code">
                  <span class="font-semibold">Country:</span> {{ profileUser.country_code }}
                </p>
                <p>
                  <span class="font-semibold">Joined:</span> {{ formatDate(profileUser.created_at) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Badges -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h3 class="card-title">Badges</h3>
              <div class="grid grid-cols-3 gap-2">
                <div class="tooltip" data-tip="Level 50">
                  <div class="w-full aspect-square bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg flex items-center justify-center text-2xl">
                    🏆
                  </div>
                </div>
                <div class="tooltip" data-tip="VIP Member">
                  <div class="w-full aspect-square bg-gradient-to-br from-purple-400 to-purple-600 rounded-lg flex items-center justify-center text-2xl">
                    👑
                  </div>
                </div>
                <div class="tooltip" data-tip="Top Gifter">
                  <div class="w-full aspect-square bg-gradient-to-br from-pink-400 to-pink-600 rounded-lg flex items-center justify-center text-2xl">
                    🎁
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Header from '@/components/common/Header.vue';

const route = useRoute();
const authStore = useAuthStore();

const loading = ref(true);
const profileUser = ref(null);
const stats = ref({});
const posts = ref([]);

const currentUser = computed(() => authStore.currentUser);
const isOwnProfile = computed(() => {
  const userId = route.params.id;
  return !userId || parseInt(userId) === currentUser.value?.id;
});

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0);
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('vi-VN', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  });
};

onMounted(async () => {
  loading.value = true;

  // TODO: Fetch profile data from API
  if (isOwnProfile.value) {
    profileUser.value = currentUser.value;
  } else {
    // Mock data for other users
    profileUser.value = {
      id: parseInt(route.params.id),
      username: 'user' + route.params.id,
      full_name: 'User ' + route.params.id,
      avatar: `https://i.pravatar.cc/150?img=${route.params.id}`,
      cover_photo: 'https://picsum.photos/1200/400',
      bio: 'This is my bio',
      level: 42,
      vip_level: 3,
      is_vip: true,
      is_verified: true,
      created_at: new Date(2024, 0, 1),
    };
  }

  stats.value = {
    friends_count: 156,
    followers_count: 892,
    following_count: 234,
    posts_count: 45,
    videos_count: 12,
  };

  posts.value = [];

  loading.value = false;
});
</script>
