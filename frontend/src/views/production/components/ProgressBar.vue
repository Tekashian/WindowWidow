<template>
  <div class="w-full">
    <div class="flex justify-between text-sm mb-1">
      <span class="text-gray-400">{{ label }}</span>
      <span class="text-cyan-400 font-medium">{{ percentage }}%</span>
    </div>
    <div class="w-full bg-gray-800 rounded-full h-2 overflow-hidden">
      <div
        class="h-full rounded-full transition-all duration-500 ease-out"
        :class="barColor"
        :style="{ width: `${percentage}%` }"
      >
        <div class="h-full w-full bg-gradient-to-r from-transparent to-white/20 animate-pulse"></div>
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

const barColor = computed(() => {
  const colors = {
    cyan: 'bg-gradient-to-r from-cyan-600 to-cyan-400',
    purple: 'bg-gradient-to-r from-purple-600 to-purple-400',
    green: 'bg-gradient-to-r from-green-600 to-green-400',
    yellow: 'bg-gradient-to-r from-yellow-600 to-yellow-400',
    red: 'bg-gradient-to-r from-red-600 to-red-400'
  };
  return colors[props.color] || colors.cyan;
});
</script>
