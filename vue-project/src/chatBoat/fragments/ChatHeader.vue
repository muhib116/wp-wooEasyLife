<template>
    <div class="wel-gradient-primary text-white p-4 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-full bg-white bg-opacity-20 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold m-0">{{ title }}</h3>
                <p class="text-xs opacity-90 m-0">{{ status }}</p>
            </div>
        </div>
        <button @click="$emit('close')" class="bg-white bg-opacity-20 border-0 text-white w-8 h-8 rounded-full cursor-pointer flex items-center justify-center transition-all duration-200 hover:bg-opacity-30" aria-label="Close Chat">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</template>

<script setup lang="ts">
import { Icon } from '@/components'
withDefaults(defineProps<{
    title?: string
    status?: string
}>(), {
    title: 'Chat Support',
    status: 'Online'
})

defineEmits<{
    close: []
}>()
</script>

<!-- Styles are now in Tailwind classes directly in the template -->
