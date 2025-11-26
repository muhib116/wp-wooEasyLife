<template>
    <div class="mb-2 flex flex-col md:flex-row items-center justify-between">
        <p class="mb-4 font-medium text-blue-500 bg-blue-100/70 py-1 px-4">
            If the cart remains inactive for 25 minutes, it will be considered an abandoned order.
        </p>

        <Button.Native
            v-if="hasSelectedItems()"
            @onClick="handleBulkDelete"
            class="px-1 text-red-500"
        >
            <Icon name="PhX" size="20" />
            Remove Selected Items
        </Button.Native>
    </div>

    <div class="flex justify-end mb-2">
        <Pagination />
    </div>

    <div class="hidden w-full md:block">
        <Table.Table>
            <Table.THead :class="{ 'bg-red-500': selectedOption.id == 'canceled' }">
                <Table.Th 
                    class="w-5"
                    v-if="selectedOption.id != 'canceled'"
                >
                    <label class="flex gap-2">
                        <input
                            type="checkbox"
                            v-model="selectAll"
                            @change="toggleSelectAll"
                            title="Click here to select all items"
                        />
                    </label>
                </Table.Th>
                <Table.Th class="truncate w-5">#sl</Table.Th>
                <Table.Th class="truncate">Customer Info</Table.Th>
                <Table.Th class="truncate">Shipping</Table.Th>
                <Table.Th class="truncate">Payment</Table.Th>
                <Table.Th class="truncate">Cart Info</Table.Th>
                <Table.Th class="truncate text-right">Action</Table.Th>
            </Table.THead>
            <Table.TBody>
                <template v-if="abandonOrders?.length">
                    <TableRow
                        v-for="(item, index) in abandonOrders || []"
                        :key="item.id"
                        :item="item"
                        :index="index"
                    />
                </template>
                <Table.Tr v-else-if="!isLoading">
                    <Table.Td colspan="6" class="text-center text-gray-400 text-lg">
                        No result found!
                    </Table.Td>
                </Table.Tr>
            </Table.TBody>
        </Table.Table>
    </div>

    <div class="block md:hidden">
        <Table.Table>
            <Table.THead>
                <Table.Th class="truncate">Customer Info</Table.Th>
            </Table.THead>
            <Table.TBody>
                <template v-if="abandonOrders?.length">
                    <TableRowForMobile
                        v-for="item in abandonOrders || []"
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
    </div>
</template>

<script setup lang="ts">
    import { Table, Button, Icon } from '@components'
    import { inject } from 'vue'
    import TableRow from './TableRow.vue'
    import TableRowForMobile from './TableRowForMobile.vue'
    import Pagination from './Pagination.vue'

    const {
        selectAll,
        isLoading,
        abandonOrders,
        hasSelectedItems,
        handleBulkDelete,
        toggleSelectAll,
        selectedOption
    } = inject('useMissingOrder')
</script>