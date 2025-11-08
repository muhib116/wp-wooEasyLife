<template>
    <div class="p-4 bg-white border-t border-gray-200 flex gap-2">
        <input 
            v-model="inputValue" 
            @keyup.enter="handleSend"
            type="text" 
            placeholder="Type your message..."
            class="flex-1 py-2.5 px-3.5 border border-gray-200 rounded-3xl outline-none text-sm focus:border-indigo-500"
        />
        <button 
            @click="handleSend" 
            :disabled="!inputValue.trim()"
            class="w-10 h-10 rounded-full wel-gradient-primary text-white border-0 cursor-pointer flex items-center justify-center transition-transform duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
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

<!-- Styles are now in Tailwind classes directly in the template -->
