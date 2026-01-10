<template>
  <div class="min-h-screen bg-base-200">
    <Header />

    <div class="container mx-auto px-4 py-6 max-w-4xl">
      <h1 class="text-3xl font-bold mb-6">⚙️ Settings</h1>

      <div class="space-y-6">
        <!-- Profile Settings -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">Profile Settings</h2>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Full Name</span>
              </label>
              <input
                type="text"
                v-model="form.full_name"
                class="input input-bordered"
                placeholder="Enter your full name"
              />
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Bio</span>
              </label>
              <textarea
                v-model="form.bio"
                class="textarea textarea-bordered"
                placeholder="Tell us about yourself"
                rows="3"
              ></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Gender</span>
                </label>
                <select v-model="form.gender" class="select select-bordered">
                  <option value="">Prefer not to say</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <div class="form-control">
                <label class="label">
                  <span class="label-text">Country</span>
                </label>
                <select v-model="form.country_code" class="select select-bordered">
                  <option value="VN">Vietnam</option>
                  <option value="US">United States</option>
                  <option value="KR">South Korea</option>
                  <option value="JP">Japan</option>
                  <option value="TH">Thailand</option>
                </select>
              </div>
            </div>

            <div class="card-actions justify-end mt-4">
              <button class="btn btn-primary" @click="saveProfile" :disabled="saving">
                <span v-if="saving" class="loading loading-spinner loading-sm"></span>
                {{ saving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </div>

        <!-- Account Settings -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">Account Settings</h2>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Email</span>
              </label>
              <input
                type="email"
                v-model="form.email"
                class="input input-bordered"
                placeholder="your@email.com"
              />
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Phone Number</span>
              </label>
              <input
                type="tel"
                v-model="form.phone"
                class="input input-bordered"
                placeholder="+84 XXX XXX XXX"
              />
            </div>

            <div class="divider"></div>

            <h3 class="font-bold mb-2">Change Password</h3>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Current Password</span>
              </label>
              <input
                type="password"
                v-model="passwordForm.current_password"
                class="input input-bordered"
                placeholder="••••••••"
              />
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">New Password</span>
              </label>
              <input
                type="password"
                v-model="passwordForm.new_password"
                class="input input-bordered"
                placeholder="••••••••"
              />
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Confirm New Password</span>
              </label>
              <input
                type="password"
                v-model="passwordForm.confirm_password"
                class="input input-bordered"
                placeholder="••••••••"
              />
            </div>

            <div class="card-actions justify-end mt-4">
              <button class="btn btn-secondary" @click="changePassword">
                Change Password
              </button>
            </div>
          </div>
        </div>

        <!-- Privacy Settings -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">Privacy Settings</h2>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Show online status</span>
                <input type="checkbox" v-model="privacy.show_online_status" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Allow friend requests</span>
                <input type="checkbox" v-model="privacy.allow_friend_requests" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Show profile to public</span>
                <input type="checkbox" v-model="privacy.public_profile" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Allow messages from non-friends</span>
                <input type="checkbox" v-model="privacy.allow_messages_from_strangers" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="card-actions justify-end mt-4">
              <button class="btn btn-primary" @click="savePrivacy">
                Save Privacy Settings
              </button>
            </div>
          </div>
        </div>

        <!-- Notification Settings -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">Notification Settings</h2>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Push notifications</span>
                <input type="checkbox" v-model="notifications.push_enabled" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Email notifications</span>
                <input type="checkbox" v-model="notifications.email_enabled" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">New message notifications</span>
                <input type="checkbox" v-model="notifications.new_message" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Friend request notifications</span>
                <input type="checkbox" v-model="notifications.friend_request" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="form-control">
              <label class="label cursor-pointer">
                <span class="label-text">Gift received notifications</span>
                <input type="checkbox" v-model="notifications.gift_received" class="toggle toggle-primary" />
              </label>
            </div>

            <div class="card-actions justify-end mt-4">
              <button class="btn btn-primary" @click="saveNotifications">
                Save Notification Settings
              </button>
            </div>
          </div>
        </div>

        <!-- Language Settings -->
        <div class="card bg-base-100 shadow-xl">
          <div class="card-body">
            <h2 class="card-title">Language & Region</h2>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Language</span>
              </label>
              <select v-model="language" class="select select-bordered" @change="changeLanguage">
                <option value="en">English</option>
                <option value="vi">Tiếng Việt</option>
              </select>
            </div>

            <div class="form-control">
              <label class="label">
                <span class="label-text">Theme</span>
              </label>
              <select v-model="theme" class="select select-bordered" @change="changeTheme">
                <option value="light">Light</option>
                <option value="dark">Dark</option>
                <option value="system">System Default</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Danger Zone -->
        <div class="card bg-base-100 shadow-xl border-2 border-error">
          <div class="card-body">
            <h2 class="card-title text-error">Danger Zone</h2>

            <div class="alert alert-warning">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
              </svg>
              <span>These actions are irreversible. Please be careful.</span>
            </div>

            <div class="flex flex-col gap-2 mt-4">
              <button class="btn btn-outline btn-error" @click="deleteAccount">
                Delete My Account
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useNotificationStore } from '@/stores/notification';
import { useI18n } from 'vue-i18n';
import Header from '@/components/common/Header.vue';

const router = useRouter();
const authStore = useAuthStore();
const notificationStore = useNotificationStore();
const { locale } = useI18n();

const saving = ref(false);
const form = ref({
  full_name: '',
  bio: '',
  gender: '',
  country_code: 'VN',
  email: '',
  phone: '',
});

const passwordForm = ref({
  current_password: '',
  new_password: '',
  confirm_password: '',
});

const privacy = ref({
  show_online_status: true,
  allow_friend_requests: true,
  public_profile: true,
  allow_messages_from_strangers: false,
});

const notifications = ref({
  push_enabled: true,
  email_enabled: true,
  new_message: true,
  friend_request: true,
  gift_received: true,
});

const language = ref('en');
const theme = ref('light');

const saveProfile = async () => {
  saving.value = true;
  try {
    // TODO: Update profile via API
    await new Promise(resolve => setTimeout(resolve, 1000));
    notificationStore.success('Profile updated successfully');
  } catch (error) {
    notificationStore.error('Failed to update profile');
  } finally {
    saving.value = false;
  }
};

const changePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.confirm_password) {
    notificationStore.error('Passwords do not match');
    return;
  }

  // TODO: Change password via API
  notificationStore.success('Password changed successfully');
  passwordForm.value = {
    current_password: '',
    new_password: '',
    confirm_password: '',
  };
};

const savePrivacy = async () => {
  // TODO: Update privacy settings via API
  notificationStore.success('Privacy settings updated');
};

const saveNotifications = async () => {
  // TODO: Update notification settings via API
  notificationStore.success('Notification settings updated');
};

const changeLanguage = () => {
  locale.value = language.value;
  localStorage.setItem('language', language.value);
  notificationStore.success('Language changed');
};

const changeTheme = () => {
  document.documentElement.setAttribute('data-theme', theme.value);
  localStorage.setItem('theme', theme.value);
  notificationStore.success('Theme changed');
};

const deleteAccount = () => {
  if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
    // TODO: Delete account via API
    notificationStore.info('Account deletion feature coming soon');
  }
};

onMounted(() => {
  // Load current user data
  const user = authStore.currentUser;
  if (user) {
    form.value = {
      full_name: user.full_name || '',
      bio: user.bio || '',
      gender: user.gender || '',
      country_code: user.country_code || 'VN',
      email: user.email || '',
      phone: user.phone || '',
    };
  }

  // Load saved preferences
  language.value = localStorage.getItem('language') || 'en';
  theme.value = localStorage.getItem('theme') || 'light';
});
</script>
