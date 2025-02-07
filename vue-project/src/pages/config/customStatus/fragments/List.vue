<template>
    <div v-if="statusList && Object.keys(statusList).length" class="relative">
        <MessageBox
            :title="alertMessage?.message"
            :type="alertMessage?.type"
        />
        <Heading
            title="Status list"
            class="mb-2"
        />
        <Table.Table>
            <Table.THead class="truncate bg-gray-700 text-white">
                <Table.Th>Title</Table.Th>
                <Table.Th>Slug</Table.Th>
                <Table.Th>Description</Table.Th>
                <Table.Th class="text-center">Color</Table.Th>
                <Table.Th class="text-center">Actions</Table.Th>
            </Table.THead>
            <Table.TBody>
                <TableRow
                    v-for="(item, id) in statusList"
                    :key="id"
                    :item="item"
                    :id="id"
                />
            </Table.TBody>
        </Table.Table>
    </div>
    <div 
        v-else-if="!isLoading"
        class="text-center text-gray-300 flex flex-col items-center gap-4"
    >
        No custom status available
        <Button.Primary
            class="px-auto animate-bounce"
            @click="tabChange('create')"
        >
            Create custom order status
        </Button.Primary>
    </div>
</template>

<script setup lang="ts">
    import { inject } from 'vue'
    import { Table, Heading, Button, MessageBox } from '@components'
    import TableRow from './TableRow.vue'

    const {
        statusList,
        tabChange,
        isLoading,
        alertMessage
    } = inject('useCustomStatus')
</script>