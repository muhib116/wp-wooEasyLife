<template>

    <div class="flex gap-4 justify-center items-center h-[72px] print:hidden bg-white z-[39] sticky top-[32px]">
        <Button.Primary
            @click="showInvoices = false"
        >
            <Icon
                name="PhCaretLeft"
            />
            Back to List
        </Button.Primary>
        <Button.Primary
            @click="handlePrint"
        >
            <Icon
                name="PhTag"
            />
            Now Print Label
        </Button.Primary>
    </div>

    <Invoice.Label
        v-for="(order, index) in orders"
        :key="index"
        :order="order"
    />
</template>

<script setup lang="ts">
    import { inject } from 'vue'
    import { Button, Icon, Invoice } from '@/components'

    const {
        showLabels,
        orders,
        selectedOrders
    } = inject('useOrders')

    const handlePrint = () => {
        window.print()
    }
</script>