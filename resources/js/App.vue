<template>
  <div id="app" class="min-h-screen bg-base-200">
    <!-- Main App Content -->
    <router-view />

    <!-- Global Notification Toast -->
    <div class="toast toast-top toast-end z-50">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="alert"
        :class="{
          'alert-success': notification.type === 'success',
          'alert-error': notification.type === 'error',
          'alert-warning': notification.type === 'warning',
          'alert-info': notification.type === 'info',
        }"
      >
        <span>{{ notification.message }}</span>
      </div>
    </div>

    <!-- Loading Overlay -->
    <div v-if="isLoading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="loading loading-spinner loading-lg text-primary"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useAuthStore } from './stores/auth';
import { useNotificationStore } from './stores/notification';

const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const isLoading = computed(() => authStore.isLoading);
const notifications = computed(() => notificationStore.notifications);

onMounted(async () => {
  // Try to restore auth from local storage
  if (authStore.token) {
    await authStore.fetchUser();
  }
});
</script>

<style>
/* Custom Scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  @apply bg-base-300;
}

::-webkit-scrollbar-thumb {
  @apply bg-primary rounded-full;
}

::-webkit-scrollbar-thumb:hover {
  @apply bg-primary-focus;
}

/* Custom animations */
@keyframes gift-fly {
  0% {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
  50% {
    transform: translateY(-50vh) scale(1.5);
    opacity: 1;
  }
  100% {
    transform: translateY(-100vh) scale(0.5);
    opacity: 0;
  }
}

.gift-animation {
  animation: gift-fly 2s ease-out forwards;
}
</style>
