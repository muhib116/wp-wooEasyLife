<template>
  <div class="variable-editor-container">
    <!-- Editor Toolbar -->
    <div class="bg-white border border-gray-300 rounded-t-lg p-2 flex items-center space-x-2">
      <button 
        @click="toggleBold" 
        :class="{ 'bg-gray-200': editor?.isActive('bold') }"
        class="p-1.5 rounded hover:bg-gray-100 transition-colors"
        title="Bold"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M6 4h8a4 4 0 0 1 4 4a4 4 0 0 1-4 4H6z"></path>
          <path d="M6 12h9a4 4 0 0 1 4 4a4 4 0 0 1-4 4H6z"></path>
        </svg>
      </button>
      
      <button 
        @click="toggleItalic" 
        :class="{ 'bg-gray-200': editor?.isActive('italic') }"
        class="p-1.5 rounded hover:bg-gray-100 transition-colors"
        title="Italic"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="19" y1="4" x2="10" y2="4"></line>
          <line x1="14" y1="20" x2="5" y2="20"></line>
          <line x1="15" y1="4" x2="9" y2="20"></line>
        </svg>
      </button>
      
      <div class="h-6 w-px bg-gray-300 mx-1"></div>
      
      <button 
        @click="toggleVariableMenu" 
        class="p-1.5 rounded bg-primary-50 text-primary-700 hover:bg-primary-100 transition-colors flex items-center"
        title="Insert Variable"
        ref="variableMenuButton"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 5v14M5 12h14"></path>
        </svg>
        <span class="ml-1 text-sm font-medium">Variable</span>
      </button>
    </div>
    
    <!-- Editor Content -->
    <div class="border border-gray-300 border-t-0 rounded-b-lg overflow-hidden">
      <editor-content :editor="editor" class="prose max-w-none p-3 min-h-[200px] focus:outline-none" />
    </div>
    
    <!-- Variable Selection Menu -->
    <div 
      v-if="showVariableMenu" 
      ref="variableMenu"
      class="absolute bg-white border border-gray-200 rounded-lg shadow-lg w-64 max-h-80 overflow-y-auto z-50"
      :style="menuPosition"
    >
      <div class="p-2 border-b border-gray-200">
        <input 
          type="text" 
          placeholder="Search variables..." 
          v-model="searchQuery"
          class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        />
      </div>
      
      <div class="divide-y divide-gray-200">
        <div v-if="filteredVariables.length === 0" class="py-3 px-4 text-sm text-gray-500">
          No variables found
        </div>
        
        <div v-for="variable in filteredVariables" :key="variable.id" class="py-2 px-3 hover:bg-gray-50">
          <button 
            @click="insertVariable(variable)"
            class="flex items-center w-full text-left"
          >
            <span :class="`variable-icon ${variable.type}`">
              <!-- Text icon -->
              <svg v-if="variable.type === 'text'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary-600">
                <path d="M17 6.1H3M21 12.1H3M21 18.1H3"></path>
              </svg>
              
              <!-- Number icon -->
              <svg v-else-if="variable.type === 'number'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-secondary-600">
                <path d="M9 8h6M9 12h6M9 16h6M5 8h.01M5 12h.01M5 16h.01M19 8h.01M19 12h.01M19 16h.01"></path>
              </svg>
              
              <!-- Date icon -->
              <svg v-else-if="variable.type === 'date'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-accent-600">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              
              <!-- Email icon -->
              <svg v-else-if="variable.type === 'email'" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-success-600">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
              </svg>
            </span>
            
            <div class="ml-2">
              <div class="text-sm font-medium">{{ variable.label }}</div>
              <div class="text-xs text-gray-500">{{ variable.description }}</div>
            </div>
          </button>
        </div>
        
        <div v-if="allowCustomVariables" class="py-2 px-3">
          <button 
            @click="showCustomVariableForm = true"
            class="flex items-center w-full text-left text-primary-600 hover:text-primary-700"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="16"></line>
              <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            <span class="ml-2 text-sm font-medium">Add Custom Variable</span>
          </button>
        </div>
      </div>
    </div>
    
    <!-- Custom Variable Form -->
    <div v-if="showCustomVariableForm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg shadow-xl p-6 w-96 max-w-full">
        <h3 class="text-lg font-semibold mb-4">Add Custom Variable</h3>
        
        <form @submit.prevent="createCustomVariable">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Variable Name</label>
            <input 
              type="text" 
              v-model="customVariable.name"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="VARIABLE_NAME"
            />
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Variable Type</label>
            <select 
              v-model="customVariable.type"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
            >
              <option value="text">Text</option>
              <option value="number">Number</option>
              <option value="date">Date</option>
              <option value="email">Email</option>
            </select>
          </div>
          
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
            <input 
              type="text" 
              v-model="customVariable.description"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
              placeholder="Describe the variable"
            />
          </div>
          
          <div class="flex justify-end space-x-3">
            <button 
              type="button"
              @click="showCustomVariableForm = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Cancel
            </button>
            <button 
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              Add Variable
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import { useEditor, EditorContent } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Placeholder from '@tiptap/extension-placeholder';

