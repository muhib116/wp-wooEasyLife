<template>
    <div class="flex justify-between items-center">
        <span class="mb-1 mt-2 block text-gray-500">Search by customer information</span>
        <Button.Native
            class="-mt-4 hover:text-red-500"
            @onClick="$emit('close')"
        >
            <Icon
                name="PhX"
                size="20"
                weight="bold"
            />
        </Button.Native>
    </div>
    <SearchBox 
        class="grid grid-cols-[1fr_35px]"
    />
</template>

<script setup lang="ts">
    import SearchBox from '@/pages/orders/fragments/fragments/SearchBox.vue'
    import {
        Icon,
        Button
    } from '@components'
</script>