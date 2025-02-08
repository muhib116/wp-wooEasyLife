<template>
    <div class="grid">
        <span 
            title="Payment methods"
            class="font-medium whitespace-nowrap"
        >
            🚚 {{ order.payment_method_title || 'N/A' }}
        </span>
        <span
            class="truncate"
            :title="`Shipping methods: ${order?.shipping_methods?.join(', ') || 'N/A'}`"
        >
            📍 {{ order.shipping_methods.join(', ') || 'N/A' }}
        </span>
        <span
            title="Shipping cost"
            class="font-medium text-red-500"
        >
            💰 Cost: <span v-html="order.currency_symbol"></span>{{ order.shipping_cost || 'N/A' }}
        </span>
    </div>
</template>

<script setup lang="ts">
    defineProps({
        order: Object
    })
</script>