<template>
    <div class="wel-chatbot-messages" ref="messagesContainerRef">
        <div 
            v-for="(message, index) in messages || []" 
            :key="index"
            :class="['wel-message', message.type === 'user' ? 'wel-message-user' : 'wel-message-bot']"
        >
            <div class="wel-message-content">
                {{ message.text }}
            </div>
            <div class="wel-message-time">{{ message.time }}</div>
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

<style scoped>
.wel-chatbot-messages {
    flex: 1;
    overflow-y: auto;
    padding: 16px;
    background: #f7f7f7;
}

.wel-message {
    margin-bottom: 16px;
    animation: fadeIn 0.3s ease-in;
}

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

.wel-message-content {
    padding: 10px 14px;
    border-radius: 12px;
    max-width: 75%;
    word-wrap: break-word;
}

.wel-message-bot .wel-message-content {
    background: white;
    color: #333;
    border-bottom-left-radius: 4px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.wel-message-user {
    text-align: right;
}

.wel-message-user .wel-message-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}

.wel-message-time {
    font-size: 11px;
    color: #999;
    margin-top: 4px;
}
</style>
