<template>
    <div
        v-if="active"
        class="flex gap-1 items-end z-40"
        :style="{
            width: `${size || 40}px`,
            height: `${size || 40}px`,
        }"
    >
        <Icon
            name="PhSpinner"
            :size="size"
            :weight="weight"
            class="animate-spin !w-full !h-full"
        />
    </div>
</template>

<script setup>
    import { Icon } from '@components'

    defineOptions({
        name: 'Loader',
    })
    const props = defineProps({
        active: {
            type: Boolean,
            default: () => true,
        },
        size: {
            type: String,
            default: 40,
        },
        weight: {
            type: String,
            default: 'bold',
        }
    })
</script>