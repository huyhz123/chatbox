<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <!-- Top Banner -->
      <div class="card bg-gradient-to-r from-primary to-secondary text-primary-content shadow-xl mb-6">
        <div class="card-body flex-row items-center justify-between">
          <div>
            <h2 class="card-title text-2xl">Go Live Now!</h2>
            <p>Share your moments with the world</p>
          </div>
          <button class="btn btn-accent gap-2" @click="startLiveStream">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
            </svg>
            Start Live Stream
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        <button
          class="btn btn-sm"
          :class="activeFilter === 'all' ? 'btn-primary' : 'btn-ghost'"
          @click="activeFilter = 'all'"
        >
          All
        </button>
        <button
          class="btn btn-sm"
          :class="activeFilter === 'popular' ? 'btn-primary' : 'btn-ghost'"
          @click="activeFilter = 'popular'"
        >
          Popular
        </button>
        <button
          class="btn btn-sm"
          :class="activeFilter === 'gaming' ? 'btn-primary' : 'btn-ghost'"
          @click="activeFilter = 'gaming'"
        >
          Gaming
        </button>
        <button
          class="btn btn-sm"
          :class="activeFilter === 'music' ? 'btn-primary' : 'btn-ghost'"
          @click="activeFilter = 'music'"
        >
          Music
        </button>
        <button
          class="btn btn-sm"
          :class="activeFilter === 'talk' ? 'btn-primary' : 'btn-ghost'"
          @click="activeFilter = 'talk'"
        >
          Talk Show
        </button>
        <button
          class="btn btn-sm"
          :class="activeFilter === 'pk' ? 'btn-primary' : 'btn-ghost'"
          @click="activeFilter = 'pk'"
        >
          PK Battle
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="flex justify-center py-12">
        <span class="loading loading-spinner loading-lg"></span>
      </div>

      <!-- Live Streams Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <div
          v-for="stream in filteredStreams"
          :key="stream.id"
          class="card bg-base-100 shadow-xl hover:shadow-2xl transition-shadow cursor-pointer group"
          @click="joinStream(stream)"
        >
          <figure class="relative overflow-hidden">
            <img :src="stream.thumbnail" class="w-full h-56 object-cover group-hover:scale-110 transition-transform duration-300" :alt="stream.title" />

            <!-- Live Badge -->
            <div class="absolute top-2 left-2">
              <div class="badge badge-error gap-1 font-bold">
                <div class="w-2 h-2 rounded-full bg-white animate-pulse"></div>
                LIVE
              </div>
            </div>

            <!-- Viewers Count -->
            <div class="absolute top-2 right-2 bg-base-100/80 backdrop-blur px-2 py-1 rounded-full flex items-center gap-1 text-sm">
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
              </svg>
              {{ formatNumber(stream.viewers_count) }}
            </div>

            <!-- PK Battle Indicator -->
            <div v-if="stream.is_pk_battle" class="absolute bottom-2 right-2">
              <div class="badge badge-warning gap-1 font-bold">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                PK
              </div>
            </div>

            <!-- Streamer Avatar -->
            <div class="absolute bottom-2 left-2">
              <div class="avatar">
                <div class="w-10 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                  <img :src="stream.streamer.avatar" />
                </div>
              </div>
            </div>
          </figure>

          <div class="card-body p-4">
            <h3 class="font-bold line-clamp-2">{{ stream.title }}</h3>
            <div class="flex items-center gap-2 mt-1">
              <p class="text-sm text-base-content/70">{{ stream.streamer.username }}</p>
              <div v-if="stream.streamer.is_verified" class="badge badge-info badge-xs">
                <svg class="w-2 h-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
              </div>
              <div v-if="stream.streamer.vip_level" class="badge badge-warning badge-xs">
                VIP {{ stream.streamer.vip_level }}
              </div>
            </div>
            <div class="flex flex-wrap gap-1 mt-2">
              <span v-for="tag in stream.tags" :key="tag" class="badge badge-sm badge-ghost">
                {{ tag }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="!loading && filteredStreams.length === 0" class="card bg-base-100 shadow-xl">
        <div class="card-body text-center py-12">
          <svg class="w-24 h-24 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
          </svg>
          <p class="mt-4 text-base-content/50">No live streams at the moment</p>
          <button class="btn btn-primary mt-4" @click="startLiveStream">
            Be the first to go live!
          </button>
        </div>
      </div>

      <!-- Load More -->
      <div v-if="!loading && filteredStreams.length > 0" class="text-center mt-6">
        <button class="btn btn-outline btn-wide" @click="loadMore">
          Load More Streams
        </button>
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
const activeFilter = ref('all');
const streams = ref([]);

const filteredStreams = computed(() => {
  if (activeFilter.value === 'all') return streams.value;
  return streams.value.filter(stream => stream.category === activeFilter.value);
});

const formatNumber = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
};

