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

<!-- Styles are imported in main.ts -->
