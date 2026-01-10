import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token') || null,
    isLoading: false,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    currentUser: (state) => state.user,
    isVip: (state) => state.user?.is_vip || false,
    userLevel: (state) => state.user?.level || 1,
    userBalance: (state) => state.user?.balance || 0,
  },

  actions: {
    async login(credentials) {
      this.isLoading = true;
      try {
        const response = await axios.post('/api/v1/chat/auth/login', credentials);

        if (response.data.success) {
          this.token = response.data.data.token;
          this.user = response.data.data.user;

          localStorage.setItem('token', this.token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

          return { success: true };
        }

        return { success: false, message: response.data.message };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Login failed',
        };
      } finally {
        this.isLoading = false;
      }
    },

    async register(data) {
      this.isLoading = true;
      try {
        const response = await axios.post('/api/v1/chat/auth/register', data);

        if (response.data.success) {
          this.token = response.data.data.token;
          this.user = response.data.data.user;

          localStorage.setItem('token', this.token);
          axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

          return { success: true };
        }

        return { success: false, message: response.data.message };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Registration failed',
          errors: error.response?.data?.errors || {},
        };
      } finally {
        this.isLoading = false;
      }
    },

    async logout() {
      this.isLoading = true;
      try {
        await axios.post('/api/v1/chat/auth/logout');
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
        this.isLoading = false;
      }
    },

    async fetchUser() {
      if (!this.token) return;

      this.isLoading = true;
      try {
        const response = await axios.get('/api/v1/chat/auth/me');

        if (response.data.success) {
          this.user = response.data.data.user;
        }
      } catch (error) {
        console.error('Fetch user error:', error);
        // Token might be invalid, logout
        this.logout();
      } finally {
        this.isLoading = false;
      }
    },

    async updateProfile(data) {
      this.isLoading = true;
      try {
        const response = await axios.put('/api/v1/chat/user/profile', data);

        if (response.data.success) {
          this.user = response.data.data.user;
          return { success: true };
        }

        return { success: false, message: response.data.message };
      } catch (error) {
        return {
          success: false,
          message: error.response?.data?.message || 'Update failed',
        };
      } finally {
        this.isLoading = false;
      }
    },

    setToken(token) {
      this.token = token;
      localStorage.setItem('token', token);
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
    },
  },
});
