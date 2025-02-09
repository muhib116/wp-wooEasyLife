<template>
    <Card.Native class="min-h-[200px] px-0 md:px-6 shadow-none md:shadow-md">
        <MessageBox
            class="!fixed top-10 right-4 z-50"
            :title="alertMessage.message"
            :type="alertMessage.type"
        />
        <div class="mb-2 flex justify-between sticky -top-6 z-[20] bg-white">
            <Loader
                class="absolute inset-x-1/2 top-[5px] -translate-x-1/2 z-[51]"
                :active="isLoading"
            />
            
            <h3 class="lg:text-xl font-semibold text-gray-900">
                Configure your preference
            </h3>

            <Button.Primary
                class="!bg-green-500"
                @onClick="UpdateConfig"
            >
                <span class="hidden lg:inline-block">
                    Save
                </span>
                <Icon
                    class="lg:hidden inline-block"
                    name="PhChecks"
                    weight="bold"
                    size="22"
                />
            </Button.Primary>
        </div>
        <Table.Table class="whitespace-nowrap">
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
    import { Table, MessageBox, Loader, Card, Button, Icon } from '@components'
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