export default {
  name: 'VariableTemplateEditor',
  components: {
    EditorContent,
  },
  props: {
    modelValue: {
      type: String,
      default: '',
    },
    availableVariables: {
      type: Array,
      default: () => [],
    },
    placeholder: {
      type: String,
      default: 'Start typing...',
    },
    allowCustomVariables: {
      type: Boolean,
      default: false,
    },
  },
  emits: ['update:modelValue', 'variable-inserted', 'content-updated'],
  setup(props, { emit }) {
    const editor = useEditor({
      content: props.modelValue,
      extensions: [
        StarterKit,
        Placeholder.configure({
          placeholder: props.placeholder,
        }),
      ],
      onUpdate: ({ editor }) => {
        emit('update:modelValue', editor.getHTML());
        emit('content-updated', editor.getHTML());
      },
    });

    // Watch for external changes to modelValue
    watch(() => props.modelValue, (newValue) => {
      const isSame = editor.value?.getHTML() === newValue;
      if (editor.value && !isSame) {
        editor.value.commands.setContent(newValue);
      }
    });

    // Variable menu state
    const showVariableMenu = ref(false);
    const variableMenuButton = ref(null);
    const variableMenu = ref(null);
    const menuPosition = ref({});
    const searchQuery = ref('');

    // Custom variable form state
    const showCustomVariableForm = ref(false);
    const customVariable = ref({
      name: '',
      type: 'text',
      description: '',
    });

    // Filter variables based on search
    const filteredVariables = computed(() => {
      if (!searchQuery.value) return props.availableVariables;
      
      const query = searchQuery.value.toLowerCase();
      return props.availableVariables.filter(v => 
        v.id.toLowerCase().includes(query) || 
        v.label.toLowerCase().includes(query) ||
        v.description?.toLowerCase().includes(query)
      );
    });

    // Toggle variable menu
    const toggleVariableMenu = () => {
      showVariableMenu.value = !showVariableMenu.value;
      
      if (showVariableMenu.value) {
        nextTick(() => {
          positionMenu();
          document.addEventListener('click', closeMenuOnOutsideClick);
        });
      } else {
        document.removeEventListener('click', closeMenuOnOutsideClick);
      }
    };

    // Position the menu below the button
    const positionMenu = () => {
      if (!variableMenuButton.value || !variableMenu.value) return;
      
      const buttonRect = variableMenuButton.value.getBoundingClientRect();
      menuPosition.value = {
        top: `${buttonRect.bottom + window.scrollY + 5}px`,
        left: `${buttonRect.left + window.scrollX}px`,
      };
    };

    // Close the menu when clicking outside
    const closeMenuOnOutsideClick = (event) => {
      if (
        variableMenu.value && 
        !variableMenu.value.contains(event.target) && 
        !variableMenuButton.value.contains(event.target)
      ) {
        showVariableMenu.value = false;
        document.removeEventListener('click', closeMenuOnOutsideClick);
      }
    };

    // Insert a variable into the editor
    const insertVariable = (variable) => {
      if (editor.value) {
        const variableText = `{{${variable.id}}}`;
        editor.value.commands.insertContent(variableText);
        showVariableMenu.value = false;
        emit('variable-inserted', variable);
      }
    };

    // Create a custom variable
    const createCustomVariable = () => {
      if (!customVariable.value.name) return;
      
      // Format the name to follow conventions (uppercase with underscores)
      const formattedName = customVariable.value.name
        .toUpperCase()
        .replace(/[^A-Z0-9_]/g, '_');
      
      const newVariable = {
        id: formattedName,
        label: formattedName,
        type: customVariable.value.type || 'text',
        description: customVariable.value.description || '',
      };
      
      // Add to available variables (would typically update store or emit event)
      emit('variable-created', newVariable);
      
      // Insert the variable
      insertVariable(newVariable);
      
      // Reset the form
      customVariable.value = { name: '', type: 'text', description: '' };
      showCustomVariableForm.value = false;
    };

    // Editor formatting shortcuts
    const toggleBold = () => {
      editor.value?.chain().focus().toggleBold().run();
    };
    
    const toggleItalic = () => {
      editor.value?.chain().focus().toggleItalic().run();
    };

    // Clean up event listeners
    onBeforeUnmount(() => {
      document.removeEventListener('click', closeMenuOnOutsideClick);
    });

    return {
      editor,
      showVariableMenu,
      variableMenuButton,
      variableMenu,
      menuPosition,
      searchQuery,
      filteredVariables,
      showCustomVariableForm,
      customVariable,
      toggleVariableMenu,
      insertVariable,
      createCustomVariable,
      toggleBold,
      toggleItalic,
    };
  },
};
</script>

<style scoped>
.variable-editor-container {
  position: relative;
}

:deep(.ProseMirror) {
  min-height: 120px;
  outline: none;
}

:deep(.ProseMirror p) {
  margin-bottom: 0.75em;
}

:deep(.ProseMirror p:last-child) {
  margin-bottom: 0;
}
</style>