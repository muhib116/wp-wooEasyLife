<template>
    <tbody>
        <tr
            v-for="(product, index) in productInfo.product_info || []"
            :key="index"
            class="border-b border-gray-200"
        >
            <td class="max-w-0 py-5 pl-4 pr-3 text-sm sm:pl-0">
                <div class="font-medium text-gray-900">
                    {{ product.product_name }}
                </div>
            </td>
            <td class="hidden px-3 py-5 text-right text-sm text-gray-500 sm:table-cell">
                {{ product.product_quantity }}
            </td>
            <td class="hidden px-3 py-5 text-right text-sm text-gray-500 sm:table-cell">
                <span v-html="currency_symbol"></span>{{ product.product_price }}
            </td>
            <td class="py-5 pl-3 pr-4 text-right text-sm text-gray-500 sm:pr-0">
                <span v-html="currency_symbol"></span>{{ product.product_total }}
            </td>
        </tr>
    </tbody>
</template>

<script setup lang="ts">
    defineProps<{
        productInfo: {
            product_info: {
                product_name: string
                product_quantity: number
                product_price: number
                product_total: number
            }[]
        }[]
        currency_symbol: string
    }>()
</script>