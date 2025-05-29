<template>
  <span 
    :class="['variable-tag', typeClass]" 
    @mouseenter="showTooltip = true"
    @mouseleave="showTooltip = false"
  >
    <span class="variable-icon">
      <!-- Text icon -->
      <svg v-if="type === 'text'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
      </svg>
      
      <!-- Number icon -->
      <svg v-else-if="type === 'number'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M9 8h6M9 12h6M9 16h6M5 8h.01M5 12h.01M5 16h.01M19 8h.01M19 12h.01M19 16h.01"></path>
      </svg>
      
      <!-- Date icon -->
      <svg v-else-if="type === 'date'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
        <line x1="16" y1="2" x2="16" y2="6"></line>
        <line x1="8" y1="2" x2="8" y2="6"></line>
        <line x1="3" y1="10" x2="21" y2="10"></line>
      </svg>
      
      <!-- Email icon -->
      <svg v-else-if="type === 'email'" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
        <polyline points="22,6 12,13 2,6"></polyline>
      </svg>
    </span>
    
    {{ name }}
    
    <!-- Tooltip -->
    <div 
      v-if="showTooltip && description" 
      class="absolute z-10 transform -translate-y-full mt-[-8px] left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-800 text-white text-xs rounded shadow-lg"
    >
      {{ description }}
      <div class="absolute w-2 h-2 bg-gray-800 transform rotate-45 left-1/2 -translate-x-1/2 bottom-[-4px]"></div>
    </div>
  </span>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'VariableTag',
  props: {
    name: {
      type: String,
      required: true,
    },
    type: {
      type: String,
      default: 'text',
      validator: (value) => ['text', 'number', 'date', 'email'].includes(value),
    },
    description: {
      type: String,
      default: '',
    },
  },
  setup(props) {
    const showTooltip = ref(false);
    
    const typeClass = computed(() => {
      return props.type || 'text';
    });
    
    return {
      showTooltip,
      typeClass,
    };
  },
};
</script>

<style scoped>
.variable-tag {
  position: relative;
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  margin: 0 0.25rem;
  font-weight: 500;
  font-size: 0.875rem;
  line-height: 1;
  transition: all 0.2s ease;
}

.variable-tag:hover {
  filter: brightness(0.95);
}
</style>