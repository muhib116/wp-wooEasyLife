<template>
    <Card.Native class="relative min-h-[200px]">
        <Loader
            :active="isLoading"
            class="absolute inset-x-1/2 top-[15px] -translate-x-1/2 z-20"
        />
        <MessageBox
            :title="alertMessage.message"
            :type="alertMessage.type"
        />
        <div class="mb-2">
            <h3 class="text-xl font-semibold text-gray-900">
                Blacklisted Customers
            </h3>
        </div>
        <Table.Table v-if="blackListData?.length">
            <Table.THead>
                <Table.Th>#sl</Table.Th>
                <Table.Th>Phone/Email/Ip</Table.Th>
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
    import { Table, MessageBox, Loader, Card } from '@components'
    import { useBlackList } from './useBlackList'
    import { provide } from 'vue'
    import TableTrow from './fragment/TableRow.vue'

    const _useBlackList = useBlackList()
    const {
        isLoading,
        blackListData,
        alertMessage,
    } = _useBlackList

    provide('useBlackList', _useBlackList)
</script>