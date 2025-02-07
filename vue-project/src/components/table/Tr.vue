<template>
    <tr 
        class="border-b"
        :class="active ? 'bg-green-50 !border border-green-400 rounded' : 'bg-white hover:bg-gray-50'"
    >
        <slot></slot>
    </tr>
</template>

<script setup lang="ts">
    defineProps<{
        active?: boolean
    }>()
</script>