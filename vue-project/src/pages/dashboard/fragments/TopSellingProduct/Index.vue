<template>
    <Card.Native class="relative">
        <div class="flex justify-between gap-4 mb-2">
            <Heading
                title="Top Selling Products"
            />

            <!-- loadTopSellingProduct -->
            <label class="font-light border px-2 py-1 rounded-sm">
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none w-14"
                    v-model="productLimit"
                    @change="loadTopSellingProduct(productLimit)"
                >
                    <option>Select limit</option>
                    <option
                        v-for="(option, index) in productLimitOptions"
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
            <MessageBox
                v-if="!topSellingProducts?.length && !isLoading"
                title="No records found for the Top Selling Product!"
                type="info"
            />
            <Table.Table v-else-if="!isLoading">
                <Table.THead>
                    <Table.Th>#SL</Table.Th>
                    <Table.Th>Product</Table.Th>
                    <Table.Th>Sold</Table.Th>
                    <Table.Th>Stock</Table.Th>
                    <Table.Th>Stock Status</Table.Th>
                </Table.THead>
                <Table.TBody>
                    <Table.Tr
                        v-for="(product, index) in topSellingProducts"
                        :key="product.product_id"
                    >
                        <Table.Td>
                            {{ index+1 }}
                        </Table.Td>
                        <Table.Td>
                            <a 
                                class="flex gap-2 items-center text-sky-500 underline"
                                :href="`${baseUrl}/wp-admin/post.php?post=${product.product_id}&action=edit`"
                                target="_blank"
                                title="Click to update product"
                            >
                                <img
                                    :src="product.image"
                                    class="size-10 object-cover block"
                                />
                                {{ product.product_name }}
                            </a>
                        </Table.Td>
                        <Table.Td>
                            {{ product.total_sold }}
                        </Table.Td>
                        <Table.Td>
                            <div
                                :class="product.low_stock_threshold >= product.stock_quantity ? 'text-red-500 font-bold animate-bounce' : ''"
                            >
                                {{ product.stock_quantity }}
                                <span
                                    v-if="product.low_stock_threshold >= product.stock_quantity"
                                >Restock needed!</span>
                            </div>
                        </Table.Td>
                        <Table.Td>
                            {{ product.stock_status }}
                        </Table.Td>
                    </Table.Tr>
                </Table.TBody>
            </Table.Table>
        </div>
    </Card.Native>
</template>

<script setup lang="ts">
    import { Card, Table, Loader, Heading, MessageBox } from '@components'
    import { useTopSellingProduct } from './useTopSellingProduct'
    import {
        baseUrl
    } from '@/api'

    const {
        isLoading,
        productLimit,
        topSellingProducts,
        productLimitOptions,
        loadTopSellingProduct
    } = useTopSellingProduct()
</script>