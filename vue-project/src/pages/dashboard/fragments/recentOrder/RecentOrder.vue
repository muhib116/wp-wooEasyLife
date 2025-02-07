<template>
    <Card.Native class="relative">
        <div class="flex justify-between gap-4 mb-2">
            <Heading
                title="Recent Orders"
            />

            <!-- loadTopSellingProduct -->
            <label class="font-light border px-2 py-1 rounded-sm">
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none w-14"
                    v-model="orderLimit"
                    @change="loadRecentOrders(orderLimit)"
                >
                    <option>Select limit</option>
                    <option
                        v-for="(option, index) in orderLimitOptions"
                        :key="index"
                        :value="option.id"
                    >
                        {{ option.title }}
                    </option>
                </select>
            </label>
        </div>

        <Loader
            class="absolute left-1/2 -translate-x-1/2 top-[200px] z-40"
            :active="isLoading"
        />

        <div class="h-[320px] overflow-auto">
            <OrderDetails v-if="activeOrder" />
            <MessageBox
                v-if="!recentOrders?.length && !isLoading"
                title="No records found for the Recent Orders!"
                type="info"
            />
            <Table.Table v-else-if="!isLoading && !activeOrder">
                <Table.THead class="whitespace-nowrap">
                    <Table.Th>#SL</Table.Th>
                    <Table.Th>Order Info</Table.Th>
                    <Table.Th>Delivery History</Table.Th>
                    <Table.Th>Delivery Partner</Table.Th>
                    <Table.Th>Shipping</Table.Th>
                    <Table.Th>Payment</Table.Th>
                    <Table.Th>Status</Table.Th>
                    <Table.Th>Note</Table.Th>
                    <Table.Th>Address</Table.Th>
                    <Table.Th>Action</Table.Th>
                </Table.THead>
                <Table.TBody>
                    <TableRow 
                        v-for="(order, index) in recentOrders"
                        :key="order.id"
                        :order="order"
                        :index="index"
                    />
                </Table.TBody>
            </Table.Table>
        </div>
    </Card.Native>
</template>

<script setup lang="ts">
    import { Card, Table, Loader, Heading, MessageBox } from '@components'
    import { useRecentOrder } from './UseRecentOrder'
    import TableRow from './TableRow.vue'
    import { useOrders } from '@/pages/orders/useOrders.ts'
    import { provide } from 'vue'
    import OrderDetails from '@/pages/orders/fragments/OrderDetails.vue'

    const {
        isLoading,
        recentOrders,
        orderLimit,
        loadRecentOrders,
        orderLimitOptions
    } = useRecentOrder()
    const _useOrders = useOrders()
    const {
        activeOrder
    } = _useOrders

    provide('useOrders', _useOrders)
</script>