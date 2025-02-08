<template>
    <div class="relative">
        <Loader
            class="absolute top-[30vh] left-1/2 -translate-x-1/2 z-30"
            :active="isLoading"
        />

        <OrderDetails v-if="activeOrder" />
        <div v-else>
            <TableHeaderForDesktop class="hidden md:block" />
            <TableHeaderForMobile class="block md:hidden" />

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
    import { Table, Loader, MessageBox } from '@components'
    import { inject } from 'vue'
    import TableHeaderAction from './TableHeaderAction.vue'
    import TableHeader from './fragments/TableHeader.vue'
    import TableRow from './fragments/TableRow.vue'
    import OrderDetails from './OrderDetails.vue'
    import Pagination from './fragments/Pagination.vue'
    import TableHeaderForDesktop from './tableHeader/Desktop.vue'
    import TableHeaderForMobile from './tableHeader/Mobile.vue'

    const {
        activeOrder,
        orders,
        isLoading
    } = inject('useOrders')
</script>