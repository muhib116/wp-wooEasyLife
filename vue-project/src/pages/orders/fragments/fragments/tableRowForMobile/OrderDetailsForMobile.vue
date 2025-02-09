<template>
    <div class="p-6 bg-white h-[100vh] overflow-auto fixed z-[99999] w-full left-0 bottom-0">
        <div class="flex justify-between items-center font-bold border-b pb-3 mb-4">
            <h2 class="text-xl">Order details</h2>
            <button
                @click="$emit('close')"
            >
                <Icon
                    name="PhX"
                    size="20"
                />
            </button>
        </div>

        <div class="grid">
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Customer Information
                </h3>
                <CustomerInfo
                    :order="order"
                    class="!text-[14px] grid gap-2"
                />
            </div>
            <hr  class="my-4"/>
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Customer Behavior
                </h3>
                <CustomerBehavior
                    :order="order"
                    textFullFormate
                    class="gap-1"
                />
            </div>
            <hr class="my-4" />
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Delivery History
                </h3>
                <DeliveryHistory
                    :order="order"
                    textFullFormate
                    class="grid gap-1"
                />
            </div>
            <hr class="my-4" />
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Delivery Partner
                </h3>
                
                <DeliveryPartner
                    :order="order"
                />
            </div>
            <hr class="my-4" />
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Shipping info
                </h3>
                
                <Shipping
                    :order="order"
                />
            </div>
            <hr class="my-4" />
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Payment
                </h3>
                
                <Payment
                    :order="order"
                />
            </div>
            <hr class="my-4" />
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Status
                </h3>
                
                <Status
                    :order="order"
                />
            </div>
            <hr class="my-4" />
            <div>
                <h3 class="text-lg font-semibold mb-2">
                    Action
                </h3>
                
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