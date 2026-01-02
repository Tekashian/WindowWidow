import { defineStore } from 'pinia';
import notificationApi from '../services/notificationApi';

export const useNotificationStore = defineStore('notifications', {
  state: () => ({
    notifications: [],
    unreadCount: 0,
    loading: false,
    error: null,
    pollInterval: null
  }),

  getters: {
    unreadNotifications: (state) => {
      return state.notifications.filter(n => !n.read);
    },

    productionNotifications: (state) => {
      return state.notifications.filter(n => n.type === 'production');
    },

    warehouseNotifications: (state) => {
      return state.notifications.filter(n => n.type === 'warehouse');
    },

    criticalNotifications: (state) => {
      return state.notifications.filter(n => n.priority === 'critical');
    },

    hasUnread: (state) => {
      return state.unreadCount > 0;
    }
  },

  actions: {
    /**
     * Fetch notifications
     */
    async fetchNotifications(params = {}) {
      this.loading = true;
      this.error = null;

      try {
        const data = await notificationApi.getNotifications(params);
        this.notifications = data.data;
        this.unreadCount = data.unread_count;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch notifications';
        console.error('Failed to fetch notifications:', error);
      } finally {
        this.loading = false;
      }
    },

    /**
     * Fetch unread count only
     */
    async fetchUnreadCount() {
      try {
        const data = await notificationApi.getUnreadCount();
        this.unreadCount = data.count;
      } catch (error) {
        console.error('Failed to fetch unread count:', error);
      }
    },

    /**
     * Mark notification as read
     */
    async markAsRead(id) {
      try {
        await notificationApi.markAsRead(id);
        
        const notification = this.notifications.find(n => n.id === id);
        if (notification) {
          notification.read = true;
          notification.read_at = new Date().toISOString();
        }
        
        this.unreadCount = Math.max(0, this.unreadCount - 1);
      } catch (error) {
        console.error('Failed to mark as read:', error);
      }
    },

    /**
     * Mark all as read
     */
    async markAllAsRead() {
      try {
        await notificationApi.markAllAsRead();
        
        this.notifications.forEach(n => {
          n.read = true;
          n.read_at = new Date().toISOString();
        });
        
        this.unreadCount = 0;
      } catch (error) {
        console.error('Failed to mark all as read:', error);
      }
    },

    /**
     * Delete notification
     */
    async deleteNotification(id) {
      try {
        await notificationApi.deleteNotification(id);
        
        const index = this.notifications.findIndex(n => n.id === id);
        if (index !== -1) {
          const wasUnread = !this.notifications[index].read;
          this.notifications.splice(index, 1);
          
          if (wasUnread) {
            this.unreadCount = Math.max(0, this.unreadCount - 1);
          }
        }
      } catch (error) {
        console.error('Failed to delete notification:', error);
      }
    },

    /**
     * Delete all read notifications
     */
    async deleteAllRead() {
      try {
        await notificationApi.deleteAllRead();
        this.notifications = this.notifications.filter(n => !n.read);
      } catch (error) {
        console.error('Failed to delete read notifications:', error);
      }
    },

    /**
     * Start polling for new notifications
     */
    startPolling(interval = 30000) {
      // Stop existing polling
      this.stopPolling();

      // Initial fetch
      this.fetchUnreadCount();

      // Start polling
      this.pollInterval = setInterval(() => {
        this.fetchUnreadCount();
      }, interval);
    },

    /**
     * Stop polling
     */
    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
        this.pollInterval = null;
      }
    }
  }
});
