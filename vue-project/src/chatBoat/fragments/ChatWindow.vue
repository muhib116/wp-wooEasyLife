<template>
    <div class="fixed bottom-20 right-4 w-96 h-96 bg-white rounded-lg shadow-2xl flex flex-col z-50 max-[480px]:!w-[calc(100vw-2.5rem)] max-[480px]:!h-[calc(100vh-6.25rem)]">
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

<!-- Styles are in ../styles.css -->
