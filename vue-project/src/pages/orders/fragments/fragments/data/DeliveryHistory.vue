<template>
    <div>
        <div 
            v-if="order?.customer_report"
            class="group whitespace-nowrap"
            v-bind="$attrs"
        >
            <div 
                class="flex gap-2"
                title="Total order"
            >
                <span v-if="textFullFormate">
                    📦 Total order: 
                </span>
                <span v-else>📦 Total: </span>
                <strong>{{ order.customer_report?.total_order || 0 }}</strong>
            </div>
            <div 
                class="flex gap-2 text-green-600"
                title="Confirmed order"
            >
                <span v-if="textFullFormate">
                    🎉 Confirmed order: 
                </span>
                <span v-else>🎉 Confirmed: </span>
                <strong>{{ order.customer_report?.confirmed || 0 }}</strong>
            </div>
            <div 
                class="flex gap-2 text-red-600"
                title="Canceled order"
            >
                <span v-if="textFullFormate">
                    ❌ Canceled order: 
                </span>
                <span v-else>❌ Canceled: </span>
                <strong>{{ (order.customer_report?.total_order - order.customer_report?.confirmed) || 0 }}</strong>
            </div>
            <div 
                class="flex gap-2 flex-wrap text-sky-600"
                title="Success Rate"
            >
                <span v-if="textFullFormate">
                    ✅ Success Rate:
                </span>
                <span v-else>✅ Rate:</span>
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
                class="md:mt-0 md:opacity-0 group-hover:opacity-100 text-white bg-orange-500 shadow mt-1 rounded-sm px-2 py-1"
                @click="toggleFraudHistoryModel=true"
            >
                View Details
            </button>
        </div>
        <div v-else class="relative">
            <!-- <Loader
                :active="'fraudDataLoading' in order && order.fraudDataLoading"
                class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
                size="26"
            /> -->
            N/A
        </div>
    
        <Button.Native 
            v-if="configData.fraud_customer_checker"
            class="p-0.5 font-light text-green-500 whitespace-nowrap mt-2"
            title="Refresh Delivery Report"
            @onClick="btn => handleFraudCheck(btn, order)"
        >
            <Icon name="PhArrowClockwise" size="16" />
        </Button.Native>
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
    import { Loader, Modal, Button, Icon } from '@/components'
    import FraudHistory from '@/pages/orders/fragments/fragments/FraudHistory.vue'
    import { inject, ref } from 'vue'

    defineProps({
        order: Object,
        textFullFormate: Boolean
    })
    const {configData} = inject('configData') as any

    const {
        handleFraudCheck
    } = inject('useOrders') as any

    defineOptions({
        inheritAttrs: false
    })

    const toggleFraudHistoryModel = ref(false)
</script>