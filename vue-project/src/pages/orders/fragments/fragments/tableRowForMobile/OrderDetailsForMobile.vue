<template>
    <div class="p-3 bg-white h-[100vh] overflow-auto fixed z-50 w-full left-0 bottom-0">
        <div class="flex justify-between items-center font-bold border-b pb-2 mb-2">
            <h2 class="text-lg">Order details</h2>
            <button
                @click="$emit('close')"
            >
                <Icon
                    name="PhX"
                    size="20"
                />
            </button>
        </div>

        <div>
            <div>
                <h3>Customer Information</h3>
                <CustomerInfo
                    :order="order"
                />
            </div>
            <div>
                <h3>Customer Behavior</h3>
                <CustomerBehavior
                    :order="order"
                />
            </div>
            <div>
                <h3>Delivery History</h3>
                <DeliveryHistory
                    :order="order"
                />
            </div>
            <div>
                <h3>Delivery Partner</h3>
                
                <DeliveryPartner
                    :order="order"
                />
            </div>
            <div>
                <h3>Shipping info</h3>
                
                <Shipping
                    :order="order"
                />
            </div>
            <div>
                <h3>Payment</h3>
                
                <Payment
                    :order="order"
                />
            </div>
            <div>
                <h3>Status</h3>
                
                <Status
                    :order="order"
                />
            </div>
            <div>
                <h3>Action</h3>
                
                <Action
                    :order="order"
                />
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Icon } from '@components'
    import CustomerInfo from '@/pages/orders/fragments/fragments/data/CustomerInfo.vue'
    import CustomerBehavior from '@/pages/orders/fragments/fragments/data/CustomerBehavior.vue'
    import DeliveryHistory from '@/pages/orders/fragments/fragments/data/DeliveryHistory.vue'
    import DeliveryPartner from '@/pages/orders/fragments/fragments/data/DeliveryPartner.vue'
    import Shipping	 from '@/pages/orders/fragments/fragments/data/Shipping.vue'
    import Payment	 from '@/pages/orders/fragments/fragments/data/Payment.vue'
    import Status	 from '@/pages/orders/fragments/fragments/data/Status.vue'
    import Action	 from '@/pages/orders/fragments/fragments/data/Action.vue'

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
    }>()
</script>