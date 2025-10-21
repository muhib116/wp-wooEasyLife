<template>
  <div class="variable-menu bg-white border border-gray-200 rounded-lg shadow-lg overflow-hidden">
    <div class="p-2 border-b border-gray-200">
      <input 
        type="text" 
        v-model="localSearchQuery"
        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        placeholder="Search variables..."
        @input="$emit('update:searchQuery', localSearchQuery)"
        ref="searchInput"
      />
    </div>
    
    <div class="variable-list max-h-60 overflow-y-auto">
      <div v-if="filteredVariables.length === 0" class="py-3 px-4 text-sm text-gray-500">
        No variables found
      </div>
      
      <div 
        v-for="(variable, index) in filteredVariables" 
        :key="variable.id"
        class="variable-item py-2 px-3 hover:bg-gray-50 cursor-pointer transition-colors"
        :class="{ 'border-t border-gray-100': index > 0 }"
        @click="$emit('select', variable)"
      >
        <div class="flex items-center">
          <span :class="`variable-icon ${variable.type}`">
            <!-- Text icon -->
            <svg v-if="variable.type === 'text'" class="w-4 h-4 text-primary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
            </svg>
            
            <!-- Number icon -->
            <svg v-else-if="variable.type === 'number'" class="w-4 h-4 text-secondary-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 8h6M9 12h6M9 16h6M5 8h.01M5 12h.01M5 16h.01M19 8h.01M19 12h.01M19 16h.01"></path>
            </svg>
            
            <!-- Date icon -->
            <svg v-else-if="variable.type === 'date'" class="w-4 h-4 text-accent-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
              <line x1="16" y1="2" x2="16" y2="6"></line>
              <line x1="8" y1="2" x2="8" y2="6"></line>
              <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
            
            <!-- Email icon -->
            <svg v-else-if="variable.type === 'email'" class="w-4 h-4 text-success-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </span>
          
          <div class="ml-2">
            <div class="text-sm font-medium">{{ variable.label }}</div>
            <div v-if="variable.description" class="text-xs text-gray-500">{{ variable.description }}</div>
          </div>
        </div>
      </div>
    </div>
    
    <div v-if="allowCustomVariables" class="border-t border-gray-200 p-2">
      <button 
        @click="$emit('create-custom')"
        class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-primary-600 hover:bg-primary-50 rounded-md transition-colors"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="16"></line>
          <line x1="8" y1="12" x2="16" y2="12"></line>
        </svg>
        Add Custom Variable
      </button>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';

export default {
  name: 'VariableMenu',
  props: {
    variables: {
      type: Array,
      required: true
    },
    searchQuery: {
      type: String,
      default: ''
    },
    allowCustomVariables: {
      type: Boolean,
      default: false
    }
  },
  emits: ['select', 'create-custom', 'update:searchQuery'],
  setup(props) {
    const localSearchQuery = ref(props.searchQuery);
    const searchInput = ref(null);
    
    const filteredVariables = computed(() => {
      if (!localSearchQuery.value) return props.variables;
      
      const query = localSearchQuery.value.toLowerCase();
      return props.variables.filter(v => 
        v.id.toLowerCase().includes(query) || 
        v.label.toLowerCase().includes(query) ||
        v.description?.toLowerCase().includes(query)
      );
    });
    
    // Focus search input when menu opens
    onMounted(() => {
      if (searchInput.value) {
        searchInput.value.focus();
      }
    });
    
    // Keep local search query in sync with prop
    watch(() => props.searchQuery, (newVal) => {
      localSearchQuery.value = newVal;
    });
    
    return {
      localSearchQuery,
      filteredVariables,
      searchInput
    };
  }
};
</script>

<style scoped>
.variable-menu {
  width: 16rem;
  animation: fadeIn 0.15s ease-out;
}

.variable-item {
  transition: background-color 0.15s ease;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>