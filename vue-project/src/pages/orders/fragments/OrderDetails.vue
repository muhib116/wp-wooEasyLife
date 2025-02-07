<template>
    <div class="flex justify-between pr-6">
        <Heading
            title="Order Details"
            class="mb-4 px-6"
        />
        <Button.Primary
            @click="setActiveOrder('')"
        >
            <Icon
                name="PhCaretLeft"
            />
            Back
        </Button.Primary>
    </div>

    <div class="px-6">
        <div v-if="activeOrder" class="grid grid-cols-2 mb-4">
            <h4>
                <span style="font-weight: bold;">
                    Name:
                </span> 
                {{ activeOrder?.customer_name }}
            </h4>
            <h4>
                <span style="font-weight: bold;">
                    Phone:
                </span> 
                {{ activeOrder.billing_address?.phone }}
            </h4>
            <h4 v-if="activeOrder.billing_address?.email">
                <span style="font-weight: bold;">
                    Email:
                </span> 
                {{ activeOrder.billing_address?.email }}
            </h4>
            <h4>
                <span style="font-weight: bold;">
                    Billing Address:
                </span> 
                {{ activeOrder.billing_address?.address_1 }} 
                {{ activeOrder.billing_address?.address_2 }}
            </h4>
        </div>

        <Table.Table>
            <Table.THead>
                <Table.Th style="width: 30px;">Image</Table.Th>
                <Table.Th>Product Name</Table.Th>
                <Table.Th>Price</Table.Th>
                <Table.Th>Quantity</Table.Th>
                <Table.Th>Total Price</Table.Th>
            </Table.THead>
            <Table.TBody>
                <Table.Tr 
                    v-for="item in activeOrder.product_info?.product_info || []"
                    :key="item.id"
                >
                    <Table.Td>
                        <img 
                            :src="item.product_image" 
                            alt="product image"
                            style="
                                width: 60px;
                                height: 60px;
                                object-fit: cover;
                                object-position: center;
                            "
                        />
                    </Table.Td>
                    <Table.Td>{{ item.product_name }}</Table.Td>
                    <Table.Td>
                        <span v-html="activeOrder.currency_symbol"></span>{{ item.product_price }}
                    </Table.Td>
                    <Table.Td>{{ item.product_quantity }}</Table.Td>
                    <Table.Td>
                        <span v-html="activeOrder.currency_symbol"></span>{{ item.product_total }}
                    </Table.Td>
                </Table.Tr>
            </Table.TBody>
        </Table.Table>
    </div>
</template>

<script setup lang="ts">
    import { Table, Button, Icon, Heading } from '@components'
    import { inject } from 'vue'

    const {
        activeOrder,
        setActiveOrder
    } = inject('useOrders')
</script>