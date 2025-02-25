<template>
    <div class="grid text-right mb-2 w-fit">
        <span class="truncate">
            Price: <span v-html="order.product_price+order.currency_symbol"></span>
        </span>
        <span
            v-if="order?.applied_coupons.length"
        >
            Discount: -<span v-html="order.discount_total+order.currency_symbol"></span>
        </span>
        <span class="truncate">
            Shipping: <span v-html="order.shipping_cost+order.currency_symbol"></span>
        </span>
        <hr class="border-b-0 opacity-50 my-2" />
        <span class="truncate text-orange-500">
            Total: <span v-html="order.total+order.currency_symbol"></span>
        </span>
    </div>
    <div 
        v-if="order?.applied_coupons.length"
        class="whitespace-nowrap text-right w-fit"
    >
        🎟️ Coupons: {{ order?.applied_coupons?.join(', ') || 'N/A' }}
    </div>
</template>

<script setup lang="ts">
    defineProps({
        order: Object
    })
</script>