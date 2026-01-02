<template>
  <div class="search-filter-bar">
    <div class="search-box">
      <input
        type="text"
        :value="modelValue"
        @input="handleSearch"
        :placeholder="placeholder"
        class="search-input"
      />
      <span class="search-icon">🔍</span>
    </div>
    
    <slot name="filters"></slot>
    
    <slot name="actions"></slot>
  </div>
</template>

<script setup>
import { ref } from 'vue'

defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Szukaj...'
  },
  debounce: {
    type: Number,
    default: 500
  }
})

const emit = defineEmits(['update:modelValue'])

let debounceTimeout = null

const handleSearch = (event) => {
  const value = event.target.value
  
  clearTimeout(debounceTimeout)
  debounceTimeout = setTimeout(() => {
    emit('update:modelValue', value)
  }, 500)
}
</script>

<style scoped>
.search-filter-bar {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 20px;
  flex-wrap: wrap;
}

.search-box {
  position: relative;
  flex: 1;
  min-width: 250px;
}

.search-input {
  width: 100%;
  padding: 10px 40px 10px 12px;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  transition: border-color 0.2s;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
}

.search-icon {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  opacity: 0.5;
}
</style>
