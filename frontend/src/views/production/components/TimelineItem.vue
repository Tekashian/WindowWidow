<template>
  <div class="space-y-4">
    <div v-for="(entry, index) in timeline" :key="entry.id" class="relative">
      <!-- Timeline Line -->
      <div
        v-if="index < timeline.length - 1"
        class="absolute left-4 top-12 bottom-0 w-0.5 bg-gradient-to-b from-cyan-500 to-purple-500"
      ></div>

      <!-- Timeline Item -->
      <div class="flex gap-4">
        <!-- Icon Circle -->
        <div class="relative z-10 flex-shrink-0">
          <div
            :class="[
              'w-8 h-8 rounded-full flex items-center justify-center',
              getStatusColor(entry.status)
            ]"
          >
            <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
              <path
                v-if="entry.status === 'completed'"
                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
              />
              <path
                v-else-if="entry.status === 'on_hold' || entry.status === 'cancelled'"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              />
              <path
                v-else
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z"
              />
            </svg>
          </div>
        </div>

        <!-- Content Card -->
        <div class="flex-1 bg-gray-800/50 border border-gray-700 rounded-lg p-4 hover:border-cyan-500/50 transition-all">
          <div class="flex justify-between items-start mb-2">
            <div>
              <StatusBadge :status="entry.status" />
              <div v-if="entry.delay_reason" class="mt-2 text-orange-400 text-sm">
                ⏰ Opóźnienie: {{ entry.delay_reason }}
              </div>
            </div>
            <div class="text-right text-sm">
              <div class="text-gray-400">
                {{ formatDateTime(entry.created_at) }}
              </div>
              <div v-if="entry.creator" class="text-cyan-400 mt-1">
                👤 {{ entry.creator.name }}
              </div>
            </div>
          </div>

          <p v-if="entry.notes" class="text-gray-300 text-sm mb-2">
            {{ entry.notes }}
          </p>

          <div v-if="entry.estimated_completion" class="text-sm text-gray-400">
            📅 Szacowany termin: {{ formatDateTime(entry.estimated_completion) }}
          </div>

          <!-- Issues -->
          <div v-if="entry.issues && entry.issues.length > 0" class="mt-3 space-y-2">
            <div
              v-for="(issue, idx) in entry.issues"
              :key="idx"
              class="bg-red-900/20 border border-red-700/50 rounded p-2 text-sm"
            >
              <div class="text-red-400 font-medium">⚠️ Problem: {{ issue.type }}</div>
              <div class="text-gray-300">{{ issue.description }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="timeline.length === 0" class="text-center py-8 text-gray-400">
      Brak wpisów w historii
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import StatusBadge from './StatusBadge.vue';

const props = defineProps({
  timeline: {
    type: Array,
    required: true,
    default: () => []
  }
});

const getStatusColor = (status) => {
  const colors = {
    pending: 'bg-gray-600',
    materials_check: 'bg-blue-600',
    materials_reserved: 'bg-cyan-600',
    in_progress: 'bg-purple-600',
    quality_check: 'bg-yellow-600',
    completed: 'bg-green-600',
    shipped_to_warehouse: 'bg-teal-600',
    delivered: 'bg-emerald-600',
    on_hold: 'bg-orange-600',
    cancelled: 'bg-red-600'
  };
  return colors[status] || colors.pending;
};

const formatDateTime = (dateString) => {
  if (!dateString) return '-';
  const date = new Date(dateString);
  return date.toLocaleDateString('pl-PL', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};
</script>
