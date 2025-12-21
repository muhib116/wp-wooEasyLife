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
                :checked="[...selectedOrders].find(item => item?.id == order.id)"
            />
        </Table.Td>
        <Table.Td >
            <CustomerInfo
                :order="order"
            />
        </Table.Td>
        <Table.Td>
            <CustomerBehavior
                :order="order"
            />
        </Table.Td>
        <Table.Td>
            <DeliveryHistory
                :order="order"
            />
        </Table.Td>
        <Table.Td 
            class="whitespace-nowrap"
        >
            <div class="flex items-start gap-2">
                <DeliveryPartner
                    class="flex-1"
                    :order="order"
                />
            </div>
        </Table.Td>
        <Table.Td>
            <Shipping
                :order="order"
            />
        </Table.Td>
        <Table.Td>
            <Payment
                :order="order"
            />
        </Table.Td>
        <Table.Td
            class="whitespace-nowrap"
        >
            <Status
                :order="order"
            />
        </Table.Td>
        <Table.Td>
            <PrintStickerAndMark
                class="-mt-2 mb-2"
                :order="order"
            />
            <Action
                :order="order"
            />
        </Table.Td>
    </Table.Tr>

</template>

<script setup lang="ts">
    import { Table, Icon, Button } from '@/components'
    import { inject } from 'vue'
    import CustomerInfo from '@/pages/orders/fragments/fragments/data/CustomerInfo.vue'
    import CustomerBehavior from '@/pages/orders/fragments/fragments/data/CustomerBehavior.vue'
    import DeliveryHistory from '@/pages/orders/fragments/fragments/data/DeliveryHistory.vue'
    import DeliveryPartner	 from '@/pages/orders/fragments/fragments/data/DeliveryPartner.vue'
    import Shipping	 from '@/pages/orders/fragments/fragments/data/Shipping.vue'
    import Payment	 from '@/pages/orders/fragments/fragments/data/Payment.vue'
    import Status	 from '@/pages/orders/fragments/fragments/data/Status.vue'
    import Action	 from '@/pages/orders/fragments/fragments/data/Action.vue'
    import PrintStickerAndMark from '../PrintStickerAndMark.vue';

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
    } = inject('useOrders') as any
</script>