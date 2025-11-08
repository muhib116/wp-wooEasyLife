<template>
    <button 
        @click="$emit('toggle')" 
        class="fixed bottom-4 right-4 w-16 h-16 wel-gradient-primary text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center z-50"
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
