<template>
  <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center bg-base-300/50 backdrop-blur-sm">
    <div class="card bg-base-100 shadow-2xl">
      <div class="card-body items-center text-center">
        <!-- Loading Animation -->
        <div class="relative w-24 h-24">
          <span class="loading loading-spinner loading-lg text-primary"></span>
        </div>

        <!-- Loading Text -->
        <h3 v-if="title" class="text-xl font-bold mt-4">{{ title }}</h3>
        <p v-if="message" class="text-base-content/70">{{ message }}</p>

        <!-- Progress Bar (if provided) -->
        <div v-if="progress !== null" class="w-64 mt-4">
          <div class="flex justify-between text-xs mb-1">
            <span>{{ progress }}%</span>
          </div>
          <progress class="progress progress-primary w-full" :value="progress" max="100"></progress>
        </div>

        <!-- Cancel Button (if cancellable) -->
        <button v-if="cancellable" class="btn btn-ghost btn-sm mt-4" @click="handleCancel">
          Cancel
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Loading...',
  },
  message: {
    type: String,
    default: '',
  },
  progress: {
    type: Number,
    default: null,
  },
  cancellable: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['cancel']);

const handleCancel = () => {
  emit('cancel');
};
</script>
