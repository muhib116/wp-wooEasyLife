<template>
    <!-- {{ order }} -->
    <tfoot>
        <tr>
            <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-6 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0">
                Subtotal
            </th>
            <th scope="row" class="pl-6 pr-3 pt-6 text-left text-sm font-normal text-gray-500 sm:hidden">
                Subtotal
            </th>
            <td class="pl-3 pr-6 pt-6 text-right text-sm text-gray-500 sm:pr-0">
                <span v-html="order.product_price"></span>
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0">
                Discount
            </th>
            <th scope="row" class="pl-6 pr-3 pt-4 text-left text-sm font-normal text-gray-500 sm:hidden">
                Discount
            </th>
            <td class="pl-3 pr-6 pt-4 text-right text-sm text-gray-500 sm:pr-0">
                <span v-html="order.currency_symbol"></span>{{ order.discount_total }}
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 text-right text-sm font-normal text-gray-500 sm:table-cell sm:pl-0">
                Shipping Cost
            </th>
            <th scope="row" class="pl-6 pr-3 pt-4 text-left text-sm font-normal text-gray-500 sm:hidden">
                Shipping Cost
            </th>
            <td class="pl-3 pr-6 pt-4 text-right text-sm text-gray-500 sm:pr-0">
                <span v-html="order.currency_symbol"></span>{{ order.shipping_cost }}
            </td>
        </tr>
        <tr>
            <th scope="row" colspan="3" class="hidden pl-4 pr-3 pt-4 text-right text-sm font-semibold text-gray-900 sm:table-cell sm:pl-0">
                Total
            </th>
            <th scope="row" class="pl-6 pr-3 pt-4 text-left text-sm font-semibold text-gray-900 sm:hidden">
                Total
            </th>
            <td class="pl-3 pr-4 pt-4 text-right text-sm font-semibold text-gray-900 sm:pr-0">
                <span v-html="order.currency_symbol"></span>
                {{ (order.product_info.total_price - order.discount_total) + order.shipping_cost }}
            </td>
        </tr>
    </tfoot>
</template>

<script setup lang="ts">
    defineProps<{
        order: {
            product_price: string,
            currency_symbol: string,
            discount_total: number,
            shipping_cost: number,
            product_info: {
                total_price: number
            }
        }
    }>()
</script>