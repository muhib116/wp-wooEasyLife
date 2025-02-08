<template>
    <div>
        <div class="flex gap-1">
            <Select.Primary
                inputClass="py-1 px-3 pr-3 rounded-sm block w-full bg-transparent border border-secondary-five"
                defaultOption="Select Status"
                :options="wooCommerceStatuses"
                itemKey="slug"
                v-model="selectedStatus"
            />
            <Button.Primary 
                class="!py-1"
                @onClick="handleStatusChange"    
            >
                Apply
            </Button.Primary>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { inject } from 'vue'
    import { Button, Select } from '@components'

    const {
        selectedStatus,
        wooCommerceStatuses,
        handleStatusChange
    } = inject('useOrders')
</script>