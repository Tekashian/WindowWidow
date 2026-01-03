<template>
  <div class="progress-bar-container">
    <div class="progress-header">
      <span class="progress-label">{{ label }}</span>
      <span class="progress-percentage">{{ percentage }}%</span>
    </div>
    <div class="progress-track">
      <div class="progress-bar" :class="barColorClass" :style="{ width: `${percentage}%` }">
        <div class="progress-shimmer"></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  percentage: {
    type: Number,
    required: true,
    validator: (value) => value >= 0 && value <= 100
  },
  label: {
    type: String,
    default: 'Postęp'
  },
  color: {
    type: String,
    default: 'cyan',
    validator: (value) => ['cyan', 'purple', 'green', 'yellow', 'red'].includes(value)
  }
});

const barColorClass = computed(() => {
  const colors = {
    cyan: 'bar-cyan',
    purple: 'bar-purple',
    green: 'bar-green',
    yellow: 'bar-yellow',
    red: 'bar-red'
  };
  return colors[props.color] || colors.cyan;
});
</script>

<style scoped>
.progress-bar-container {
  width: 100%;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.progress-label {
  color: var(--gray-400);
}

.progress-percentage {
  color: var(--primary);
  font-weight: 500;
}

.progress-track {
  width: 100%;
  background: rgba(31, 41, 55, 1);
  border-radius: 9999px;
  height: 0.5rem;
  overflow: hidden;
}

.progress-bar {
  height: 100%;
  border-radius: 9999px;
  transition: width 500ms cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.progress-shimmer {
  position: absolute;
  top: 0;
  right: 0;
  bottom: 0;
  left: 0;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  animation: shimmer 2s ease-in-out infinite;
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

.bar-cyan {
  background: linear-gradient(90deg, #0891B2, #06B6D4);
}

.bar-purple {
  background: linear-gradient(90deg, #7C3AED, #A855F7);
}

.bar-green {
  background: linear-gradient(90deg, #10B981, #34D399);
}

.bar-yellow {
  background: linear-gradient(90deg, #EAB308, #FCD34D);
}

.bar-red {
  background: linear-gradient(90deg, #DC2626, #EF4444);
}
</style>
