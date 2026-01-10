import { defineStore } from 'pinia';

export const useNotificationStore = defineStore('notification', {
  state: () => ({
    notifications: [],
  }),

  actions: {
    add(notification) {
      const id = Date.now();
      this.notifications.push({
        id,
        type: notification.type || 'info',
        message: notification.message,
      });

      // Auto remove after 3 seconds
      setTimeout(() => {
        this.remove(id);
      }, 3000);
    },

    success(message) {
      this.add({ type: 'success', message });
    },

    error(message) {
      this.add({ type: 'error', message });
    },

    warning(message) {
      this.add({ type: 'warning', message });
    },

    info(message) {
      this.add({ type: 'info', message });
    },

    remove(id) {
      const index = this.notifications.findIndex(n => n.id === id);
      if (index > -1) {
        this.notifications.splice(index, 1);
      }
    },
  },
});
