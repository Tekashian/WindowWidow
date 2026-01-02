<template>
  <div class="pagination" v-if="paginationData.last_page > 1">
    <button 
      class="pagination-btn"
      :disabled="paginationData.current_page === 1"
      @click="$emit('page-change', 1)"
    >
      «
    </button>
    
    <button 
      class="pagination-btn"
      :disabled="paginationData.current_page === 1"
      @click="$emit('page-change', paginationData.current_page - 1)"
    >
      ‹
    </button>
    
    <template v-for="page in visiblePages" :key="page">
      <span v-if="page === '...'" class="pagination-ellipsis">...</span>
      <button
        v-else
        :class="['pagination-btn', { active: page === paginationData.current_page }]"
        @click="$emit('page-change', page)"
      >
        {{ page }}
      </button>
    </template>
    
    <button 
      class="pagination-btn"
      :disabled="paginationData.current_page === paginationData.last_page"
      @click="$emit('page-change', paginationData.current_page + 1)"
    >
      ›
    </button>
    
    <button 
      class="pagination-btn"
      :disabled="paginationData.current_page === paginationData.last_page"
      @click="$emit('page-change', paginationData.last_page)"
    >
      »
    </button>
    
    <div class="pagination-info">
      <span>
        {{ paginationData.from }}-{{ paginationData.to }} z {{ paginationData.total }}
      </span>
      
      <select 
        class="pagination-per-page"
        :value="paginationData.per_page"
        @change="$emit('per-page-change', parseInt($event.target.value))"
      >
        <option :value="10">10</option>
        <option :value="15">15</option>
        <option :value="25">25</option>
        <option :value="50">50</option>
        <option :value="100">100</option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  paginationData: {
    type: Object,
    required: true
  }
})

defineEmits(['page-change', 'per-page-change'])

const visiblePages = computed(() => {
  const current = props.paginationData.current_page
  const last = props.paginationData.last_page
  const delta = 2
  const pages = []

  for (let i = 1; i <= last; i++) {
    if (
      i === 1 ||
      i === last ||
      (i >= current - delta && i <= current + delta)
    ) {
      pages.push(i)
    } else if (pages[pages.length - 1] !== '...') {
      pages.push('...')
    }
  }

  return pages
})
</script>

<style scoped>
.pagination {
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 24px;
  flex-wrap: wrap;
}

.pagination-btn {
  min-width: 36px;
  height: 36px;
  padding: 0 8px;
  border: 1px solid #d1d5db;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
  background: #f3f4f6;
  border-color: #9ca3af;
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.pagination-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.pagination-ellipsis {
  padding: 0 8px;
  color: #6b7280;
}

.pagination-info {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-left: auto;
  font-size: 14px;
  color: #6b7280;
}

.pagination-per-page {
  padding: 6px 8px;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 14px;
  cursor: pointer;
}
</style>
