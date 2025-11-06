<template>
    <div class="flex flex-wrap gap-x-2">
        <span
            v-if="order?.ip_block_listed"
            class="!py-0 !text-[10px] flex items-center text-[#f93926]"
        >
            <Icon
                name="PhCellTower"
                size="12"
            />
            Ip blocked
        </span>
        <span
            v-if="order?.phone_block_listed"
            class="!py-0 !text-[10px] flex items-center text-[#e82661]"
        >
            <Icon
                name="PhSimCard"
                size="12"
            />
            Phone blocked
        </span>
        <span
            v-if="order?.email_block_listed"
            class="!py-0 !text-[10px] flex items-center text-[#444444]"
        >
            <Icon
                name="PhSimCard"
                size="12"
            />
            Email blocked
        </span>
        <span
            v-if="order?.device_block_listed"
            class="!py-0 !text-[10px] flex items-center text-[#e82661]"
        >
            <Icon
                name="PhDeviceMobileSlash"
                size="12"
            />
            Device blocked
        </span>
    </div>
</template>

<script setup lang="ts">
    import { Icon } from '@/components'
    
    type OrderBlacklistData = {
        ip_block_listed?: boolean
        phone_block_listed?: boolean
        email_block_listed?: boolean
        device_block_listed?: boolean
    }
    
    defineProps<{
        order: OrderBlacklistData
    }>()
</script>