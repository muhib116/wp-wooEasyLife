<template>
    <p class="mb-4 font-medium text-blue-500 bg-blue-100/70 py-1 px-4">
        If the cart remains inactive for 25 minutes, it will be considered an abandoned order.
    </p>
    <Table.Table>
        <Table.THead>
            <Table.Th class="truncate">Customer Info</Table.Th>
            <Table.Th class="truncate">Contact Info</Table.Th>
            <Table.Th class="truncate">Status Of Cart</Table.Th>
            <Table.Th class="truncate">Abandon/Recover Date</Table.Th>
            <Table.Th class="truncate text-center">Order Details</Table.Th>
            <Table.Th class="truncate text-right">Action</Table.Th>
        </Table.THead>
        <Table.TBody>
            <template v-if="filteredAbandonOrders?.length">
                <TableRow
                    v-for="item in filteredAbandonOrders || []"
                    :key="item.id"
                    :item="item"
                />
            </template>
            <Table.Tr v-else-if="!isLoading">
                <Table.Td colspan="6" class="text-center text-gray-400 text-lg">
                    No result found!
                </Table.Td>
            </Table.Tr>
        </Table.TBody>
    </Table.Table>
</template>

<script setup lang="ts">
    import { Table } from '@components'
    import { inject } from 'vue'
    import TableRow from './TableRow.vue'

    const {
        isLoading,
        filteredAbandonOrders
    } = inject('useMissingOrder')
</script>