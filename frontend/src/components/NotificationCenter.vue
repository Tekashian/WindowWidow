<template>
  <div class="notification-center">
    <!-- Notification Bell -->
    <button @click="togglePanel" :class="['bell-btn', { 'bell-active': showPanel }]">
      <svg class="bell-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      
      <!-- Unread Badge -->
      <span v-if="unreadCount > 0" class="unread-badge">
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Notifications Panel -->
    <transition name="panel">
      <div v-if="showPanel" ref="panelRef" class="notifications-panel">
        <!-- Header -->
        <div class="panel-header">
          <h3 class="panel-title">🔔 Powiadomienia</h3>
          <div class="panel-actions">
            <button v-if="unreadCount > 0" @click="markAllRead" class="action-btn" title="Oznacz wszystkie jako przeczytane">
              ✓ Wszystkie
            </button>
            <button @click="deleteAllReadNotifications" class="action-btn" title="Usuń przeczytane">
              🗑️
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="notifications-list">
          <div v-if="loading" class="loading-container">
            <div class="spinner"></div>
            <p class="loading-text">Ładowanie...</p>
          </div>

          <div v-else-if="notifications.length === 0" class="empty-container">
            <svg class="empty-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p class="empty-text">Brak powiadomień</p>
          </div>

          <div v-else>
            <div
              v-for="notification in notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              :class="['notification-item', { 'notification-unread': !notification.read }]"
            >
              <div class="notification-content">
                <!-- Icon -->
                <div class="notification-icon">{{ notification.icon || '🔔' }}</div>

                <!-- Content -->
                <div class="notification-body">
                  <div class="notification-header">
                    <h4 class="notification-title">{{ notification.title }}</h4>
                    <span v-if="notification.priority === 'critical'" class="priority-badge">
                      KRYTYCZNE
                    </span>
                  </div>

                  <p class="notification-message">{{ notification.message }}</p>

                  <div class="notification-footer">
                    <span class="notification-time">{{ formatTime(notification.created_at) }}</span>
                    <span v-if="!notification.read" class="unread-dot"></span>
                  </div>
                </div>

                <!-- Delete Button -->
                <button @click.stop="deleteNotification(notification.id)" class="delete-btn" title="Usuń">
                  ✕
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useNotificationStore } from '../stores/notificationStore';
import { useRouter } from 'vue-router';

const router = useRouter();
const notificationStore = useNotificationStore();

const showPanel = ref(false);
const panelRef = ref(null);

const notifications = computed(() => notificationStore.notifications);
const unreadCount = computed(() => notificationStore.unreadCount);
const loading = computed(() => notificationStore.loading);

const togglePanel = () => {
  showPanel.value = !showPanel.value;
};

const closePanel = () => {
  showPanel.value = false;
};

const handleNotificationClick = async (notification) => {
  if (!notification.read) {
    await notificationStore.markAsRead(notification.id);
  }
  
  if (notification.link) {
    router.push(notification.link);
    closePanel();
  }
};

const markAllRead = async () => {
  await notificationStore.markAllAsRead();
};

const deleteNotification = async (id) => {
  await notificationStore.deleteNotification(id);
};

const deleteAllReadNotifications = async () => {
  await notificationStore.deleteReadNotifications();
};

const formatTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Teraz';
  if (diffMins < 60) return `${diffMins} min temu`;
  if (diffHours < 24) return `${diffHours}h temu`;
  if (diffDays < 7) return `${diffDays} dni temu`;
  
  return date.toLocaleDateString('pl-PL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
};

// Handle click outside
const handleClickOutside = (event) => {
  if (panelRef.value && !panelRef.value.contains(event.target) && !event.target.closest('.bell-btn')) {
    closePanel();
  }
};

watch(showPanel, (newValue) => {
  if (newValue) {
    document.addEventListener('click', handleClickOutside);
  } else {
    document.removeEventListener('click', handleClickOutside);
  }
});

onMounted(() => {
  notificationStore.fetchNotifications();
  
  // Poll for new notifications every 30 seconds
  const interval = setInterval(() => {
    notificationStore.fetchNotifications();
  }, 30000);

  onUnmounted(() => {
    clearInterval(interval);
    document.removeEventListener('click', handleClickOutside);
  });
});
</script>

<style scoped>
.notification-center {
  position: relative;
}

