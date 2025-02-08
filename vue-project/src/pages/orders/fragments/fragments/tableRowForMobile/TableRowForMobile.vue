<template>
    <Table.Tr
        class="group"
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
        <Table.Td class="space-y-1">
            <div
                class="flex flex-wrap items-start gap-2 relative"
            >
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
                <span
                    title="Customer fraud score"
                    class="text-red-500"
                >
                    🚨 CFS: 
                    {{order?.customer_custom_data?.fraud_score?.toFixed(0) || 0}}%
                </span>
                <a 
                    class="absolute top-0 right-0 text-orange-500"
                    :href="`${baseUrl}/wp-admin/post.php?post=${order.id}&action=edit`"
                    target="_blank"
                >
                    <Icon 
                        name="PhArrowSquareOut"
                        size="20"
                        weight="bold"
                    />
                </a>
            </div>
            <div
                class="flex gap-1 font-medium"
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
            <div v-if="order?.courier_data?.consignment_id" title="Consignment Id">
                🆔 {{ order?.courier_data?.consignment_id }}
            </div>

            <div
                v-if="order.customer_report?.success_rate"
                title="Courier success rate"
                class="flex gap-1"
                @click="toggleFraudHistoryModel = true"
            >
                ✅ Rate:
                <strong class="truncate block">
                    {{ order.customer_report?.success_rate || '0%' }}
                </strong>
            </div>

            <div class="text-sm flex gap-1 items-center">
                📅 {{ order.date_created }}
            </div>
            <div class="text-sm flex gap-1 items-center">
                📞 {{ order.billing_address.phone }}
            </div>
            <div
                class="text-sm flex gap-1 items-center"
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
            <div class="flex gap-2 items-center mt-2">
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
    import { Table, Icon, Modal } from '@components'
    import { inject, ref } from 'vue'
    import Address from '../address/Index.vue'
    import { baseUrl } from '@/api'
    import FraudHistory from '../FraudHistory.vue'
    import MultipleOrders from '../MultipleOrders.vue'
    import Notes from '../notes/Index.vue'
    import BlackListData from './BlackListData.vue'
    import { useTableRowForMobile } from './useTableRowForMobile'
    import OrderDetailsForMobile from './OrderDetailsForMobile.vue'

    defineProps<{
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
        courierStatusInfo
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