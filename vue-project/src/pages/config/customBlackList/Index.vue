<template>
    <Card.Native class="relative min-h-[200px] px-0 md:px-6 shadow-none md:shadow-md">
        <Loader
            :active="isLoading"
            class="absolute inset-x-1/2 top-[15px] -translate-x-1/2 z-20"
        />
        <MessageBox
            :title="alertMessage.message"
            :type="alertMessage.type"
        />
        <div class="mb-2 flex items-center justify-between">
            <h3 class="font-semibold text-gray-900 text-xl">
                Blacklisted Customers
            </h3>

            <Button.Native
                v-if="hasSelectedItems()"
                @onClick="handleBulkDelete"
                class="px-1 text-red-500"
            >
                <Icon name="PhX" size="20" />
                Remove Selected Items
            </Button.Native>
        </div>


        <Table.Table
            class="whitespace-nowrap"
            v-if="blackListData?.length"
        >
            <Table.THead>
                <Table.Th class="w-8">
                    <label class="flex gap-2">
                        <input
                            type="checkbox"
                            v-model="selectAll"
                            @change="toggleSelectAll"
                            title="Click here to select all items"
                        />
                    </label>
                </Table.Th>
                <Table.Th>#sl</Table.Th>
                <Table.Th>Phone/Email/Ip/Device</Table.Th>
                <Table.Th>Type</Table.Th>
                <Table.Th>Blocked At</Table.Th>
                <Table.Th class="text-right">Action</Table.Th>
            </Table.THead>
            <Table.TBody>
                <TableTrow
                    v-for="(item, index) in blackListData"
                    :key="index"
                    :index="index"
                    :item="item"
                />
            </Table.TBody>
        </Table.Table>
        <MessageBox
            title="No entries found in the blacklist."
            type="info"
            v-else-if="!isLoading"
        />
    </Card.Native>
</template>


<script setup>
    import { Table, MessageBox, Loader, Card, Button, Icon } from '@components'
    import { useBlackList } from './useBlackList'
    import { provide, ref } from 'vue'
    import TableTrow from './fragment/TableRow.vue'

    const _useBlackList = useBlackList()
    const {
        isLoading,
        selectAll,
        blackListData,
        alertMessage,
        handleBulkDelete,
        toggleSelectAll,
        hasSelectedItems
    } = _useBlackList

    provide('useBlackList', _useBlackList)
</script>