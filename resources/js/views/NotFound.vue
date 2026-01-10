<template>
  <div class="min-h-screen bg-base-200 flex items-center justify-center">
    <div class="text-center px-4">
      <!-- 404 Animation -->
      <div class="text-9xl font-bold text-primary mb-4 animate-bounce">
        404
      </div>

      <!-- Sad Face Icon -->
      <div class="text-6xl mb-6">
        😕
      </div>

      <!-- Error Message -->
      <h1 class="text-4xl font-bold mb-4">Page Not Found</h1>
      <p class="text-xl text-base-content/70 mb-8 max-w-md mx-auto">
        Oops! The page you're looking for doesn't exist. It might have been moved or deleted.
      </p>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <button class="btn btn-primary gap-2" @click="goHome">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Go Home
        </button>

        <button class="btn btn-ghost gap-2" @click="goBack">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Go Back
        </button>
      </div>

      <!-- Popular Links -->
      <div class="mt-12">
        <p class="text-sm text-base-content/60 mb-4">You might be interested in:</p>
        <div class="flex flex-wrap gap-2 justify-center">
          <router-link to="/" class="btn btn-sm btn-outline">Home</router-link>
          <router-link to="/chat" class="btn btn-sm btn-outline">Chat</router-link>
          <router-link to="/discover" class="btn btn-sm btn-outline">Discover</router-link>
          <router-link to="/live" class="btn btn-sm btn-outline">Live Streams</router-link>
          <router-link to="/games" class="btn btn-sm btn-outline">Games</router-link>
        </div>
      </div>

      <!-- Fun Facts -->
      <div class="mt-12 max-w-md mx-auto">
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h3 class="card-title text-sm">💡 Did you know?</h3>
            <p class="text-sm text-base-content/70">
              The HTTP 404 error code was named after room 404 at CERN, where the original web servers were located.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';

const router = useRouter();

const goHome = () => {
  router.push('/');
};

const goBack = () => {
  router.go(-1);
};
</script>
