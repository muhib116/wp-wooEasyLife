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

            <div 
                v-if="orders?.length" 
                class="min-h-[300px] overflow-auto w-full"
            >
                <div class="hidden md:block">
                    <Table.Table>
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
                </div>

                <div class="block md:hidden">
                    <Table.Table>
                        <Table.THead class="truncate bg-gray-700 text-white">
                            <Table.Th class="!pr-0">
                                <label>
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        @change="toggleSelectAll"
                                        title="Click here to select all orders"
                                    />
                                </label>
                            </Table.Th>
                            <Table.Th>Customer Info</Table.Th>
                            <!-- <Table.Th>Consumer Behavior</Table.Th>
                            <Table.Th>Delivery History</Table.Th>
                            <Table.Th>Delivery Partner</Table.Th>
                            <Table.Th>Shipping</Table.Th>
                            <Table.Th>Payment</Table.Th>
                            <Table.Th>Status</Table.Th>
                            <Table.Th class="text-center">Action</Table.Th> -->
                        </Table.THead>
                        <Table.TBody>
                            <TableRowForMobile
                                v-for="(order, index) in orders"
                                :key="index"
                                :order="order"
                            />
                        </Table.TBody>
                    </Table.Table>
                </div>
            </div>

            <MessageBox
                v-else-if="!isLoading"
                title="No record found!"
                type="info"
                class="mx-4"
            />

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
    import TableRowForMobile from './fragments/tableRowForMobile/TableRowForMobile.vue'
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