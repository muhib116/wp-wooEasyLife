<template>
    <span class="truncate">
        💵 Price: <span v-html="order.product_price"></span>
    </span>
    <div class="whitespace-nowrap">
        💰 Discount: {{ order.discount_total }}
        <br/>
        🎟️ Coupons: {{ order?.applied_coupons?.join(', ') || 'N/A' }}
    </div>
</template>

<script setup lang="ts">
    defineProps({
        order: Object
    })
</script>