<template>
    <div>
        <ProductAddButton
            class="mb-2 !ml-auto"
        />
        <Table.Table class="whitespace-nowrap">
            <Table.THead>
                <Table.Th style="width: 30px;">Image</Table.Th>
                <Table.Th>Product Name</Table.Th>
                <Table.Th>Price</Table.Th>
                <Table.Th class="text-center">Quantity</Table.Th>
                <Table.Th class="text-right">Total Price</Table.Th>
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
                    <Table.Td>
                        <ProductQuantityHandle
                            :item="item"
                        />
                    </Table.Td>
                    <Table.Td class="text-right">
                        <span v-html="activeOrder.currency_symbol"></span>
                        {{ item.product_quantity * item.product_price }}
                    </Table.Td>
                </Table.Tr>
            </Table.TBody>
        </Table.Table>
    </div>
</template>

<script setup lang="ts">
    import { Table } from '@components'
    import { inject } from 'vue'
    import ProductQuantityHandle from './ProductQuantityHandle.vue';
    import ProductAddButton from './ProductAddButton.vue';

    const {
        activeOrder
    } = inject('useOrders')
</script>