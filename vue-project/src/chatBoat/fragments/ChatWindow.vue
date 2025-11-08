<template>
    <div class="wel-chatbot-window">
        <ChatHeader @close="$emit('close')" />
        <ChatMessages 
            :messages="messages" 
            :messagesContainer="messagesContainer"
            ref="chatMessagesRef"
        />
        <ChatInput 
            :modelValue="userInput"
            @update:modelValue="$emit('update:userInput', $event)"
            @send="$emit('send')"
        />
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import ChatHeader from './ChatHeader.vue'
import ChatMessages from './ChatMessages.vue'
import ChatInput from './ChatInput.vue'
import type { Message } from '../useChatBoat'

defineProps<{
    messages: Message[]
    userInput: string
    messagesContainer: HTMLElement | null
}>()

defineEmits<{
    close: []
    send: []
    'update:userInput': [value: string]
}>()

const chatMessagesRef = ref<InstanceType<typeof ChatMessages> | null>(null)

defineExpose({
    chatMessagesRef
})
</script>

<style scoped>
.wel-chatbot-window {
    width: 380px;
    height: 500px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    display: flex;
    flex-direction: column;
    overflow: hidden;
}

@media (max-width: 480px) {
    .wel-chatbot-window {
        width: calc(100vw - 40px);
        height: calc(100vh - 100px);
    }
}
</style>
