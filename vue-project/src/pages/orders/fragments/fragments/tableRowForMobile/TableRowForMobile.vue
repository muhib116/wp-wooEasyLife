<template>
    <Table.Tr
        class="group active:bg-gray-100 select-none"
        :class="`status-${order.status}`"
        :active="selectedOrders.has(order)"
        @touchstart="startLongPress" 
        @touchend="cancelLongPress" 
        @touchcancel="cancelLongPress"
        @touchmove="onTouchMove"
        @contextmenu.prevent
    >
        <Table.Td
            @click="setSelectedOrder(order)"  
            class="cursor-pointer hover:bg-green-50 !pr-0"
        >
            <input
                type="checkbox"
                :value="order.id"
                :checked="[...selectedOrders].find(item => item.id == order.id)"
            />
        </Table.Td>
        <Table.Td class="space-y-2 w-fit">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-2">
                    <span 
                        class="px-1 bg-gray-500 text-white capitalize rounded-sm text"
                        title="Order Id"
                    >
                        #{{ order.id }}
                    </span>
                    <span 
                        v-if="order.order_source "
                        class="px-1 bg-sky-500 text-white capitalize rounded-sm text"
                        title="Order source"
                    >
                        {{ order.order_source }}
                    </span>
                </div>
                
                <Button.Native
                    class="opacity-30 flex items-center gap-2"
                    :class="{
                        '!opacity-100 text-green-500' : order.is_done == 1
                    }"
                    @onClick="btn => markAsDone(order, btn)"
                >
                    <Icon
                        name="PhChecks"
                        size="25"
                        weight="bold"
                    />
                    {{ order.is_done == 1 ? 'done' : '' }}
                </Button.Native>
            </div>
            <div
                class="flex flex-wrap items-start gap-2 relative"
            >
                <span
                    title="Customer fraud score"
                    class="text-red-500"
                >
                    🚨 CFS: 
                    {{order?.customer_custom_data?.fraud_score?.toFixed(0) || 0}}%
                </span>

                <span 
                    title="Delivery success probability"
                    class="font-semibold w-fit flex gap-1 text-[12px] items-center bg-sky-500 px-2 rounded-sm"
                    :style="{
                        background: +deliveryProbability >=0 ? `hsl(${ (+deliveryProbability / 100) * 120 }deg 75% 35%)` : `red`,
                        color: '#fff'
                    }"
                >
                    {{ +deliveryProbability >= 0 ? `DSP: ${deliveryProbability}%` : deliveryProbability }}
                    <Icon
                        class="text-red-100 cursor-pointer"
                        title="This is just a prediction based on available data. \nWe do not guarantee the accuracy of the outcome, as various external factors may influence the actual results."
                        name="PhInfo"
                        size="18"
                    />
                </span>
            </div>

            <div
                class="flex gap-1 font-medium w-fit"
            >
                {{ order.billing_address.first_name }}
                {{ order.billing_address.last_name }}
                <span
                    v-if="order.repeat_customer"
                    class="text-green-500 tex-sm"
                    title="Repeat customer"
                >
                    (Repeat)
                </span>
            </div>
            <div 
                class="w-fit"
                v-if="order?.courier_data?.consignment_id" 
                title="Consignment Id"
            >
                🆔 {{ order?.courier_data?.consignment_id }}
            </div>

            <div
                v-if="order.customer_report?.success_rate"
                title="Courier success rate"
                class="flex gap-1 w-fit"
                @click="toggleFraudHistoryModel = true"
            >
                ✅ Rate:
                <strong class="truncate block">
                    {{ order.customer_report?.success_rate || '0%' }}
                </strong>
            </div>

            <div 
                class="text-sm flex gap-1 items-center w-fit"
            >
                📅 {{ order.date_created }}
            </div>

            <div class="flex gap-2">
                <a 
                    class="text-sm flex gap-1 items-center w-fit text-orange-500 underline"
                    :href="`tel: ${order.billing_address.phone}`"
                >
                    📞 {{ order.billing_address.phone }}
                </a>
                <a 
                    :href="`https://wa.me/${order.billing_address.phone}`" 
                    class="items-center size-6 rounded-sm shadow grid place-content-center bg-green-500 text-white"
                >
                    <Icon
                        name="PhWhatsappLogo"
                        size="20"
                        weight="fill"
                    />
                </a>
            </div>

            <div
                class="text-sm flex gap-1 items-center w-fit"
                @click="toggleAddressModel = true"
            >
                <p>
                    🏠 
                    {{ order.billing_address.address_1 }},
                    {{ order.billing_address.address_2 }}
                </p>
            </div>

            <BlackListData
                :order="order"
            />

            <div class="flex gap-3 items-center">
                <a
                    v-if='order?.courier_data?.parcel_tracking_link'
                    class="font-medium text-blue-500" 
                    title="Click to track your parcel"
                    :href="order?.courier_data?.parcel_tracking_link"
                    target="_black"
                >
                    📍 Track Parcel
                </a>
                <span class="font-bold">
                    Total: {{ order?.product_info?.total_price || 0 }}tk
                </span>
            </div>
            
            <div class="flex gap-2 items-center">
                <button class="relative order-status capitalize px-3 py-0 rounded-[18px] text-[13px] pointer-events-auto" :class="`status-${order.status}`">
                    {{ order.status=='processing' ? 'New Order' : order?.status?.replace(/-/g, ' ') }}
    
                    <span 
                        v-if="(order?.total_order_per_customer_for_current_order_status || 0) > 1"
                        title="Multiple order place"
                        class="cursor-pointer absolute -top-2 -right-1 size-4 place-content-center bg-red-500 aspect-square border-none text-white rounded-full text-[10px] hover:scale-110 shadow duration-300"
                        @click="toggleMultiOrderModel = true"
                    >
                        {{ order.total_order_per_customer_for_current_order_status }}
                    </span>
                </button>
                <span 
                    v-if="order?.courier_data?.status"
                    class="font-medium text-sky-500 flex items-center gap-2" 
                    title="Courier status"
                >
                    📦 {{ order?.courier_data?.status || 'N/A' }}
                    <Icon
                        name="PhInfo"
                        :title="courierStatusInfo[order?.courier_data?.status]"
                        size="20"
                        class="cursor-pointer"
                    />
                </span>
            </div>

            <hr />
            <div class="grid gap-2 grid-cols-2 -ml-[36px]">
                <div class="col-span-2">
                    <CourierEntry
                        :order="order"
                    />
                </div>
                <button
                    class="relative flex flex-col whitespace-nowrap justify-center items-center text-white bg-blue-500 w-full text-center py-1 px-2 rounded-sm pointer-events-auto hover:brightness-95"
                    title="Order details"
                    @click="(e) => {
                        e.preventDefault();
                        setActiveOrder(order)
                    }"
                >
                    Order Details
                </button>
                <button
                    class="relative flex flex-col whitespace-nowrap justify-center items-center text-white bg-orange-500 w-full text-center py-1 px-2 rounded-sm pointer-events-auto hover:brightness-95"
                    @click="toggleNotesModel = true"
                >
                    Notes
                </button>
            </div>

        </Table.Td>
    </Table.Tr>

    <!-- address manage start -->
    <Modal 
        v-model="toggleAddressModel"
        @close="toggleAddressModel = false"
        class="max-w-[70%] w-full"
        title="Address manage"
    >
        <Address :order="order" />
    </Modal>
    <!-- address manage end -->

    <!-- fraud history start -->
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
    <!-- fraud history start -->

    <!-- multi order start -->
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
    <!-- multi order start -->
    
    <!-- customer notes start -->
    <Modal 
        v-model="toggleNotesModel"
        @close="toggleNotesModel = false"
        class="max-w-[50%] w-full"
        title="Order Notes"
        hideFooter
    >
        <Notes
            :order="order"
        />
    </Modal>
    <!-- customer notes end -->

    <Transition name="slide">
        <OrderDetailsForMobile
            v-if="showOrderDetailsPopup"
            @close="showOrderDetailsPopup = false"
            :order="order"
        />
    </Transition>
