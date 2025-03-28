<template>
    <div 
        class="print:hidden flex gap-1"
    >
        <Input.Native
            placeholder="Search customer"
            class="text-base border px-2 py-1 rounded-sm"
            v-model="orderFilter.search"
        />
        <Button.Primary
            class="!py-1 px-2"
            @onClick="debouncedGetOrders"
        >
            <Icon
                name="PhMagnifyingGlass"
                weight="bold"
                size="20"
            />
        </Button.Primary>
        <slot name="afterButton"></slot>
    </div>
</template>

<script setup lang="ts">
    import { Button, Icon, Input } from '@components'
    import { inject } from 'vue'

    // Inject dependencies
    const {
        debouncedGetOrders,
        orderFilter
    } = inject('useOrders')
</script>