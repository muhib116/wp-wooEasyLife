<template>
    <Card.Native class="min-h-[200px]">
        <MessageBox
            class="!fixed top-10 right-4 z-50"
            :title="alertMessage.message"
            :type="alertMessage.type"
        />
        <div class="mb-2 flex justify-between sticky -top-6 z-50 bg-white">
            <Loader
                class="absolute inset-x-1/2 top-[5px] -translate-x-1/2 z-[51]"
                :active="isLoading"
            />
            
            <h3 class="text-xl font-semibold text-gray-900">
                Configure your preference
            </h3>

            <Button.Primary
                @onClick="UpdateConfig"
            >
                Save Changes
            </Button.Primary>
        </div>
        <Table.Table>
            <Table.THead>
                <Table.Th>#sl</Table.Th>
                <Table.Th>Config Name</Table.Th>
                <Table.Th>Action</Table.Th>
            </Table.THead>
            <Table.TBody>
                <template
                    v-for="(item, key, index) in configData"
                    :key="key"
                >
                    <TableTrow
                        :objKey="key"
                        :index="index"
                    />
                </template>
            </Table.TBody>
        </Table.Table>
    </Card.Native>
</template>


<script setup>
    import { Table, MessageBox, Loader, Card, Button } from '@components'
    import { useIntegration } from './useIntegration'
    import { provide } from 'vue'
    import TableTrow from './fragment/TableRow.vue'

    const _useIntegration = useIntegration()
    const {
        isLoading,
        configData,
        alertMessage,
        UpdateConfig
    } = _useIntegration

    provide('useIntegration', _useIntegration)
</script>