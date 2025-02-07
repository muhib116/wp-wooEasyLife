<template>
    <Table.THead class="truncate bg-gray-700 text-white">
        <Table.Th>
            <label class="flex gap-2">
                <input
                    type="checkbox"
                    v-model="selectAll"
                    @change="toggleSelectAll"
                    title="Click here to select all orders"
                />
            </label>
        </Table.Th>
        <Table.Th>Customer Info</Table.Th>
        <Table.Th>Consumer Behavior</Table.Th>
        <Table.Th>Delivery History</Table.Th>
        <Table.Th>Delivery Partner</Table.Th>
        <Table.Th>Shipping</Table.Th>
        <Table.Th>Payment</Table.Th>
        <Table.Th>Status</Table.Th>
        <Table.Th class="text-center">Action</Table.Th>
    </Table.THead>
</template>

<script setup lang="ts">
    import { Table } from '@components'
    import { inject } from 'vue'


    const {
        selectAll,
        toggleSelectAll
    } = inject('useOrders')
</script>