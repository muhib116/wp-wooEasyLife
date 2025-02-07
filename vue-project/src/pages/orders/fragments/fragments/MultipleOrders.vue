<template>
    <div>
        <div v-if="orders.length">
            <div class="flex justify-between items-center">
                <h3 class="font-bold m-0 mb-[10px]">
                    Customer Details 
                    <span class="font-medium text-sky-500">
                        (total orders: {{ orders?.length || 0 }})
                    </span>
                </h3>
                <Button.Primary
                    v-if="selectedOrderInfo.length"
                    @click="selectedOrderInfo = []"
                >
                    <Icon
                        name="PhCaretLeft"
                    />
                    Back
                </Button.Primary>
            </div>
            <div class="grid gap-1 grid-cols-[auto_1fr] mb-4">
                <h4>
                    <span style="font-weight: bold;"> Name: </span> 
                    {{ orders[0].billing_address.first_name }} 
                    {{ orders[0].billing_address.last_name }}
                </h4>
                <h4>
                    <span style="font-weight: bold;"> Phone: </span> 
                    {{ orders[0].billing_address.phone }}
                </h4>
                <h4>
                    <span style="font-weight: bold;"> Email: </span> 
                    {{ orders[0].billing_address.email || 'n/a' }}
                </h4>
                <h4>
                    <span style="font-weight: bold;"> Address: </span> 
                    {{ orders[0].billing_address.address_1 }}
                    {{ orders[0].billing_address.address_2 }}
                </h4>
            </div>
    

            <OrderDetails
                v-if="selectedOrderInfo.length"
                :selectedOrderInfo="selectedOrderInfo"
            />

            <Table.Table v-else>
                <Table.THead>
                    <Table.Th>#SL</Table.Th>
                    <Table.Th>Order</Table.Th>
                    <Table.Th>Date</Table.Th>
                    <Table.Th>Payment Method</Table.Th>
                    <Table.Th>Status</Table.Th>
                    <Table.Th>Total</Table.Th>
                    <Table.Th>Action</Table.Th>
                </Table.THead>
    
                <Table.TBody>
                    <Table.Tr
                        v-for="(order, index) in orders"
                        :key="order.id"
                    >
                        <Table.Td>{{ index + 1 }}</Table.Td>
                        <Table.Td>
                            #{{ order.id }}
                            {{ order.billing_address.first_name }}
                            {{ order.billing_address.last_name }}
                        </Table.Td>
                        <Table.Td>
                            {{ order.date_created }}
                        </Table.Td>
                        <Table.Td>
                            {{ order.payment_method_title }}
                        </Table.Td>
                        <Table.Td class="capitalize">
                            {{ order.status == 'processing' ? 'New order' : order.status }}
                        </Table.Td>
                        <Table.Td>
                            {{ order.product_info.total_price }}
                        </Table.Td>
                        <Table.Td>
                            <Button.Primary
                                @onClick="selectedOrderInfo = order?.product_info?.product_info"
                            >
                                View
                            </Button.Primary>
                        </Table.Td>
                    </Table.Tr>
                </Table.TBody>
            </Table.Table>
        </div>
        <div
            v-else
            class="relative min-h-[150px]"
        >
            <Loader
                class="absolute inset-1/2"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, onMounted } from 'vue'
    import { getOrderList } from '@/api'
    import { Table, Button, Loader, Icon } from '@components'
    import OrderDetails from './OrderDetails.vue'
    import { normalizePhoneNumber } from '@/helper'

    const props = defineProps<{
        item: object
    }>()

    const orders = ref([])
    const selectedOrderInfo = ref([])
    
    onMounted(async () => {
        const payload = {
            status: props.item.status,
            billing_phone: normalizePhoneNumber(props.item.billing_address.phone)
        }
        const { data } = await getOrderList(payload)
        orders.value = data
    })
</script>