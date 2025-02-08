<template>
    <div
        v-if="order?.customer_custom_data" 
        class="grid"
        v-bind="$attrs"
    >
        <span
            title="Lifetime total orders"
        >
            <span v-if="textFullFormate">
                📦 Lifetime total orders: 
            </span>
            <span v-else>📦 LTO: </span>
            {{order?.customer_custom_data?.total_orders}}
        </span>
        <span
            title="Lifetime total complete orders"
        >
            <span v-if="textFullFormate">
                ✅ Lifetime total complete orders: 
            </span>
            <span v-else>✅ LTCO: </span>
            {{order?.customer_custom_data?.total_complete_orders}}
        </span>
        <span
            title="Total spent amount"
            class="text-green-500"
        >
            <span v-if="textFullFormate">
                💰 Total spent amount: 
            </span>
            <span v-else>💰 TSA: </span>
            <span
                v-html="order?.currency_symbol || 'tk'"
            ></span>{{order?.customer_custom_data?.total_spent}}
        </span>
        <span
            title="Order frequency per day"
            class="text-orange-400"
        >
            <span v-if="textFullFormate">
                ⏳ Order frequency per day: 
            </span>
            <span v-else>⏳ OFPD: </span>
            {{order?.customer_custom_data?.order_frequency}}
        </span>
        <span
            class="capitalize"
            title="Customer Type"
        >
            <span v-if="textFullFormate">
                ⚠️ Customer Type: 
            </span>
            <span v-else>⚠️ CT: </span>
            {{order?.customer_custom_data?.customer_type}}
        </span>
        <span
            title="Customer fraud score"
            class="text-red-500"
        >
            <span v-if="textFullFormate">
                🚨 Customer fraud score: 
            </span>
            <span v-else>🚨 CFS: </span>
            {{order?.customer_custom_data?.fraud_score?.toFixed(0) || 0}}%
        </span>
    </div>
    <div v-else class="text-red-400 text-center">
        Behavior not detected!
    </div>
</template>

<script setup lang="ts">
    defineProps({
        order: Object,
        textFullFormate: Boolean
    })

    defineOptions({
        inheritAttrs: false
    })
</script>