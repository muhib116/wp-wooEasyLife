<template>
    <div class="flex gap-1 items-center">
        <Select.Primary
            inputClass="py-1 px-3 pr-3 rounded-sm block w-full bg-transparent border border-secondary-five"
            defaultOption="Select Status"
            :options="wooCommerceStatuses"
            itemKey="slug"
            v-model="selectedStatus"
        />
        <Button.Primary 
            class="px-1 !py-1 font-medium bg-orange-500"
            @onClick="handleStatusChange"    
        >
            <span class="hidden sm:inline-block">
                Apply
            </span>
            <Icon
                class="inline-block sm:hidden shadow-sm"
                name="PhArrowsClockwise"
                size="25"
                weight="fill"
            />
        </Button.Primary>
    </div>
</template>

<script setup lang="ts">
    import { inject } from 'vue'
    import { Button, Select, Icon } from '@components'

    const {
        selectedStatus,
        wooCommerceStatuses,
        handleStatusChange
    } = inject('useOrders')
</script>