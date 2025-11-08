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

<!-- Styles are in ../styles.css -->
