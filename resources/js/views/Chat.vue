<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 h-[calc(100vh-180px)]">
        <!-- Conversations List -->
        <div class="card bg-base-100 shadow-xl overflow-hidden">
          <div class="card-body p-0 flex flex-col h-full">
            <!-- Search and New Chat -->
            <div class="p-4 border-b border-base-300">
              <div class="flex gap-2">
                <input
                  type="text"
                  v-model="searchQuery"
                  :placeholder="$t('chat.search')"
                  class="input input-bordered input-sm flex-1"
                />
                <button class="btn btn-primary btn-sm">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Conversations -->
            <div class="flex-1 overflow-y-auto">
              <div
                v-for="conv in filteredConversations"
                :key="conv.id"
                class="flex items-center gap-3 p-4 hover:bg-base-200 cursor-pointer transition-colors"
                :class="{ 'bg-base-200': activeConversation?.id === conv.id }"
                @click="selectConversation(conv)"
              >
                <div class="avatar" :class="{ 'online': conv.is_online }">
                  <div class="w-12 rounded-full">
                    <img :src="conv.avatar" :alt="conv.name" />
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="font-semibold truncate">{{ conv.name }}</p>
                    <span class="text-xs text-base-content/60">{{ formatTime(conv.last_message_at) }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <p class="text-sm text-base-content/70 truncate">{{ conv.last_message }}</p>
                    <span v-if="conv.unread_count > 0" class="badge badge-primary badge-sm">{{ conv.unread_count }}</span>
                  </div>
                </div>
              </div>

              <div v-if="filteredConversations.length === 0" class="text-center py-12">
                <p class="text-base-content/50">{{ $t('chat.no_conversations') }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Chat Area -->
        <div class="md:col-span-2 card bg-base-100 shadow-xl overflow-hidden">
          <div v-if="!activeConversation" class="flex items-center justify-center h-full">
            <div class="text-center">
              <svg class="w-24 h-24 mx-auto text-base-content/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
              </svg>
              <p class="mt-4 text-base-content/50">{{ $t('chat.select_conversation') }}</p>
            </div>
          </div>

          <div v-else class="flex flex-col h-full">
            <!-- Chat Header -->
            <div class="p-4 border-b border-base-300 flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="avatar" :class="{ 'online': activeConversation.is_online }">
                  <div class="w-10 rounded-full">
                    <img :src="activeConversation.avatar" :alt="activeConversation.name" />
                  </div>
                </div>
                <div>
                  <p class="font-semibold">{{ activeConversation.name }}</p>
                  <p class="text-xs text-base-content/60">
                    {{ activeConversation.is_online ? $t('chat.online') : $t('chat.offline') }}
                  </p>
                </div>
              </div>
              <div class="flex gap-2">
                <button class="btn btn-ghost btn-sm btn-circle" @click="startVoiceCall">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </button>
                <button class="btn btn-ghost btn-sm btn-circle" @click="startVideoCall">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                  </svg>
                </button>
                <button class="btn btn-ghost btn-sm btn-circle">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                  </svg>
                </button>
              </div>
            </div>

            <!-- Messages -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4" ref="messagesContainer">
              <div
                v-for="message in messages"
                :key="message.id"
                class="chat"
                :class="message.sender_id === currentUser?.id ? 'chat-end' : 'chat-start'"
              >
                <div class="chat-image avatar">
                  <div class="w-10 rounded-full">
                    <img :src="message.sender_avatar" />
                  </div>
                </div>
                <div class="chat-header">
                  {{ message.sender_name }}
                  <time class="text-xs opacity-50">{{ formatTime(message.created_at) }}</time>
                </div>
                <div class="chat-bubble" :class="message.sender_id === currentUser?.id ? 'chat-bubble-primary' : ''">
                  <p v-if="message.type === 'text'">{{ message.content }}</p>
                  <img v-if="message.type === 'image'" :src="message.content" class="max-w-xs rounded-lg" />
                  <audio v-if="message.type === 'audio'" :src="message.content" controls class="max-w-xs"></audio>
                  <video v-if="message.type === 'video'" :src="message.content" controls class="max-w-xs rounded-lg"></video>
                  <div v-if="message.type === 'gift'" class="flex items-center gap-2">
                    <span class="text-2xl">{{ message.gift_icon }}</span>
                    <span>{{ message.gift_name }}</span>
                  </div>
                </div>
                <div v-if="message.sender_id === currentUser?.id" class="chat-footer opacity-50">
                  {{ message.is_read ? 'Seen' : 'Delivered' }}
                </div>
              </div>

              <div v-if="isTyping" class="chat chat-start">
                <div class="chat-image avatar">
                  <div class="w-10 rounded-full">
                    <img :src="activeConversation.avatar" />
                  </div>
                </div>
                <div class="chat-bubble">
                  <span class="loading loading-dots loading-xs"></span>
                </div>
              </div>
            </div>

            <!-- Message Input -->
            <div class="p-4 border-t border-base-300">
              <div class="flex gap-2">
                <button class="btn btn-ghost btn-sm btn-circle" @click="showEmojiPicker = !showEmojiPicker">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                </button>
                <button class="btn btn-ghost btn-sm btn-circle">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </button>
                <button class="btn btn-ghost btn-sm btn-circle" @click="openGiftModal">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                  </svg>
                </button>
                <input
                  type="text"
                  v-model="messageInput"
                  :placeholder="$t('chat.type_message')"
                  class="input input-bordered flex-1"
                  @keyup.enter="sendMessage"
                  @input="handleTyping"
                />
                <button class="btn btn-primary" @click="sendMessage" :disabled="!messageInput.trim()">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import Header from '@/components/common/Header.vue';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const searchQuery = ref('');
const conversations = ref([]);
const activeConversation = ref(null);
const messages = ref([]);
const messageInput = ref('');
const isTyping = ref(false);
const showEmojiPicker = ref(false);
const messagesContainer = ref(null);

const currentUser = computed(() => authStore.currentUser);

const filteredConversations = computed(() => {
  if (!searchQuery.value) return conversations.value;
  return conversations.value.filter(conv =>
    conv.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const formatTime = (date) => {
  const now = new Date();
  const msgDate = new Date(date);
  const diff = now - msgDate;

  if (diff < 60000) return 'Just now';
  if (diff < 3600000) return Math.floor(diff / 60000) + 'm';
  if (diff < 86400000) return Math.floor(diff / 3600000) + 'h';
  if (diff < 604800000) return Math.floor(diff / 86400000) + 'd';

  return msgDate.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit' });
};

const selectConversation = async (conv) => {
  activeConversation.value = conv;

  // TODO: Fetch messages from API
  messages.value = [
    {
      id: 1,
      sender_id: conv.id,
      sender_name: conv.name,
      sender_avatar: conv.avatar,
      type: 'text',
      content: 'Hey, how are you?',
      created_at: new Date(Date.now() - 3600000),
      is_read: true,
    },
    {
      id: 2,
      sender_id: currentUser.value?.id,
      sender_name: currentUser.value?.username,
      sender_avatar: currentUser.value?.avatar,
      type: 'text',
      content: "I'm good! Thanks for asking!",
      created_at: new Date(Date.now() - 3000000),
      is_read: true,
    },
    {
      id: 3,
      sender_id: conv.id,
      sender_name: conv.name,
      sender_avatar: conv.avatar,
      type: 'text',
      content: 'Want to play some games later?',
      created_at: new Date(Date.now() - 1800000),
      is_read: true,
    },
  ];

  // Mark conversation as read
  conv.unread_count = 0;

  await nextTick();
  scrollToBottom();
};

const sendMessage = async () => {
  if (!messageInput.value.trim() || !activeConversation.value) return;

  const newMessage = {
    id: Date.now(),
    sender_id: currentUser.value?.id,
    sender_name: currentUser.value?.username,
    sender_avatar: currentUser.value?.avatar,
    type: 'text',
    content: messageInput.value,
    created_at: new Date(),
    is_read: false,
  };

  messages.value.push(newMessage);
  messageInput.value = '';

  // TODO: Send message via WebSocket/API

  await nextTick();
  scrollToBottom();
};

const handleTyping = () => {
  // TODO: Emit typing event via WebSocket
};

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
  }
};

const startVoiceCall = () => {
  notificationStore.info('Voice call feature coming soon!');
};

const startVideoCall = () => {
  notificationStore.info('Video call feature coming soon!');
};

const openGiftModal = () => {
  notificationStore.info('Gift sending feature coming soon!');
};

onMounted(() => {
  // TODO: Fetch conversations from API
  conversations.value = [
    {
      id: 2,
      name: 'Alice Johnson',
      avatar: 'https://i.pravatar.cc/150?img=2',
      last_message: 'See you tomorrow!',
      last_message_at: new Date(Date.now() - 300000),
      unread_count: 2,
      is_online: true,
    },
    {
      id: 3,
      name: 'Bob Smith',
      avatar: 'https://i.pravatar.cc/150?img=3',
      last_message: 'Thanks for the help!',
      last_message_at: new Date(Date.now() - 3600000),
      unread_count: 0,
      is_online: false,
    },
    {
      id: 4,
      name: 'Carol White',
      avatar: 'https://i.pravatar.cc/150?img=4',
      last_message: 'Did you see the new event?',
      last_message_at: new Date(Date.now() - 7200000),
      unread_count: 1,
      is_online: true,
    },
    {
      id: 5,
      name: 'David Brown',
      avatar: 'https://i.pravatar.cc/150?img=5',
      last_message: 'Let me know when you are free',
      last_message_at: new Date(Date.now() - 86400000),
      unread_count: 0,
      is_online: false,
    },
  ];

  // TODO: Setup WebSocket connection for real-time updates
});
</script>
