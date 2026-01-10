<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <!-- Header -->
      <div class="card bg-gradient-to-r from-pink-500 via-purple-500 to-indigo-500 text-white shadow-xl mb-6">
        <div class="card-body flex-row items-center justify-between">
          <div>
            <h1 class="card-title text-3xl">🎤 Karaoke Room</h1>
            <p>Sing your heart out and share your talent!</p>
          </div>
          <button class="btn btn-accent gap-2" @click="startSinging">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M7 4a3 3 0 016 0v4a3 3 0 11-6 0V4zm4 10.93A7.001 7.001 0 0017 8a1 1 0 10-2 0A5 5 0 015 8a1 1 0 00-2 0 7.001 7.001 0 006 6.93V17H6a1 1 0 100 2h8a1 1 0 100-2h-3v-2.07z" clip-rule="evenodd"/>
            </svg>
            Start Singing
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Song Library -->
        <div class="lg:col-span-2">
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h2 class="card-title">Song Library</h2>

              <!-- Search and Filters -->
              <div class="flex gap-2 mb-4">
                <input
                  type="text"
                  v-model="searchQuery"
                  placeholder="Search songs..."
                  class="input input-bordered flex-1"
                />
                <select v-model="languageFilter" class="select select-bordered">
                  <option value="">All Languages</option>
                  <option value="vi">Vietnamese</option>
                  <option value="en">English</option>
                  <option value="ko">Korean</option>
                  <option value="ja">Japanese</option>
                </select>
              </div>

              <!-- Songs List -->
              <div class="space-y-2 max-h-[600px] overflow-y-auto">
                <div
                  v-for="song in filteredSongs"
                  :key="song.id"
                  class="flex items-center gap-4 p-3 rounded-lg hover:bg-base-200 transition-colors cursor-pointer"
                  @click="selectSong(song)"
                  :class="{ 'bg-base-200': selectedSong?.id === song.id }"
                >
                  <div class="avatar">
                    <div class="w-16 rounded">
                      <img :src="song.thumbnail" :alt="song.title" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <h4 class="font-bold truncate">{{ song.title }}</h4>
                    <p class="text-sm text-base-content/70 truncate">{{ song.artist }}</p>
                    <div class="flex gap-2 mt-1">
                      <span class="badge badge-sm badge-ghost">{{ song.language }}</span>
                      <span class="badge badge-sm badge-primary">{{ song.genre }}</span>
                    </div>
                  </div>
                  <div class="flex flex-col items-end gap-1">
                    <div class="flex items-center gap-1 text-sm">
                      <svg class="w-4 h-4 text-warning" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                      </svg>
                      <span>{{ song.difficulty }}/5</span>
                    </div>
                    <button class="btn btn-primary btn-xs" @click.stop="singNow(song)">
                      Sing
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
          <!-- Room Info -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h3 class="card-title">🎶 Active Rooms</h3>
              <div class="space-y-2">
                <div
                  v-for="room in karaokeRooms"
                  :key="room.id"
                  class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 cursor-pointer"
                  @click="joinKaraokeRoom(room)"
                >
                  <div class="avatar">
                    <div class="w-10 rounded-full">
                      <img :src="room.host.avatar" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm truncate">{{ room.name }}</p>
                    <div class="flex items-center gap-1 text-xs text-base-content/70">
                      <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                      </svg>
                      <span>{{ room.members }}</span>
                    </div>
                  </div>
                  <div class="badge badge-success badge-sm">Live</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Singers -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h3 class="card-title">🏆 Top Singers</h3>
              <div class="space-y-3">
                <div
                  v-for="(singer, index) in topSingers"
                  :key="singer.id"
                  class="flex items-center gap-2"
                >
                  <div class="font-bold text-lg w-6">
                    <span v-if="index === 0">🥇</span>
                    <span v-else-if="index === 1">🥈</span>
                    <span v-else-if="index === 2">🥉</span>
                    <span v-else>#{{ index + 1 }}</span>
                  </div>
                  <div class="avatar">
                    <div class="w-10 rounded-full">
                      <img :src="singer.avatar" />
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="font-semibold text-sm">{{ singer.username }}</p>
                    <p class="text-xs text-base-content/70">{{ singer.score }} pts</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- My Stats -->
          <div class="card bg-base-100 shadow-xl">
            <div class="card-body">
              <h3 class="card-title">📊 My Stats</h3>
              <div class="stats stats-vertical shadow">
                <div class="stat">
                  <div class="stat-title">Songs Sung</div>
                  <div class="stat-value text-primary">23</div>
                </div>
                <div class="stat">
                  <div class="stat-title">Best Score</div>
                  <div class="stat-value text-secondary">98.5</div>
                </div>
                <div class="stat">
                  <div class="stat-title">Rank</div>
                  <div class="stat-value">#42</div>
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
import { useNotificationStore } from '@/stores/notification';
import Header from '@/components/common/Header.vue';

