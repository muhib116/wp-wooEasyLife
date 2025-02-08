<template>
    <button class="relative order-status capitalize px-3 py-1 pointer-events-auto" :class="`status-${order.status}`">
        {{ order.status=='processing' ? 'New Order' : order?.status?.replace(/-/g, ' ') }}

        <span 
            v-if="(order?.total_order_per_customer_for_current_order_status || 0) > 1"
            title="Multiple order place"
            class="cursor-pointer absolute -top-2 right-0 w-5 bg-red-500 aspect-square border-none text-white rounded-full text-[10px] hover:scale-110 shadow duration-300"
            @click="toggleMultiOrderModel = true"
        >
            {{ order.total_order_per_customer_for_current_order_status }}
        </span>
    </button>

    <Modal 
        v-model="toggleMultiOrderModel"
        @close="toggleMultiOrderModel = false"
        class="max-w-[80%] w-full"
        title="Duplicate Order History"
    >
        <MultipleOrders
            :item="order"
        />
    </Modal>
</template>

<script setup lang="ts">
    import MultipleOrders from '@/pages/orders/fragments/fragments/MultipleOrders.vue'
    import { Modal } from '@components'
    import { ref } from 'vue'

    defineProps({
        order: Object
    })

    const toggleMultiOrderModel = ref(false)
</script>