</template>

<script setup lang="ts">
    import { Table, Icon, Modal, Button } from '@components'
    import { inject, ref, computed } from 'vue'
    import Address from '../address/Index.vue'
    import { baseUrl } from '@/api'
    import FraudHistory from '../FraudHistory.vue'
    import MultipleOrders from '../MultipleOrders.vue'
    import Notes from '../notes/Index.vue'
    import BlackListData from './BlackListData.vue'
    import { useTableRowForMobile } from './useTableRowForMobile'
    import OrderDetailsForMobile from './OrderDetailsForMobile.vue'
import { CourierEntry } from '@/components';

    const props = defineProps<{
        order: {
            customer_custom_data?: {
                fraud_score?: string | number;
            };
            customer_report?: {
                total_order?: string | number;
                confirmed?: string | number;
                success_rate?: string;
            };
            courier_data?: {
                partner?: string;
                parcel_tracking_link?: string;
                consignment_id?: string;
                status?: string;
            };
            payment_method_title?: string;
            shipping_methods?: string[];
            currency_symbol?: string;
            shipping_cost?: string | number;
            product_price?: string | number;
            discount_total?: string | number;
            applied_coupons?: string[];
            status?: string;
            total_order_per_customer_for_current_order_status?: number | undefined;
        };
    }>();

    const {
        setSelectedOrder,
        selectedOrders,
        courierStatusInfo,
        getDeliveryProbability,
        setActiveOrder,
        markAsDone
    } = inject('useOrders')

    const toggleAddressModel = ref(false)
    const toggleFraudHistoryModel = ref(false)
    const toggleMultiOrderModel = ref(false)
    const toggleNotesModel = ref(false)

    const {
        showOrderDetailsPopup,
        startLongPress,
        cancelLongPress,
        onTouchMove
    } = useTableRowForMobile()

    const deliveryProbability = computed(() => {
        return getDeliveryProbability(props.order)
    })
</script>

<style>
.slide-enter-active, .slide-leave-active {
  transition: transform 0.3s ease-in-out;
}

.slide-enter-from {
  transform: translateY(100%);
}

.slide-enter-to {
  transform: translateY(0);
}

.slide-leave-from {
  transform: translateY(0);
}

.slide-leave-to {
  transform: translateY(100%);
}
</style>