const notificationStore = useNotificationStore();

const searchQuery = ref('');
const languageFilter = ref('');
const selectedSong = ref(null);
const songs = ref([]);
const karaokeRooms = ref([]);
const topSingers = ref([]);

const filteredSongs = computed(() => {
  let filtered = songs.value;

  if (searchQuery.value) {
    filtered = filtered.filter(song =>
      song.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      song.artist.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  if (languageFilter.value) {
    filtered = filtered.filter(song => song.language === languageFilter.value);
  }

  return filtered;
});

const selectSong = (song) => {
  selectedSong.value = song;
};

const singNow = (song) => {
  notificationStore.info(`Starting karaoke for: ${song.title}`);
  // TODO: Open karaoke room with song
};

const startSinging = () => {
  notificationStore.info('Karaoke room feature coming soon!');
};

const joinKaraokeRoom = (room) => {
  notificationStore.info(`Joining ${room.name}...`);
};

onMounted(() => {
  // TODO: Fetch songs from API
  songs.value = [
    { id: 1, title: 'Nơi Này Có Anh', artist: 'Sơn Tùng M-TP', thumbnail: 'https://picsum.photos/100/100?random=1', language: 'vi', genre: 'Pop', difficulty: 3 },
    { id: 2, title: 'Shape of You', artist: 'Ed Sheeran', thumbnail: 'https://picsum.photos/100/100?random=2', language: 'en', genre: 'Pop', difficulty: 2 },
    { id: 3, title: 'Dynamite', artist: 'BTS', thumbnail: 'https://picsum.photos/100/100?random=3', language: 'ko', genre: 'K-Pop', difficulty: 4 },
    { id: 4, title: 'Lemon', artist: 'Kenshi Yonezu', thumbnail: 'https://picsum.photos/100/100?random=4', language: 'ja', genre: 'J-Pop', difficulty: 5 },
    { id: 5, title: 'Chúng Ta Của Hiện Tại', artist: 'Sơn Tùng M-TP', thumbnail: 'https://picsum.photos/100/100?random=5', language: 'vi', genre: 'Ballad', difficulty: 3 },
    { id: 6, title: 'Someone Like You', artist: 'Adele', thumbnail: 'https://picsum.photos/100/100?random=6', language: 'en', genre: 'Ballad', difficulty: 4 },
  ];

  karaokeRooms.value = [
    { id: 1, name: 'Chill Vibes', host: { avatar: 'https://i.pravatar.cc/150?img=20' }, members: 5 },
    { id: 2, name: 'K-Pop Lovers', host: { avatar: 'https://i.pravatar.cc/150?img=21' }, members: 12 },
    { id: 3, name: 'Ballad Night', host: { avatar: 'https://i.pravatar.cc/150?img=22' }, members: 8 },
  ];

  topSingers.value = [
    { id: 1, username: 'SingingStar', avatar: 'https://i.pravatar.cc/150?img=30', score: 9850 },
    { id: 2, username: 'VoiceAngel', avatar: 'https://i.pravatar.cc/150?img=31', score: 9720 },
    { id: 3, username: 'MusicLover', avatar: 'https://i.pravatar.cc/150?img=32', score: 9650 },
    { id: 4, username: 'KaraokePro', avatar: 'https://i.pravatar.cc/150?img=33', score: 9500 },
  ];
});
</script>
