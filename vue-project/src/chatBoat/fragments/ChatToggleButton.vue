<template>
    <button 
        @click="$emit('toggle')" 
        class="wel-chatbot-toggle"
        aria-label="Open Chat"
    >
        <Icon name="PhChat" size="20" />
    </button>
</template>

<script setup lang="ts">
import { Icon } from '@/components'
defineEmits<{
    toggle: []
}>()
</script>

<style scoped>
.wel-chatbot-toggle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.wel-chatbot-toggle:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.wel-chatbot-toggle svg {
    width: 28px;
    height: 28px;
}
</style>
