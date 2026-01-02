<template>
  <span
    :class="[
      'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium transition-all',
      statusClasses
    ]"
  >
    <span class="w-2 h-2 rounded-full mr-2 animate-pulse" :class="dotClass"></span>
    {{ statusText }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: {
    type: String,
    required: true
  }
});

const statusConfig = {
  pending: {
    classes: 'bg-yellow-900/30 text-yellow-300 border border-yellow-700',
    dot: 'bg-yellow-400',
    text: 'Oczekuje'
  },
  in_transit: {
    classes: 'bg-blue-900/30 text-blue-300 border border-blue-700',
    dot: 'bg-blue-400',
    text: 'W transporcie'
  },
  delivered: {
    classes: 'bg-green-900/30 text-green-300 border border-green-700',
    dot: 'bg-green-400',
    text: 'Dostarczone'
  },
  rejected: {
    classes: 'bg-red-900/30 text-red-300 border border-red-700',
    dot: 'bg-red-400',
    text: 'Odrzucone'
  },
  partial: {
    classes: 'bg-orange-900/30 text-orange-300 border border-orange-700',
    dot: 'bg-orange-400',
    text: 'Częściowe'
  }
};

const statusClasses = computed(() => statusConfig[props.status]?.classes || statusConfig.pending.classes);
const dotClass = computed(() => statusConfig[props.status]?.dot || statusConfig.pending.dot);
const statusText = computed(() => statusConfig[props.status]?.text || props.status);
</script>
