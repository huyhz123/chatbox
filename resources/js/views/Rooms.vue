<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <!-- Header Actions -->
      <div class="flex flex-col md:flex-row gap-4 items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Voice Chat Rooms</h1>
        <button class="btn btn-primary gap-2" @click="createRoom">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Create Room
        </button>
      </div>

      <!-- Filters -->
      <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button
          v-for="filter in filters"
          :key="filter.value"
          class="btn btn-sm"
          :class="activeFilter === filter.value ? 'btn-primary' : 'btn-ghost'"
          @click="activeFilter = filter.value"
        >
          {{ filter.label }}
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Rooms Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="room in filteredRooms"
          :key="room.id"
          class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow cursor-pointer"
          @click="joinRoom(room)"
        >
          <div class="card-body">
            <!-- Room Header -->
            <div class="flex items-start justify-between">
              <div class="flex items-center gap-3">
                <div class="avatar">
                  <div class="w-16 rounded-xl bg-gradient-to-br from-primary to-secondary p-0.5">
                    <div class="w-full h-full rounded-xl bg-base-100 flex items-center justify-center">
                      <span class="text-2xl">{{ room.icon }}</span>
                    </div>
                  </div>
                </div>
                <div>
                  <h3 class="font-bold text-lg">{{ room.name }}</h3>
                  <p class="text-xs text-base-content/70">by {{ room.owner.username }}</p>
                </div>
              </div>
              <div class="badge" :class="room.is_private ? 'badge-warning' : 'badge-success'">
                {{ room.is_private ? '🔒 Private' : '🌐 Public' }}
              </div>
            </div>

            <!-- Room Info -->
            <div class="flex items-center gap-4 text-sm mt-4">
              <div class="flex items-center gap-1">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
                <span>{{ room.current_users }}/{{ room.max_users }}</span>
              </div>
              <div class="badge badge-ghost badge-sm">{{ room.category }}</div>
            </div>

            <!-- User Seats -->
            <div class="grid grid-cols-4 gap-2 mt-4">
              <div
                v-for="(seat, index) in 8"
                :key="index"
                class="aspect-square rounded-lg flex items-center justify-center"
                :class="room.seats[index] ? 'bg-base-200' : 'bg-base-300/30'"
              >
                <div v-if="room.seats[index]" class="avatar">
                  <div class="w-full rounded-lg">
                    <img :src="room.seats[index].avatar" :alt="room.seats[index].username" />
                  </div>
                </div>
                <svg v-else class="w-6 h-6 text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </div>
            </div>

            <!-- Room Tags -->
            <div class="flex flex-wrap gap-1 mt-4">
              <span v-for="tag in room.tags" :key="tag" class="badge badge-sm badge-outline">
                {{ tag }}
              </span>
            </div>

            <!-- Join Button -->
            <div class="card-actions justify-end mt-4">
              <button class="btn btn-primary btn-sm" @click.stop="joinRoom(room)">
                Join Room
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredRooms.length === 0" class="card bg-base-100 shadow-xl">
        <div class="card-body text-center py-12">
          <svg class="w-24 h-24 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
          </svg>
          <p class="mt-4 text-base-content/50">No rooms available</p>
          <button class="btn btn-primary mt-4" @click="createRoom">
            Create Your Room
          </button>
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

const loading = ref(true);
const activeFilter = ref('all');
const rooms = ref([]);

const filters = [
  { label: 'All Rooms', value: 'all' },
  { label: 'Music', value: 'music' },
  { label: 'Gaming', value: 'gaming' },
  { label: 'Chat', value: 'chat' },
  { label: 'Party', value: 'party' },
];

const filteredRooms = computed(() => {
  if (activeFilter.value === 'all') return rooms.value;
  return rooms.value.filter(room => room.category === activeFilter.value);
});

const joinRoom = (room) => {
  if (room.current_users >= room.max_users) {
    notificationStore.error('Room is full!');
    return;
  }
  if (room.is_private) {
    notificationStore.info('Password required to join this room');
    return;
  }
  notificationStore.info(`Joining ${room.name}...`);
};

const createRoom = () => {
  notificationStore.info('Room creation feature coming soon!');
};

onMounted(async () => {
  loading.value = true;

  // TODO: Fetch rooms from API
  rooms.value = Array.from({ length: 9 }, (_, i) => ({
    id: i + 1,
    name: ['Chill Lounge', 'Gaming Squad', 'Music Lovers', 'Party Time', 'Late Night Chat', 'Study Together', 'Podcast Room', 'Karaoke Party', 'Book Club'][i],
    icon: ['☕', '🎮', '🎵', '🎉', '💬', '📚', '🎙️', '🎤', '📖'][i],
    category: ['chat', 'gaming', 'music', 'party', 'chat', 'chat', 'chat', 'music', 'chat'][i],
    owner: {
      id: i + 10,
      username: `host${i + 1}`,
      avatar: `https://i.pravatar.cc/150?img=${i + 10}`,
    },
    current_users: Math.floor(Math.random() * 8) + 1,
    max_users: 8,
    is_private: Math.random() > 0.7,
    seats: Array.from({ length: 8 }, (_, j) => {
      if (j < Math.floor(Math.random() * 8) + 1) {
        return {
          username: `user${j}`,
          avatar: `https://i.pravatar.cc/150?img=${j + 20}`,
        };
      }
      return null;
    }),
    tags: [['chill', 'relax'], ['gaming', 'fun'], ['music', 'party'], ['fun', 'dance']][Math.floor(Math.random() * 4)],
  }));

  loading.value = false;
});
</script>
