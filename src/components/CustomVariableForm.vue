<template>
  <div class="bg-white rounded-lg shadow-xl p-6">
    <h3 class="text-lg font-semibold mb-4">Add Custom Variable</h3>
    
    <form @submit.prevent="submitForm">
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Variable Name</label>
        <input 
          type="text" 
          v-model="variableName"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          placeholder="VARIABLE_NAME"
        />
        <p v-if="nameError" class="mt-1 text-xs text-error-600">{{ nameError }}</p>
      </div>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Variable Type</label>
        <div class="grid grid-cols-2 gap-2">
          <button
            type="button"
            v-for="type in variableTypes"
            :key="type.value"
            @click="variableType = type.value"
            :class="[
              'flex items-center px-3 py-2 border rounded-md transition-colors',
              variableType === type.value 
                ? 'border-primary-500 bg-primary-50 text-primary-700' 
                : 'border-gray-300 hover:bg-gray-50'
            ]"
          >
            <component :is="type.icon" class="w-4 h-4 mr-2" />
            {{ type.label }}
          </button>
        </div>
      </div>
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
        <input 
          type="text" 
          v-model="variableDescription"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          placeholder="Describe the variable"
        />
      </div>
      
      <div class="flex justify-end space-x-3">
        <button 
          type="button"
          @click="$emit('cancel')"
          class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
        >
          Cancel
        </button>
        <button 
          type="submit"
          class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors"
        >
          Add Variable
        </button>
      </div>
    </form>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'CustomVariableForm',
  emits: ['submit', 'cancel'],
  setup(props, { emit }) {
    const variableName = ref('');
    const variableType = ref('text');
    const variableDescription = ref('');
    const nameError = ref('');
    
    const variableTypes = [
      {
        label: 'Text',
        value: 'text',
        icon: {
          render() {
            return (
              <svg viewBox="0 0 24 24\" fill="none\" stroke="currentColor\" stroke-width="2\" class="text-primary-600">
                <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
              </svg>
            );
          }
        }
      },
      {
        label: 'Number',
        value: 'number',
        icon: {
          render() {
            return (
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-secondary-600">
                <path d="M9 8h6M9 12h6M9 16h6M5 8h.01M5 12h.01M5 16h.01M19 8h.01M19 12h.01M19 16h.01"></path>
              </svg>
            );
          }
        }
      },
      {
        label: 'Date',
        value: 'date',
        icon: {
          render() {
            return (
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-accent-600">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
            );
          }
        }
      },
      {
        label: 'Email',
        value: 'email',
        icon: {
          render() {
            return (
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success-600">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
            );
          }
        }
      }
    ];
    
    const submitForm = () => {
      // Validate name format
      if (!variableName.value.trim()) {
        nameError.value = 'Variable name is required';
        return;
      }
      
      // Format the name to follow conventions (uppercase with underscores)
      const formattedName = variableName.value
        .trim()
        .toUpperCase()
        .replace(/[^A-Z0-9_]/g, '_');
      
      const newVariable = {
        id: formattedName,
        label: formattedName,
        type: variableType.value,
        description: variableDescription.value.trim() || '',
      };
      
      emit('submit', newVariable);
      
      // Reset form
      variableName.value = '';
      variableType.value = 'text';
      variableDescription.value = '';
      nameError.value = '';
    };
    
    return {
      variableName,
      variableType,
      variableDescription,
      nameError,
      variableTypes,
      submitForm,
    };
  }
};
</script>