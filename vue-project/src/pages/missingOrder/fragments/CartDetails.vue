<template>

    <h3 class="font-bold m-0 mb-[10px]">Customer Details</h3>
        <div class="grid gap-x-4 gap-y-2 grid-cols-[auto_1fr] mb-4">
        <h4>
            <span style="font-weight: bold">
                Name: 
            </span>
            {{ order.customer_name }}</h4>
        <h4>
            <span style="font-weight: bold">
                Phone: 
            </span>
            {{ order.customer_phone || 'n/a' }}</h4>
        <h4>
            <span style="font-weight: bold">
                Email: 
            </span>
            {{ order.customer_email || 'n/a' }}</h4>
        <h4>
            <span style="font-weight: bold"> Address: </span> Mymensingh, guail kandi,
            Mymensingh, guail kandi
        </h4>
    </div>

    <Table.Table>
        <Table.THead>
            <Table.Th>Image</Table.Th>
            <Table.Th>Product name</Table.Th>
            <Table.Th>Price</Table.Th>
            <Table.Th>Quantity</Table.Th>
            <Table.Th class="text-right">Total Price</Table.Th>
        </Table.THead>

        <Table.TBody>
            <Table.Tr
                v-for="(item, index) in order.cart_contents || []"
                :key="index"
            >
                <Table.Td>
                    <img 
                        v-if="item.image"
                        :src="item.image" 
                        alt="product image"
                        style="
                            width: 60px;
                            height: 60px;
                            object-fit: cover;
                            object-position: center;
                        "
                    />
                    <Icon
                        v-else
                        name="PhImageBroken"
                        size="60"
                    />
                </Table.Td>
                <Table.Td>
                    <a
                        :href="item.product_url"
                        target="_blank"
                        class="text-blue-500 underline"
                    >
                        {{ item.name }}
                    </a>
                </Table.Td>
                <Table.Td>
                    {{ item.price }}
                </Table.Td>
                <Table.Td class="capitalize">
                    {{ item.quantity }}
                </Table.Td>
                <Table.Td class="text-right">
                    {{ item.price * item.quantity }}
                </Table.Td>
            </Table.Tr>
        </Table.TBody>

        <Table.THead>
            <Table.Td colspan="4" class="text-right font-bold">
                Total Value: 
            </Table.Td>
            <Table.Td class="text-right font-bold">{{ order.total_value  }}tk</Table.Td>
        </Table.THead>
    </Table.Table>
</template>

<script setup lang="ts">
    import { Table, Icon } from '@components'
    import { inject } from 'vue'

    defineProps<{
        order: object
    }>()


    const {
        markAsRecovered,
        markAsAbandoned
    } = inject('useMissingOrder')
</script>