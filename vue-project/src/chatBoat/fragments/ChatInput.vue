<template>
    <div class="wel-chatbot-input">
        <input 
            v-model="inputValue" 
            @keyup.enter="handleSend"
            type="text" 
            placeholder="Type your message..."
            class="wel-input"
        />
        <button 
            @click="handleSend" 
            :disabled="!inputValue.trim()"
            class="wel-send-button"
            aria-label="Send Message"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
    modelValue: string
}>()

const emit = defineEmits<{
    'update:modelValue': [value: string]
    send: []
}>()

const inputValue = computed({
    get: () => props.modelValue,
    set: (value: string) => emit('update:modelValue', value)
})

function handleSend() {
    if (inputValue.value.trim()) {
        emit('send')
    }
}
</script>

<style scoped>
.wel-chatbot-input {
    padding: 16px;
    background: white;
    border-top: 1px solid #e5e5e5;
    display: flex;
    gap: 8px;
}

.wel-input {
    flex: 1;
    padding: 10px 14px;
    border: 1px solid #e5e5e5;
    border-radius: 24px;
    outline: none;
    font-size: 14px;
}

.wel-input:focus {
    border-color: #667eea;
}

.wel-send-button {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s;
}

.wel-send-button:hover:not(:disabled) {
    transform: scale(1.05);
}

.wel-send-button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
