<template>
  <span
    :class="[
      'inline-flex items-center px-2 py-1 rounded text-xs font-bold',
      priorityClasses
    ]"
  >
    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
      <path v-if="priority === 'urgent'" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9 9a1 1 0 012 0v4a1 1 0 11-2 0V9zm1-5a1 1 0 100 2 1 1 0 000-2z"/>
      <path v-else-if="priority === 'high'" d="M10 2a1 1 0 011 1v6.586l2.707-2.707a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 9.586V3a1 1 0 011-1z"/>
      <path v-else d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z"/>
    </svg>
    {{ priorityText }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  priority: {
    type: String,
    required: true,
    validator: (value) => ['low', 'normal', 'high', 'urgent'].includes(value)
  }
});

const priorityConfig = {
  low: {
    classes: 'bg-gray-700 text-gray-300',
    text: 'Niski'
  },
  normal: {
    classes: 'bg-blue-900 text-blue-200',
    text: 'Normalny'
  },
  high: {
    classes: 'bg-orange-900 text-orange-200',
    text: 'Wysoki'
  },
  urgent: {
    classes: 'bg-red-900 text-red-200 animate-pulse',
    text: 'Pilne!'
  }
};

const priorityClasses = computed(() => priorityConfig[props.priority].classes);
const priorityText = computed(() => priorityConfig[props.priority].text);
</script>
