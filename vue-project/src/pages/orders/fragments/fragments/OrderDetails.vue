<template>
    <Table.Table>
        <Table.THead>
            <Table.Th>Image</Table.Th>
            <Table.Th>Product name</Table.Th>
            <Table.Th>Price</Table.Th>
            <Table.Th>Quantity</Table.Th>
            <Table.Th>Total Price</Table.Th>
        </Table.THead>

        <Table.TBody>
            <Table.Tr
                v-for="(item, index) in selectedOrderInfo"
                :key="index"
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
                <Table.Td>
                    {{ item.product_name }}
                </Table.Td>
                <Table.Td>
                    {{ item.product_price }}
                </Table.Td>
                <Table.Td class="capitalize">
                    {{ item.product_quantity }}
                </Table.Td>
                <Table.Td>
                    {{ item.product_total }}
                </Table.Td>
            </Table.Tr>
        </Table.TBody>
    </Table.Table>
</template>

<script setup lang="ts">
    import { Table } from '@components'
    defineProps<{
        selectedOrderInfo: object
    }>()
</script>