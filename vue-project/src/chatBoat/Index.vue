<template>
    <div class="wel-chatbot-container">
        <!-- Chatbot Toggle Button -->
        <ChatToggleButton 
            v-if="!isOpen" 
            @toggle="toggleChat" 
        />

        <!-- Chatbot Window -->
        <ChatWindow 
            v-if="isOpen"
            v-model:userInput="userInput"
            :messages="messages"
            :messagesContainer="messagesContainer"
            @close="toggleChat"
            @send="sendMessage"
        />
    </div>
</template>

<script setup lang="ts">
import { useChatBoat } from './useChatBoat'
import ChatToggleButton from './fragments/ChatToggleButton.vue'
import ChatWindow from './fragments/ChatWindow.vue'

const {
    isOpen,
    userInput,
    messages,
    messagesContainer,
    toggleChat,
    sendMessage
} = useChatBoat()
</script>

<style>
/* Force Tailwind import if needed */
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
</style>

<style scoped>
.wel-chatbot-container {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
}
</style>
