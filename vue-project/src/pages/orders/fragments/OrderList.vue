<template>
    <div class="relative">
        <Loader
            class="absolute top-[30vh] left-1/2 -translate-x-1/2 z-30"
            :active="isLoading && !orderListLoading"
        />
        
        <Modal 
            v-model="activeOrder"
            @close="setActiveOrder('')"
            title="Order Details"
        >
            <OrderDetails />
        </Modal>    

        <div class="relative">
            <TableHeaderForDesktop class="hidden md:block" />
            <TableHeaderForMobile class="block md:hidden" />

            <Loader
                class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2 z-30"
                :active="orderListLoading"
            />
            <div 
                class="min-h-[300px] overflow-auto w-full"
            >
                <template 
                    v-if="orders?.length"
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
                </template>
            </div>

            <MessageBox
                v-if="!orders?.length && !orderListLoading"
                title="No record found!"
                type="info"
                class="mx-4"
            />

            <div class="px-4 hidden md:flex justify-between gap-4 mt-4">
                <SearchBox />
                <Pagination />
            </div>
            
            <TableHeaderAction 
                class="hidden md:flex"
            />

            <Pagination  class="block md:hidden" />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Table, Loader, MessageBox, Modal } from '@components'
    import { inject } from 'vue'
    import TableHeaderAction from './TableHeaderAction.vue'
    import TableHeader from './fragments/TableHeader.vue'
    import TableRowForMobile from './fragments/tableRowForMobile/TableRowForMobile.vue'
    import TableRow from './fragments/TableRow.vue'
    import OrderDetails from './OrderDetails.vue'
    import Pagination from './fragments/Pagination.vue'
    import TableHeaderForDesktop from './tableHeader/Desktop.vue'
    import TableHeaderForMobile from './tableHeader/Mobile.vue'
    import SearchBox from '@/pages/orders/fragments/fragments/SearchBox.vue'

    const {
        activeOrder,
        orders,
        isLoading,
        selectAll,
        setActiveOrder,
        toggleSelectAll,
        orderListLoading,
    } = inject('useOrders')
</script>