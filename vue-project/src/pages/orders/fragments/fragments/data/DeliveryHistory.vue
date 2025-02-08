<template>
    <div 
        v-if="order?.customer_report"
        class="group whitespace-nowrap"
    >
        <div 
            class="flex gap-2"
            title="Total order"
        >
            📦 Total: 
            <strong>{{ order.customer_report?.total_order || 0 }}</strong>
        </div>
        <div 
            class="flex gap-2 text-green-600"
            title="Confirmed order"
        >
            🎉 Confirmed: 
            <strong>{{ order.customer_report?.confirmed || 0 }}</strong>
        </div>
        <div 
            class="flex gap-2 text-red-600"
            title="Canceled order"
        >
            ❌ Canceled: 
            <strong>{{ (order.customer_report?.total_order - order.customer_report?.confirmed) || 0 }}</strong>
        </div>
        <div 
            class="flex gap-2 flex-wrap text-sky-600"
            title="Success Rate"
        >
            ✅ Rate:
            <span
                v-if="order.customer_report?.success_rate == 'No order history found!'"
                class="truncate block"
            >
                N/A
            </span>
            <strong v-else class="truncate block">
                {{ order.customer_report?.success_rate || '0%' }}
            </strong>
        </div>
        <button
            class="md:opacity-0 group-hover:opacity-100 text-white bg-orange-500 shadow mt-1 rounded-sm px-2"
            @click="toggleFraudHistoryModel=true"
        >
            View Details
        </button>
    </div>
    <div v-else class="relative">
        <Loader
            :active="'fraudDataLoading' in order && order.fraudDataLoading"
            class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
            size="26"
        />
        N/A
    </div>


    <Modal 
        v-model="toggleFraudHistoryModel"
        @close="toggleFraudHistoryModel = false"
        class="max-w-[50%] w-full"
        :title="`Fraud history`"
    >
        <FraudHistory
            :order="order"
        />
    </Modal>
</template>

<script setup lang="ts">
    import { Loader, Modal } from '@components'
    import FraudHistory from '@/pages/orders/fragments/fragments/FraudHistory.vue'
    import { ref } from 'vue'

    defineProps({
        order: Object
    })

    const toggleFraudHistoryModel = ref(false)
</script>