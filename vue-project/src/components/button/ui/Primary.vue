<template>
    <Button.Native
        :class="twMerge('px-3 py-2 !bg-sky-500 text-white rounded-sm', wrapperClass)"
        :loaderTheme="loaderTheme || 'light'"
    >
        <slot></slot>
    </Button.Native>
</template>

<script setup lang="ts">
    import { Button } from '@components'
    import { twMerge } from 'tailwind-merge'
    import { computed } from 'vue'

    interface Props {
        class?: string
        loaderTheme?: string
    }
    const props = defineProps<Props>()
    const wrapperClass = computed<string>(() => props.class)
</script>