const joinStream = (stream) => {
  // TODO: Navigate to live stream room with Agora.io integration
  notificationStore.info(`Joining ${stream.streamer.username}'s live stream...`);
};

const startLiveStream = () => {
  // TODO: Open start live stream modal
  notificationStore.info('Live streaming feature coming soon!');
};

const loadMore = () => {
  // TODO: Load more streams from API
  notificationStore.info('Loading more streams...');
};

onMounted(async () => {
  loading.value = true;

  // TODO: Fetch live streams from API
  // Mock data for now
  streams.value = [
    {
      id: 1,
      title: 'Chilling with music and good vibes 🎵',
      thumbnail: 'https://picsum.photos/400/300?random=1',
      category: 'music',
      viewers_count: 2341,
      is_pk_battle: false,
      streamer: {
        id: 2,
        username: 'musiclover',
        avatar: 'https://i.pravatar.cc/150?img=2',
        is_verified: true,
        vip_level: 5,
      },
      tags: ['music', 'chill', 'acoustic'],
    },
    {
      id: 2,
      title: 'EPIC PK BATTLE - Come support me! 🔥',
      thumbnail: 'https://picsum.photos/400/300?random=2',
      category: 'pk',
      viewers_count: 5672,
      is_pk_battle: true,
      streamer: {
        id: 3,
        username: 'pkmaster',
        avatar: 'https://i.pravatar.cc/150?img=3',
        is_verified: true,
        vip_level: 7,
      },
      tags: ['pk', 'battle', 'competitive'],
    },
    {
      id: 3,
      title: 'Playing PUBG Mobile - Road to Conqueror',
      thumbnail: 'https://picsum.photos/400/300?random=3',
      category: 'gaming',
      viewers_count: 892,
      is_pk_battle: false,
      streamer: {
        id: 4,
        username: 'progamer',
        avatar: 'https://i.pravatar.cc/150?img=4',
        is_verified: false,
        vip_level: 3,
      },
      tags: ['gaming', 'pubg', 'mobile'],
    },
    {
      id: 4,
      title: 'Late night talk - AMA about anything!',
      thumbnail: 'https://picsum.photos/400/300?random=4',
      category: 'talk',
      viewers_count: 456,
      is_pk_battle: false,
      streamer: {
        id: 5,
        username: 'talkshow',
        avatar: 'https://i.pravatar.cc/150?img=5',
        is_verified: true,
        vip_level: 4,
      },
      tags: ['talk', 'chat', 'qa'],
    },
    {
      id: 5,
      title: 'Karaoke night with friends 🎤🎶',
      thumbnail: 'https://picsum.photos/400/300?random=5',
      category: 'music',
      viewers_count: 1234,
      is_pk_battle: false,
      streamer: {
        id: 6,
        username: 'singstar',
        avatar: 'https://i.pravatar.cc/150?img=6',
        is_verified: false,
        vip_level: 2,
      },
      tags: ['karaoke', 'singing', 'fun'],
    },
    {
      id: 6,
      title: 'League of Legends - Challenger gameplay',
      thumbnail: 'https://picsum.photos/400/300?random=6',
      category: 'gaming',
      viewers_count: 3421,
      is_pk_battle: false,
      streamer: {
        id: 7,
        username: 'lolpro',
        avatar: 'https://i.pravatar.cc/150?img=7',
        is_verified: true,
        vip_level: 6,
      },
      tags: ['gaming', 'lol', 'challenger'],
    },
    {
      id: 7,
      title: 'Morning coffee and positive energy ☕',
      thumbnail: 'https://picsum.photos/400/300?random=7',
      category: 'talk',
      viewers_count: 678,
      is_pk_battle: false,
      streamer: {
        id: 8,
        username: 'morningvibes',
        avatar: 'https://i.pravatar.cc/150?img=8',
        is_verified: false,
        vip_level: 1,
      },
      tags: ['talk', 'morning', 'motivation'],
    },
    {
      id: 8,
      title: 'HUGE PK BATTLE - Top 1 vs Top 2 🏆',
      thumbnail: 'https://picsum.photos/400/300?random=8',
      category: 'pk',
      viewers_count: 8945,
      is_pk_battle: true,
      streamer: {
        id: 9,
        username: 'topstreamer',
        avatar: 'https://i.pravatar.cc/150?img=9',
        is_verified: true,
        vip_level: 7,
      },
      tags: ['pk', 'epic', 'top'],
    },
  ];

  loading.value = false;
});
</script>
