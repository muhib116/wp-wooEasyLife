<template>
    <div class="fixed bottom-5 right-5 z-[9999] font-sans">
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

<!-- Styles are now in Tailwind classes directly in the template -->