/* Bell Button */
.bell-btn {
  position: relative;
  padding: 0.5rem;
  color: var(--gray-400);
  background: none;
  border: none;
  cursor: pointer;
  transition: color var(--transition-base);
}

.bell-btn:hover,
.bell-active {
  color: var(--primary);
}

.bell-icon {
  width: 1.5rem;
  height: 1.5rem;
}

.unread-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: #EF4444;
  color: white;
  font-size: 0.75rem;
  font-weight: 700;
  border-radius: 9999px;
  width: 1.25rem;
  height: 1.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

/* Panel Transitions */
.panel-enter-active {
  transition: opacity 200ms ease-out, transform 200ms ease-out;
}

.panel-leave-active {
  transition: opacity 150ms ease-in, transform 150ms ease-in;
}

.panel-enter-from,
.panel-leave-to {
  opacity: 0;
  transform: translateY(0.25rem);
}

.panel-enter-to,
.panel-leave-from {
  opacity: 1;
  transform: translateY(0);
}

/* Notifications Panel */
.notifications-panel {
  position: absolute;
  right: 0;
  margin-top: 0.5rem;
  width: 24rem;
  max-width: calc(100vw - 2rem);
  background: rgba(31, 41, 55, 1);
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
  z-index: 50;
  overflow: hidden;
}

/* Panel Header */
.panel-header {
  background: linear-gradient(90deg, #0891B2, #7C3AED);
  padding: 0.75rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.panel-title {
  color: white;
  font-weight: 700;
  font-size: 1rem;
}

.panel-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.action-btn {
  color: rgba(255, 255, 255, 0.8);
  background: none;
  border: none;
  font-size: 0.75rem;
  cursor: pointer;
  transition: color var(--transition-base);
  padding: 0.25rem 0.5rem;
}

.action-btn:hover {
  color: white;
}

/* Notifications List */
.notifications-list {
  max-height: 24rem;
  overflow-y: auto;
}

/* Loading & Empty States */
.loading-container,
.empty-container {
  padding: 2rem;
  text-align: center;
  color: var(--gray-400);
}

.spinner {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 2px solid var(--gray-700);
  border-top-color: var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.loading-text {
  margin-top: 0.5rem;
}

.empty-icon {
  width: 4rem;
  height: 4rem;
  margin: 0 auto 0.5rem;
  color: var(--gray-600);
}

.empty-text {
  color: var(--gray-400);
}

/* Notification Item */
.notification-item {
  padding: 0.75rem 1rem;
  border-bottom: 1px solid var(--border);
  cursor: pointer;
  transition: background var(--transition-base);
  background: rgba(17, 24, 39, 0.3);
}

.notification-unread {
  background: rgba(6, 182, 212, 0.1);
}

.notification-unread:hover {
  background: rgba(6, 182, 212, 0.2);
}

.notification-item:hover {
  background: rgba(17, 24, 39, 0.5);
}

.notification-content {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
}

.notification-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
  line-height: 1;
}

.notification-body {
  flex: 1;
  min-width: 0;
}

.notification-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 0.5rem;
  margin-bottom: 0.25rem;
}

.notification-title {
  color: white;
  font-weight: 500;
  font-size: 0.875rem;
  line-height: 1.4;
}

.priority-badge {
  color: #EF4444;
  font-size: 0.625rem;
  font-weight: 700;
  flex-shrink: 0;
}

.notification-message {
  color: var(--gray-300);
  font-size: 0.8125rem;
  line-height: 1.5;
  margin-bottom: 0.5rem;
  word-wrap: break-word;
}

.notification-footer {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.notification-time {
  color: var(--gray-500);
  font-size: 0.75rem;
}

.unread-dot {
  width: 0.5rem;
  height: 0.5rem;
  background: var(--primary);
  border-radius: 50%;
  display: inline-block;
}

.delete-btn {
  background: none;
  border: none;
  color: var(--gray-500);
  cursor: pointer;
  padding: 0.25rem 0.5rem;
  transition: color var(--transition-base);
  flex-shrink: 0;
  font-size: 1.25rem;
  line-height: 1;
}

.delete-btn:hover {
  color: #EF4444;
}

/* Responsive */
@media (max-width: 640px) {
  .notifications-panel {
    width: calc(100vw - 2rem);
  }
}
</style>
