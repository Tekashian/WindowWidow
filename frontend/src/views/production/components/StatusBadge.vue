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
    classes: 'bg-gray-800/50 text-gray-300 border border-gray-700',
    dot: 'bg-gray-400',
    text: 'Oczekujące'
  },
  materials_check: {
    classes: 'bg-blue-900/30 text-blue-300 border border-blue-700',
    dot: 'bg-blue-400',
    text: 'Sprawdzanie materiałów'
  },
  materials_reserved: {
    classes: 'bg-cyan-900/30 text-cyan-300 border border-cyan-700',
    dot: 'bg-cyan-400',
    text: 'Materiały zarezerwowane'
  },
  in_progress: {
    classes: 'bg-purple-900/30 text-purple-300 border border-purple-700',
    dot: 'bg-purple-400',
    text: 'W produkcji'
  },
  quality_check: {
    classes: 'bg-yellow-900/30 text-yellow-300 border border-yellow-700',
    dot: 'bg-yellow-400',
    text: 'Kontrola jakości'
  },
  completed: {
    classes: 'bg-green-900/30 text-green-300 border border-green-700',
    dot: 'bg-green-400',
    text: 'Ukończone'
  },
  shipped_to_warehouse: {
    classes: 'bg-teal-900/30 text-teal-300 border border-teal-700',
    dot: 'bg-teal-400',
    text: 'Wysłano do magazynu'
  },
  delivered: {
    classes: 'bg-emerald-900/30 text-emerald-300 border border-emerald-700',
    dot: 'bg-emerald-400',
    text: 'Dostarczone'
  },
  on_hold: {
    classes: 'bg-orange-900/30 text-orange-300 border border-orange-700',
    dot: 'bg-orange-400',
    text: 'Wstrzymane'
  },
  cancelled: {
    classes: 'bg-red-900/30 text-red-300 border border-red-700',
    dot: 'bg-red-400',
    text: 'Anulowane'
  }
};

const statusClasses = computed(() => statusConfig[props.status]?.classes || statusConfig.pending.classes);
const dotClass = computed(() => statusConfig[props.status]?.dot || statusConfig.pending.dot);
const statusText = computed(() => statusConfig[props.status]?.text || props.status);
</script>
