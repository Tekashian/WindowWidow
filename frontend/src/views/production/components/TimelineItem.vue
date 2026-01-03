<template>
  <div class="timeline-container">
    <div v-for="(entry, index) in timeline" :key="entry.id" class="timeline-item">
      <!-- Timeline Line -->
      <div v-if="index < timeline.length - 1" class="timeline-line"></div>

      <!-- Timeline Item -->
      <div class="timeline-content">
        <!-- Icon Circle -->
        <div class="timeline-icon-wrapper">
          <div :class="['timeline-icon', getStatusColor(entry.status)]">
            <svg class="icon-svg" fill="currentColor" viewBox="0 0 20 20">
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
        <div class="timeline-card">
          <div class="card-header">
            <div class="header-left">
              <StatusBadge :status="entry.status" />
              <div v-if="entry.delay_reason" class="delay-reason">
                ⏰ Opóźnienie: {{ entry.delay_reason }}
              </div>
            </div>
            <div class="header-right">
              <div class="meta-date">{{ formatDateTime(entry.created_at) }}</div>
              <div v-if="entry.creator" class="meta-creator">👤 {{ entry.creator.name }}</div>
            </div>
          </div>

          <p v-if="entry.notes" class="card-notes">{{ entry.notes }}</p>

          <div v-if="entry.estimated_completion" class="card-completion">
            📅 Szacowany termin: {{ formatDateTime(entry.estimated_completion) }}
          </div>

          <!-- Issues -->
          <div v-if="entry.issues && entry.issues.length > 0" class="issues-list">
            <div v-for="(issue, idx) in entry.issues" :key="idx" class="issue-item">
              <div class="issue-title">⚠️ Problem: {{ issue.type }}</div>
              <div class="issue-description">{{ issue.description }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="timeline.length === 0" class="empty-timeline">
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
    pending: 'icon-gray',
    materials_check: 'icon-blue',
    materials_reserved: 'icon-cyan',
    in_progress: 'icon-purple',
    quality_check: 'icon-yellow',
    completed: 'icon-green',
    shipped_to_warehouse: 'icon-teal',
    delivered: 'icon-emerald',
    on_hold: 'icon-orange',
    cancelled: 'icon-red'
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

<style scoped>
.timeline-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.timeline-item {
  position: relative;
}

.timeline-line {
  position: absolute;
  left: 1rem;
  top: 3rem;
  bottom: -1rem;
  width: 2px;
  background: linear-gradient(180deg, #00F5FF, #7C3AED);
}

.timeline-content {
  display: flex;
  gap: 1rem;
}

.timeline-icon-wrapper {
  position: relative;
  z-index: 10;
  flex-shrink: 0;
}

.timeline-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.icon-svg {
  width: 1rem;
  height: 1rem;
  color: white;
}

.icon-gray {
  background: #6B7280;
}

.icon-blue {
  background: #3B82F6;
}

.icon-cyan {
  background: #06B6D4;
}

.icon-purple {
  background: #7C3AED;
}

.icon-yellow {
  background: #EAB308;
}

.icon-green {
  background: #10B981;
}

.icon-teal {
  background: #14B8A6;
}

.icon-emerald {
  background: #059669;
}

.icon-orange {
  background: #F59E0B;
}

.icon-red {
  background: #EF4444;
}

.timeline-card {
  flex: 1;
  background: rgba(31, 41, 55, 0.5);
  border: 1px solid var(--border);
  border-radius: 0.5rem;
  padding: 1rem;
  transition: all var(--transition-base);
}

.timeline-card:hover {
  border-color: rgba(0, 245, 255, 0.5);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 0.5rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.header-left {
  flex: 1;
}

.delay-reason {
  margin-top: 0.5rem;
  color: #FBBF24;
  font-size: 0.875rem;
}

.header-right {
  text-align: right;
  font-size: 0.875rem;
}

.meta-date {
  color: var(--gray-400);
}

.meta-creator {
  color: var(--primary);
  margin-top: 0.25rem;
}

.card-notes {
  color: var(--gray-300);
  font-size: 0.875rem;
  margin-bottom: 0.5rem;
}

.card-completion {
  font-size: 0.875rem;
  color: var(--gray-400);
}

.issues-list {
  margin-top: 0.75rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.issue-item {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  border-radius: 0.375rem;
  padding: 0.5rem;
  font-size: 0.875rem;
}

.issue-title {
  color: #F87171;
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.issue-description {
  color: var(--gray-300);
}

.empty-timeline {
  text-align: center;
  padding: 2rem;
  color: var(--gray-400);
}
</style>
