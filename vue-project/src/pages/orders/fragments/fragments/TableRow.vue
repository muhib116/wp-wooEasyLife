<template>
    <Table.Tr
        class="group"
        :class="`status-${order.status}`"
        :active="selectedOrders.has(order)"
    >
        <Table.Td
            @click="setSelectedOrder(order)"  
            class="cursor-pointer hover:bg-green-50"
        >
            <input
                type="checkbox"
                :value="order.id"
                :checked="[...selectedOrders].find(item => item.id == order.id)"
            />
        </Table.Td>
        <Table.Td >
            <div
                class="flex gap-2"
            >
                <span 
                    class="px-1 bg-gray-500 text-white capitalize rounded-sm text"
                    title="Order Id"
                >
                    #{{ order.id }}
                </span>
                <span 
                    class="px-1 bg-sky-500 text-white capitalize rounded-sm text"
                    title="Order source"
                >
                    {{ order.order_source }}
                    <!-- {{ order.created_via.replace('-', ' ') }} -->
                </span>
                <a 
                    class="text-orange-500 hover:scale-150 duration-200 opacity-0 group-hover:opacity-100"
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
            <div class="text-[12px] flex gap-1 items-center">
                📅 {{ order.date_created }}
            </div>
            <div class="text-[12px] flex gap-1 items-center">
                📞 {{ order.billing_address.phone }}
            </div>
            <div
                class="text-[12px] flex gap-1 items-center"
                :title="`${order.billing_address.address_1}, ${order.billing_address.address_2}`"
            >
                <p class="max-w-[240px]">
                    🏠 
                    {{ order.billing_address.address_1 }},
                    {{ order.billing_address.address_2 }}
                </p>
            </div>

            <div class="flex flex-wrap gap-x-2">
                <span
                    v-if="order?.ip_block_listed"
                    class="!py-0 !text-[10px] flex items-center text-[#f93926]"
                >
                    <Icon
                        name="PhCellTower"
                        size="12"
                    />
                    Ip blocked
                </span>
                <span
                    v-if="order?.phone_block_listed"
                    class="!py-0 !text-[10px] flex items-center text-[#e82661]"
                >
                    <Icon
                        name="PhSimCard"
                        size="12"
                    />
                    Phone blocked
                </span>
                <span
                    v-if="order?.email_block_listed"
                    class="!py-0 !text-[10px] flex items-center text-[#444444]"
                >
                    <Icon
                        name="PhSimCard"
                        size="12"
                    />
                    Email blocked
                </span>
            </div>
        </Table.Td>
        <Table.Td>
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
                    {{order?.customer_custom_data?.fraud_score || 0}}%
                </span>
            </div>
            <div v-else class="text-red-400 text-center">
                Behavior not detected!
            </div>
        </Table.Td>
        <Table.Td>
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
                    class="opacity-0 group-hover:opacity-100 text-white bg-orange-500 shadow mt-1 rounded-sm px-2"
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
        </Table.Td>
        <Table.Td
            @click="setSelectedOrder(order)"  
            class="whitespace-nowrap"
        >
            <span 
                title="Delivery success probability"
                class="font-semibold w-fit flex gap-3 mb-3 items-center bg-sky-500 px-3 py-1 rounded-sm"
                :style="{
                    background: +getDeliveryProbability >=0 ? `hsl(${ (+getDeliveryProbability / 100) * 120 }deg 75% 35%)` : `red`,
                    color: '#fff'
                }"
            >
                {{ +getDeliveryProbability >= 0 ? `DSP: ${getDeliveryProbability}%` : getDeliveryProbability }}
                <Icon
                    class="text-red-100 cursor-pointer"
                    title="This is just a prediction based on available data. \nWe do not guarantee the accuracy of the outcome, as various external factors may influence the actual results."
                    name="PhInfo"
                    size="20"
                />
            </span>

            <div 
                v-if="Object.keys(order?.courier_data)?.length"
                class="grid relative"
            >
                <div title="Delivery partner" class="mb-1">
                    <img
                        v-if="courierConfigs[order?.courier_data?.partner]?.logo"
                        :src="courierConfigs[order?.courier_data?.partner]?.logo"
                        class="w-[100px]"
                    />
                    <span v-else>
                        🚚 {{ order?.courier_data?.partner }}
                    </span>
                </div>
                <a
                    v-if='order?.courier_data?.parcel_tracking_link'
                    class="font-medium text-blue-500" 
                    title="Click to track your parcel"
                    :href="order?.courier_data?.parcel_tracking_link"
                    target="_black"
                >
                    📍 Track Parcel
                </a>
                <span title="Consignment Id">
                    🆔 {{ order?.courier_data?.consignment_id }}
                </span>
                
                <span 
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
            <div v-else>N/A</div>
        </Table.Td>
        <Table.Td
            @click="setSelectedOrder(order)"  
        >
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
        </Table.Td>
        <Table.Td
            @click="setSelectedOrder(order)"
        >
            <span class="truncate">
                💵 Price: <span v-html="order.product_price"></span>
            </span>
            <div class="whitespace-nowrap">
                💰 Discount: {{ order.discount_total }}
                <br/>
                🎟️ Coupons: {{ order?.applied_coupons?.join(', ') || 'N/A' }}
            </div>
        </Table.Td>
        <Table.Td
            @click="setSelectedOrder(order)"  
            class="whitespace-nowrap pointer-events-none"
        >
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
        </Table.Td>
        <Table.Td class="pointer-events-none">
            <div class="grid gap-2">
                <button
                    class="relative flex flex-col whitespace-nowrap justify-center items-center text-white bg-orange-500 w-full text-center py-1 px-2 rounded-sm pointer-events-auto hover:brightness-95"
                    @click="toggleNotesModel = true"
                >
                    Notes
                </button>
                <button
                    class="relative flex flex-col whitespace-nowrap justify-center items-center text-white bg-sky-500 w-full text-center py-1 px-2 rounded-sm pointer-events-auto hover:brightness-95"
                    @click="toggleModel = true"
                >
                    Address
                </button>
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
            </div>
        </Table.Td>
    </Table.Tr>

    <Modal 
        v-model="toggleModel"
        @close="toggleModel = false"
        class="max-w-[70%] w-full"
        title="Address manage"
    >
        <Address :order="order" />
    </Modal>

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
</template>

