<template>
  <div class="min-h-screen bg-base-200">
    <!-- Header -->
    <Header />

    <div class="container mx-auto px-4 py-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Sidebar -->
        <div class="lg:col-span-1 space-y-4">
          <!-- User Card -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <div class="flex items-center space-x-4">
                <div class="avatar">
                  <div class="w-16 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                    <img :src="user?.avatar || '/default-avatar.png'" :alt="user?.username" />
                  </div>
                </div>
                <div class="flex-1">
                  <h3 class="font-bold text-lg">{{ user?.full_name || user?.username }}</h3>
                  <div class="flex items-center space-x-2 text-sm">
                    <div class="badge badge-primary">{{ $t('profile.level') }} {{ user?.level }}</div>
                    <div v-if="user?.is_vip" class="badge badge-warning">VIP {{ user?.vip_level }}</div>
                  </div>
                </div>
              </div>

              <!-- Stats -->
              <div class="stats stats-vertical shadow mt-4">
                <div class="stat">
                  <div class="stat-title">{{ $t('profile.balance') }}</div>
                  <div class="stat-value text-primary">{{ formatNumber(user?.balance) }}</div>
                  <div class="stat-desc">coins</div>
                </div>
                <div class="stat">
                  <div class="stat-title">{{ $t('profile.exp') }}</div>
                  <div class="stat-value text-secondary">{{ user?.exp }} / {{ (user?.level || 1) * 100 }}</div>
                  <div class="stat-desc">
                    <progress class="progress progress-secondary w-full" :value="user?.exp" :max="(user?.level || 1) * 100"></progress>
                  </div>
                </div>
              </div>

              <!-- Quick Actions -->
              <div class="flex gap-2 mt-4">
                <button class="btn btn-primary btn-sm flex-1">Top Up</button>
                <router-link to="/shop" class="btn btn-outline btn-sm flex-1">Shop</router-link>
              </div>
            </div>
          </div>

          <!-- Online Users -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h3 class="card-title">
                <span class="flex items-center">
                  <span class="relative flex h-3 w-3 mr-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-success"></span>
                  </span>
                  {{ onlineCount }} {{ $t('chat.online') }}
                </span>
              </h3>

              <div class="space-y-2 max-h-96 overflow-y-auto">
                <div
                  v-for="onlineUser in onlineUsers"
                  :key="onlineUser.id"
                  class="flex items-center justify-between p-2 hover:bg-base-200 rounded-lg cursor-pointer transition"
                  @click="$router.push(`/profile/${onlineUser.id}`)"
                >
                  <div class="flex items-center space-x-2">
                    <div class="avatar online">
                      <div class="w-10 rounded-full">
                        <img :src="onlineUser.avatar || '/default-avatar.png'" />
                      </div>
                    </div>
                    <div>
                      <p class="font-semibold text-sm">{{ onlineUser.username }}</p>
                      <div class="flex items-center space-x-1">
                        <span class="badge badge-xs badge-primary">L{{ onlineUser.level }}</span>
                        <span v-if="onlineUser.is_vip" class="badge badge-xs badge-warning">VIP</span>
                      </div>
                    </div>
                  </div>
                  <button class="btn btn-circle btn-ghost btn-xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-4">
          <!-- Global Chat Hall -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">
                🌍 Global Chat Hall
                <div class="badge badge-secondary">Live</div>
              </h2>

              <!-- Chat Messages -->
              <div class="bg-base-200 rounded-lg p-4 h-96 overflow-y-auto space-y-2">
                <div
                  v-for="message in globalMessages"
                  :key="message.id"
                  class="chat"
                  :class="message.user_id === user?.id ? 'chat-end' : 'chat-start'"
                >
                  <div class="chat-image avatar">
                    <div class="w-10 rounded-full">
                      <img :src="message.user?.avatar || '/default-avatar.png'" />
                    </div>
                  </div>
                  <div class="chat-header">
                    {{ message.user?.username }}
                    <span v-if="message.user?.is_vip" class="badge badge-warning badge-xs ml-1">VIP</span>
                    <time class="text-xs opacity-50 ml-1">{{ formatTime(message.created_at) }}</time>
                  </div>
                  <div class="chat-bubble">{{ message.content }}</div>
                </div>
              </div>

              <!-- Message Input -->
              <div class="flex gap-2 mt-4">
                <input
                  v-model="newMessage"
                  type="text"
                  :placeholder="$t('chat.type_message')"
                  class="input input-bordered flex-1"
                  @keyup.enter="sendMessage"
                />
                <button @click="sendMessage" class="btn btn-primary">
                  {{ $t('chat.send') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Live Streams -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">
                🔴 Live Streams
                <router-link to="/live" class="btn btn-sm btn-ghost ml-auto">View All →</router-link>
              </h2>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="stream in liveStreams"
                  :key="stream.id"
                  class="card bg-base-300 shadow cursor-pointer hover:shadow-xl transition"
                  @click="$router.push(`/live/${stream.id}`)"
                >
                  <figure class="relative">
                    <img :src="stream.thumbnail" alt="Stream" class="w-full h-48 object-cover" />
                    <div class="absolute top-2 left-2">
                      <div class="badge badge-error gap-1">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                        </span>
                        LIVE
                      </div>
                    </div>
                    <div class="absolute bottom-2 right-2 badge badge-neutral">
                      👁 {{ formatNumber(stream.viewer_count) }}
                    </div>
                  </figure>
                  <div class="card-body p-4">
                    <div class="flex items-center space-x-2">
                      <div class="avatar">
                        <div class="w-8 rounded-full">
                          <img :src="stream.user?.avatar" />
                        </div>
                      </div>
                      <div class="flex-1">
                        <h3 class="font-bold text-sm line-clamp-1">{{ stream.title }}</h3>
                        <p class="text-xs text-base-content/70">{{ stream.user?.username }}</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Public Rooms -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">
                🏠 Public Rooms
                <router-link to="/rooms" class="btn btn-sm btn-ghost ml-auto">View All →</router-link>
              </h2>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div
                  v-for="room in publicRooms"
                  :key="room.id"
                  class="card bg-gradient-to-br from-primary/20 to-secondary/20 shadow cursor-pointer hover:shadow-xl transition"
                  @click="$router.push(`/rooms/${room.id}`)"
                >
                  <div class="card-body p-4">
                    <h3 class="font-bold">{{ room.name }}</h3>
                    <div class="flex items-center justify-between text-sm">
                      <span>👥 {{ room.member_count }}/{{ room.max_members }}</span>
                      <span v-if="room.type === 'vip'" class="badge badge-warning badge-sm">VIP</span>
                    </div>
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
import { useAuthStore } from '@/stores/auth';
import Header from '@/components/common/Header.vue';

const authStore = useAuthStore();
const user = computed(() => authStore.currentUser);

const newMessage = ref('');
const globalMessages = ref([]);
const onlineUsers = ref([]);
const onlineCount = ref(0);
const liveStreams = ref([]);
const publicRooms = ref([]);

const formatNumber = (num) => {
  return new Intl.NumberFormat().format(num || 0);
};

const formatTime = (date) => {
  return new Date(date).toLocaleTimeString('vi-VN', {
    hour: '2-digit',
    minute: '2-digit',
  });
};

const sendMessage = () => {
  if (!newMessage.value.trim()) return;

  // TODO: Send via WebSocket
  console.log('Sending message:', newMessage.value);
  newMessage.value = '';
};

onMounted(async () => {
  // TODO: Fetch real data from API
  onlineCount.value = 1234;

  // Mock data
  onlineUsers.value = Array.from({ length: 10 }, (_, i) => ({
    id: i + 1,
    username: `User${i + 1}`,
    level: Math.floor(Math.random() * 50) + 1,
    is_vip: Math.random() > 0.7,
    avatar: `https://i.pravatar.cc/150?img=${i + 1}`,
  }));

  liveStreams.value = Array.from({ length: 4 }, (_, i) => ({
    id: i + 1,
    title: `Amazing Live Stream ${i + 1}`,
    thumbnail: `https://picsum.photos/400/300?random=${i}`,
    viewer_count: Math.floor(Math.random() * 10000),
    user: {
      username: `Streamer${i + 1}`,
      avatar: `https://i.pravatar.cc/150?img=${i + 10}`,
    },
  }));

  publicRooms.value = Array.from({ length: 6 }, (_, i) => ({
    id: i + 1,
    name: `Room ${i + 1}`,
    member_count: Math.floor(Math.random() * 50),
    max_members: 100,
    type: Math.random() > 0.7 ? 'vip' : 'public',
  }));
});
</script>
