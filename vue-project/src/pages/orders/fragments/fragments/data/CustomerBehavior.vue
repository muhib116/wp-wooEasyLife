<template>
    <div v-if="order?.customer_custom_data" class="grid">
        <span
            title="Lifetime total orders"
        >
            📦 LTO: 
            {{order?.customer_custom_data?.total_orders}}
        </span>
        <span
            title="Lifetime total complete orders"
        >
            ✅ LTCO: 
            {{order?.customer_custom_data?.total_complete_orders}}
        </span>
        <span
            title="Total spent amount"
            class="text-green-500"
        >
            💰 TSA: 
            <span
                v-html="order?.currency_symbol || 'tk'"
            ></span>{{order?.customer_custom_data?.total_spent}}
        </span>
        <span
            title="Order frequency per day"
            class="text-orange-400"
        >
            ⏳ OFPD: 
            {{order?.customer_custom_data?.order_frequency}}
        </span>
        <span
            class="capitalize"
            title="Customer Type"
        >
                ⚠️ CT: 
            {{order?.customer_custom_data?.customer_type}}
        </span>
        <span
            title="Customer fraud score"
            class="text-red-500"
        >
            🚨 CFS: 
            {{order?.customer_custom_data?.fraud_score?.toFixed(0) || 0}}%
        </span>
    </div>
    <div v-else class="text-red-400 text-center">
        Behavior not detected!
    </div>
</template>

<script setup lang="ts">
    defineProps({
        order: Object
    })
</script>