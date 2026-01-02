<template>
  <div class="relative">
    <!-- Notification Bell -->
    <button
      @click="togglePanel"
      class="relative p-2 text-gray-400 hover:text-cyan-400 transition-colors"
      :class="{ 'text-cyan-400': showPanel }"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
      </svg>
      
      <!-- Unread Badge -->
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center animate-pulse"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Notifications Panel -->
    <transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 translate-y-1"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 translate-y-1"
    >
      <div
        v-if="showPanel"
        v-click-outside="closePanel"
        class="absolute right-0 mt-2 w-96 max-w-[calc(100vw-2rem)] bg-gray-800 border border-gray-700 rounded-xl shadow-2xl z-50 overflow-hidden"
      >
        <!-- Header -->
        <div class="bg-gradient-to-r from-cyan-600 to-purple-600 px-4 py-3 flex items-center justify-between">
          <h3 class="text-white font-bold">🔔 Powiadomienia</h3>
          <div class="flex items-center gap-2">
            <button
              v-if="unreadCount > 0"
              @click="markAllRead"
              class="text-white/80 hover:text-white text-xs transition-colors"
              title="Oznacz wszystkie jako przeczytane"
            >
              ✓ Wszystkie
            </button>
            <button
              @click="deleteAllReadNotifications"
              class="text-white/80 hover:text-white text-xs transition-colors"
              title="Usuń przeczytane"
            >
              🗑️
            </button>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
          <div v-if="loading" class="p-8 text-center text-gray-400">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-cyan-500"></div>
            <p class="mt-2">Ładowanie...</p>
          </div>

          <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-400">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <p>Brak powiadomień</p>
          </div>

          <div v-else>
            <div
              v-for="notification in notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              :class="[
                'px-4 py-3 border-b border-gray-700 cursor-pointer transition-colors',
                notification.read ? 'bg-gray-900/30' : 'bg-cyan-900/10 hover:bg-cyan-900/20'
              ]"
            >
              <div class="flex items-start gap-3">
                <!-- Icon -->
                <div class="text-2xl flex-shrink-0">
                  {{ notification.icon || '🔔' }}
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <h4 class="text-white font-medium text-sm">
                      {{ notification.title }}
                    </h4>
                    <span
                      v-if="notification.priority === 'critical'"
                      class="text-red-500 text-xs font-bold flex-shrink-0"
                    >
                      KRYTYCZNE
                    </span>
                  </div>
                  
                  <p class="text-gray-400 text-sm mb-2">
                    {{ notification.message }}
                  </p>
                  
                  <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-500">
                      {{ formatTime(notification.created_at) }}
                    </span>
                    
                    <button
                      @click.stop="deleteNotification(notification.id)"
                      class="text-gray-500 hover:text-red-400 transition-colors"
                      title="Usuń"
                    >
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="bg-gray-900/50 px-4 py-2 text-center">
          <button
            @click="closePanel"
            class="text-gray-400 hover:text-cyan-400 text-sm transition-colors"
          >
            Zamknij
          </button>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useNotificationStore } from '../stores/notificationStore';

const router = useRouter();
const notificationStore = useNotificationStore();

const showPanel = ref(false);

const notifications = computed(() => notificationStore.notifications);
const unreadCount = computed(() => notificationStore.unreadCount);
const loading = computed(() => notificationStore.loading);

const togglePanel = () => {
  showPanel.value = !showPanel.value;
  
  if (showPanel.value) {
    notificationStore.fetchNotifications({ limit: 20 });
  }
};

const closePanel = () => {
  showPanel.value = false;
};

const markAllRead = async () => {
  await notificationStore.markAllAsRead();
};

const deleteAllReadNotifications = async () => {
  await notificationStore.deleteAllRead();
};

const deleteNotification = async (id) => {
  await notificationStore.deleteNotification(id);
};

const handleNotificationClick = async (notification) => {
  // Mark as read
  if (!notification.read) {
    await notificationStore.markAsRead(notification.id);
  }

  // Navigate to link if exists
  if (notification.link) {
    closePanel();
    router.push(notification.link);
  }
};

const formatTime = (timestamp) => {
  const date = new Date(timestamp);
  const now = new Date();
  const diffMs = now - date;
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'Teraz';
  if (diffMins < 60) return `${diffMins} min temu`;
  if (diffHours < 24) return `${diffHours} godz. temu`;
  if (diffDays < 7) return `${diffDays} dni temu`;
  
  return date.toLocaleDateString('pl-PL', { 
    day: 'numeric', 
    month: 'short',
    hour: '2-digit',
    minute: '2-digit'
  });
};

// Click outside directive
const vClickOutside = {
  mounted(el, binding) {
    el.clickOutsideEvent = (event) => {
      if (!(el === event.target || el.contains(event.target))) {
        binding.value();
      }
    };
    document.addEventListener('click', el.clickOutsideEvent);
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickOutsideEvent);
  }
};

// Start polling on mount
onMounted(() => {
  notificationStore.startPolling(30000); // Poll every 30 seconds
});

// Stop polling on unmount
onUnmounted(() => {
  notificationStore.stopPolling();
});
</script>
