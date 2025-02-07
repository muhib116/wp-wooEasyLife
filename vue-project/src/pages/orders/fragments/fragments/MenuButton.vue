<template>
    <Button.Native 
        class="flex gap-3 hover:bg-[#fef8f5] px-4 py-4 w-full text-sm"
        @onClick="cb"
    >
        <span class="size-8 flex-shrink-0 flex-grow-0 text-orange-500 bg-white 2xl flex items-center justify-center rounded-full relative">
            <Icon
                :name="iconName"
                size="30"
                weight="light"
            />
            <span
                v-if="[...selectedOrders].length"
                class="absolute font-light right-0 bottom-0 size-4 rounded-full flex items-center justify-center text-white text-[10px] bg-orange-500"
            >
                {{ [...selectedOrders].length }}
            </span>
        </span>
        <div class="text-left font-normal">
            <h3 class="font-medium text-gray-900">
                {{ title }}
            </h3>
            <p class="text-gray-500">
                {{ subtitle }}
            </p>
        </div>
    </Button.Native>
</template>

<script setup lang="ts">
    import { Button, Icon } from '@components'
    import { inject } from 'vue'

    defineProps<{
        title: string,
        subtitle: string,
        iconName: string,
        cb: () => {}
    }>()

    const {
        selectedOrders
    } = inject('useOrders')
</script>

<style scoped>
    .shadow-custom {
        --tw-shadow: 0px 16px 32px -8px rgba(240, 100, 52, 0.24);
        --tw-shadow-colored: 0px 16px 32px -8px var(--tw-shadow-color);
        box-shadow: var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow);
    }
</style>