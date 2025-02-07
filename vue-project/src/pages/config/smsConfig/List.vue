<template>
    <div
        v-if="messages?.length"
        class="relative"
    >
        <MessageBox
            :title="alertMessage.message"
            :type="alertMessage.type"
        />
        <div class="mb-2">
            <h3 class="text-xl font-semibold text-gray-900">Message list</h3>
        </div>
        <Table.Table>
            <Table.THead class="whitespace-nowrap">
                <Table.Th>#SL</Table.Th>
                <Table.Th>Is active</Table.Th>
                <Table.Th>Status</Table.Th>
                <Table.Th>For</Table.Th>
                <Table.Th>Phone number</Table.Th>
                <Table.Th>Message</Table.Th>
                <Table.Th class="text-right">Action</Table.Th>
            </Table.THead>
            <Table.TBody>
                <TableRow
                    v-for="(item, index) in messages"
                    :key="index"
                    :index="index"
                    :item="item"
                />
            </Table.TBody>
        </Table.Table>
    </div>
    <div 
        v-else-if="!isLoading"
        class="text-center text-gray-300 flex flex-col items-center gap-4"
    >
        No SMS available
        <Button.Primary
            class="px-auto animate-bounce"
            @click="tabChange('create')"
        >
            Create SMS
        </Button.Primary>
    </div>
</template>

<script setup lang="ts">
    import { Table, MessageBox, Button } from '@components'
    import { inject, onMounted } from 'vue'
    import TableRow from './fragments/TableRow.vue'

    const {
        isLoading,
        messages,
        tabChange,
        loadSMS,
        alertMessage
    } = inject('useSmsConfig')


    onMounted(() => {
        loadSMS()
    })
</script>