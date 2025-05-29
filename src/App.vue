<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <header class="bg-white shadow-sm py-4 px-6">
      <div class="max-w-7xl mx-auto">
        <h1 class="text-2xl font-bold text-gray-800">Variable Template System</h1>
      </div>
    </header>
    
    <main class="flex-grow p-6">
      <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-xl font-semibold mb-4">Template Editor Demo</h2>
          
          <VariableTemplateEditor
            v-model="templateContent"
            :available-variables="availableVariables"
            :allow-custom-variables="true"
            @variable-inserted="handleVariableInserted"
            @content-updated="handleContentUpdated"
            placeholder="Type here and use variables with the + button..."
          />
          
          <div class="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h3 class="text-lg font-medium mb-2">Preview</h3>
            <div v-html="parsedContent" class="prose"></div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'App',
  setup() {
    const templateContent = ref('The Agreement of Purchase and Sale indicates that the {{NAME}} shall assume the tenancy agreement on {{SIGNING_DATE}} shall assume the tenancy agreements.');
    
    const availableVariables = ref([
      { id: 'NAME', label: 'NAME', type: 'text', description: 'Client name' },
      { id: 'AMOUNT', label: 'AMOUNT', type: 'number', description: 'Transaction amount' },
      { id: 'SIGNING_DATE', label: 'SIGNING_DATE', type: 'date', description: 'Date of signing' },
      { id: 'PRICE', label: 'PRICE', type: 'number', description: 'Purchase price' },
      { id: 'EMAIL', label: 'EMAIL', type: 'email', description: 'Client email' },
      { id: 'START_DATE', label: 'START_DATE', type: 'date', description: 'Starting date' },
      { id: 'CLIENT_NAME', label: 'CLIENT_NAME', type: 'text', description: 'Full client name' },
    ]);
    
    const parsedContent = computed(() => {
      // This would normally be a more complex parser
      // For demo, just highlight the variables
      return templateContent.value.replace(
        /\{\{([A-Z_]+)\}\}/g, 
        (match, variable) => {
          const varData = availableVariables.value.find(v => v.id === variable);
          if (!varData) return match;
          
          const typeClass = varData.type || 'text';
          return `<span class="variable-tag ${typeClass}">${variable}</span>`;
        }
      );
    });
    
    const handleVariableInserted = (variable) => {
      console.log('Variable inserted:', variable);
    };
    
    const handleContentUpdated = (content) => {
      console.log('Content updated:', content);
    };
    
    return {
      templateContent,
      availableVariables,
      parsedContent,
      handleVariableInserted,
      handleContentUpdated
    };
  }
};
</script>