<script setup lang="ts">
    import { Table, Icon, Loader, Modal } from '@components'
    import { computed, inject, ref } from 'vue'
    import Address from './address/Index.vue'
    import { baseUrl } from '@/api'
    import FraudHistory from './FraudHistory.vue'
    import MultipleOrders from './MultipleOrders.vue'
    import Notes from './notes/Index.vue'

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
        setActiveOrder,
        setSelectedOrder,
        selectedOrders,
        courierStatusInfo
    } = inject('useOrders')
    const { courierConfigs } = inject('useCourierConfig')

    const toggleModel = ref(false)
    const toggleFraudHistoryModel = ref(false)
    const toggleMultiOrderModel = ref(false)
    const toggleNotesModel = ref(false)

    const getDeliveryProbability = computed(() => {
        let order = props.order
        // Get success rate and ensure it's a valid number
        let successRate = order?.customer_report?.success_rate;

        if (isNaN(parseFloat(successRate))) {
            successRate = '0'; // Default to 0% if it's an invalid value
        }

        // Remove '%' if present and parse it as a float
        const courierSuccessRate = parseFloat(successRate.replace('%', '')) || 0;

        // Ensure fraud score is a number
        const systemFraudScore = parseFloat(order?.customer_custom_data?.fraud_score) || 0;

        // Normalize success rate to a 0-1 scale
        let probability = courierSuccessRate / 100;

        // Adjust probability based on fraud score
        if (systemFraudScore > 80) {
            probability *= 0.5; // High fraud risk, reduce probability significantly
        } else if (systemFraudScore > 50) {
            probability *= 0.7; // Medium fraud risk, moderate reduction
        } else if (systemFraudScore > 20) {
            probability *= 0.9; // Low fraud risk, slight reduction
        }

        // Ensure probability stays within 0-100%
        probability = Math.max(0, Math.min(probability * 100, 100));

        return Math.round(probability) || 'Unpredicted'; // Return probability as a rounded percentage
    })
</script>