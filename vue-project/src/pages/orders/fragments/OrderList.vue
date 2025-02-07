<template>
    <div class="relative">
        <Loader
            class="absolute top-[30vh] left-1/2 -translate-x-1/2 z-30"
            :active="isLoading"
        />
        <MessageBox
            v-if="alertMessage?.title"
            :title="alertMessage.title"
            :type="alertMessage.type"
            class="mx-4"
        />

        <OrderDetails v-if="activeOrder" />
        <div v-else>
            <Heading
                title="Recent Orders"
                class="mb-4 px-6"
            />
            <TableFilter />
            <TableHeaderAction />

            <Pagination>
                <template #beforeSearch>
                    <div class="flex gap-1">
                        <Select.Primary
                            inputClass="py-1 px-3 pr-3 rounded-sm block w-full bg-transparent border border-secondary-five"
                            defaultOption="Select Status"
                            :options="wooCommerceStatuses"
                            itemKey="slug"
                            v-model="selectedStatus"
                        />
                        <Button.Primary 
                            class="!py-1"
                            @onClick="handleStatusChange"    
                        >
                            Apply
                        </Button.Primary>
                    </div>
                </template>
            </Pagination>

            <div class="min-h-[300px]">
                <Table.Table v-if="orders?.length">
                    <TableHeader />
                    <Table.TBody>
                        <TableRow 
                            v-for="(order, index) in orders"
                            :key="index"
                            :order="order"
                        />
                    </Table.TBody>
                    <TableHeader />
                </Table.Table>
                <MessageBox
                    v-else-if="!isLoading"
                    title="No record found!"
                    type="info"
                    class="mx-4"
                />
            </div>


            <TableHeaderAction />
            <Pagination />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Table, Loader, Heading, MessageBox, Select, Button } from '@components'
    import { inject } from 'vue'
    import TableHeaderAction from './TableHeaderAction.vue'
    import TableHeader from './fragments/TableHeader.vue'
    import TableRow from './fragments/TableRow.vue'
    import TableFilter from './fragments/TableFilter.vue'
    import OrderDetails from './OrderDetails.vue'
    import Pagination from './fragments/Pagination.vue'

    const {
        activeOrder,
        orders,
        isLoading,
        alertMessage,
        selectedStatus,
        wooCommerceStatuses,
        handleStatusChange
    } = inject('useOrders')
</script>