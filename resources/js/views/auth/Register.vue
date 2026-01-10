<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary/20 to-secondary/20 p-4">
    <div class="card w-full max-w-2xl bg-base-100 shadow-2xl">
      <div class="card-body">
        <!-- Logo & Title -->
        <div class="text-center mb-6">
          <h1 class="text-4xl font-bold text-primary mb-2">{{ $t('common.app_name') }}</h1>
          <p class="text-base-content/70">{{ $t('auth.register_title') }}</p>
        </div>

        <!-- Register Form -->
        <form @submit.prevent="handleRegister">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Username -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('auth.username') }} *</span>
              </label>
              <input
                v-model="form.username"
                type="text"
                :placeholder="$t('auth.username')"
                class="input input-bordered"
                :class="{ 'input-error': errors.username }"
                required
              />
              <label v-if="errors.username" class="label">
                <span class="label-text-alt text-error">{{ errors.username[0] }}</span>
              </label>
            </div>

            <!-- Email -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('auth.email') }} *</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                :placeholder="$t('auth.email')"
                class="input input-bordered"
                :class="{ 'input-error': errors.email }"
              />
              <label v-if="errors.email" class="label">
                <span class="label-text-alt text-error">{{ errors.email[0] }}</span>
              </label>
            </div>

            <!-- Phone -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('auth.phone') }}</span>
              </label>
              <input
                v-model="form.phone"
                type="tel"
                :placeholder="$t('auth.phone')"
                class="input input-bordered"
                :class="{ 'input-error': errors.phone }"
              />
              <label v-if="errors.phone" class="label">
                <span class="label-text-alt text-error">{{ errors.phone[0] }}</span>
              </label>
            </div>

            <!-- Full Name -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('profile.full_name') }}</span>
              </label>
              <input
                v-model="form.full_name"
                type="text"
                placeholder="John Doe"
                class="input input-bordered"
              />
            </div>

            <!-- Password -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('auth.password') }} *</span>
              </label>
              <input
                v-model="form.password"
                type="password"
                :placeholder="$t('auth.password')"
                class="input input-bordered"
                :class="{ 'input-error': errors.password }"
                required
              />
              <label v-if="errors.password" class="label">
                <span class="label-text-alt text-error">{{ errors.password[0] }}</span>
              </label>
            </div>

            <!-- Confirm Password -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('auth.password_confirm') }} *</span>
              </label>
              <input
                v-model="form.password_confirmation"
                type="password"
                :placeholder="$t('auth.password_confirm')"
                class="input input-bordered"
                required
              />
            </div>

            <!-- Gender -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('profile.gender') }}</span>
              </label>
              <select v-model="form.gender" class="select select-bordered">
                <option value="">Select Gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Language -->
            <div class="form-control">
              <label class="label">
                <span class="label-text">{{ $t('common.language') }}</span>
              </label>
              <select v-model="form.language" class="select select-bordered">
                <option value="vi">Tiếng Việt</option>
                <option value="en">English</option>
              </select>
            </div>
          </div>

          <!-- Terms & Conditions -->
          <div class="form-control mt-4">
            <label class="label cursor-pointer justify-start">
              <input v-model="form.agree_terms" type="checkbox" class="checkbox checkbox-primary" required />
              <span class="label-text ml-3">
                I agree to the <a href="#" class="link link-primary">Terms & Conditions</a> and <a href="#" class="link link-primary">Privacy Policy</a>
              </span>
            </label>
          </div>

          <!-- Register Button -->
          <div class="form-control mt-6">
            <button type="submit" class="btn btn-primary btn-lg" :disabled="isLoading">
              <span v-if="isLoading" class="loading loading-spinner"></span>
              {{ isLoading ? $t('common.loading') : $t('common.register') }}
            </button>
          </div>
        </form>

        <!-- Divider -->
        <div class="divider">OR</div>

        <!-- Social Register -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
          <button @click="handleSocialLogin('google')" class="btn btn-outline">
            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
              <path fill="currentColor" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
              <path fill="currentColor" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
              <path fill="currentColor" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
              <path fill="currentColor" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
            </svg>
            Google
          </button>

          <button @click="handleSocialLogin('facebook')" class="btn btn-outline">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
              <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
            </svg>
            Facebook
          </button>
        </div>

        <!-- Login Link -->
        <div class="text-center mt-6">
          <p class="text-sm text-base-content/70">
            {{ $t('auth.already_have_account') }}
            <router-link to="/login" class="link link-primary font-semibold">
              {{ $t('common.login') }}
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();

const isLoading = ref(false);
const errors = ref({});

const form = reactive({
  username: '',
  email: '',
  phone: '',
  password: '',
  password_confirmation: '',
  full_name: '',
  gender: '',
  language: 'vi',
  agree_terms: false,
});

const handleRegister = async () => {
  if (!form.agree_terms) {
    notificationStore.error('Please agree to Terms & Conditions');
    return;
  }

  isLoading.value = true;
  errors.value = {};

  const result = await authStore.register(form);

  isLoading.value = false;

  if (result.success) {
    notificationStore.success(t('auth.register_success'));
    router.push('/');
  } else {
    notificationStore.error(result.message || 'Registration failed');
    errors.value = result.errors || {};
  }
};

const handleSocialLogin = (provider) => {
  const width = 600;
  const height = 700;
  const left = window.screen.width / 2 - width / 2;
  const top = window.screen.height / 2 - height / 2;

  window.open(
    `/api/v1/chat/auth/oauth/${provider}`,
    'oauth',
    `width=${width},height=${height},top=${top},left=${left}`
  );
};
</script>
