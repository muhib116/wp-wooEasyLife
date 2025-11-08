<template>
    <div class="flex-1 overflow-y-auto p-4 bg-gray-100" ref="messagesContainerRef">
        <div 
            v-for="(message, index) in messages || []" 
            :key="index"
            :class="['mb-4', message.type === 'user' ? 'text-right' : '']"
            style="animation: fadeIn 0.3s ease-in; word-wrap: break-word;"
        >
            <div 
                :class="[
                    'py-2.5 px-3.5 rounded-xl max-w-[75%]',
                    message.type === 'user' 
                        ? 'text-white ml-auto rounded-br-sm' 
                        : 'bg-white text-gray-800 rounded-bl-sm shadow-sm'
                ]"
                :style="message.type === 'user' ? 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : ''"
            >
                {{ message.text }}
            </div>
            <div class="text-xs text-gray-500 mt-1">{{ message.time }}</div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import type { Message } from '../useChatBoat'

const props = defineProps<{
    messages: Message[]
    messagesContainer: HTMLElement | null
}>()

const messagesContainerRef = ref<HTMLElement | null>(null)

// Expose the ref to parent
defineExpose({
    messagesContainerRef
})

// Watch for new messages and auto-scroll
watch(() => props.messages.length, () => {
    if (messagesContainerRef.value) {
        setTimeout(() => {
            if (messagesContainerRef.value) {
                messagesContainerRef.value.scrollTop = messagesContainerRef.value.scrollHeight
            }
        }, 100)
    }
})
</script>

<!-- Styles are now in Tailwind classes directly in the template -->
<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
