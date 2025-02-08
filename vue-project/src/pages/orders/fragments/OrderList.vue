<template>
    <div class="relative">
        <Loader
            class="absolute top-[30vh] left-1/2 -translate-x-1/2 z-30"
            :active="isLoading"
        />

        <OrderDetails v-if="activeOrder" />
        <div v-else>
            <Heading
                title="Recent Orders"
                class="mb-4 px-6"
            />
            <TableFilter />
            <TableHeaderAction />

            <Pagination class="">
                <template #beforeSearch>
                    <StatusChangeDropdown />
                </template>
            </Pagination>

            <div class="min-h-[300px] overflow-auto w-full">
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
    import StatusChangeDropdown from './fragments/StatusChangeDropdown.vue'

    const {
        activeOrder,
        orders,
        isLoading
    } = inject('useOrders')
</script>