<template>
    <div class="flex gap-4 items-center justify-end">
        <Button.Native
            class="opacity-50 flex items-center hover:opacity-100"
            @onClick="btn => cloneOrder(order, btn)"
            title="Clone Order"
        >
            <Icon
                name="PhCopy"
                size="25"
            />
        </Button.Native>
        <Button.Native
            v-if="order?.courier_data?.consignment_id"
            class="opacity-50 flex items-center hover:opacity-100"
            @onClick="btn => printProductDetails(order, () => markAsDone(order, btn), configData.invoice_logo)"
            title="Print Tag"
        >
            <Icon
                name="PhPrinter"
                size="25"
            />
        </Button.Native>
        <Button.Native
            class="opacity-50 flex items-center gap-2"
            :class="{
                '!opacity-100 text-green-500' : order.is_done == 1
            }"
            @onClick="btn => markAsDone(order, btn)"
        >
            <Icon
                name="PhChecks"
                size="20"
            />
            {{ order.is_done == 1 ? 'done' : '' }}
        </Button.Native>
        <Button.Native
            class="opacity-50 flex items-center gap-2"
            :class="{
                '!opacity-100 text-green-500' : order.need_follow == 1
            }"
            @onClick="btn => markAsFollowing(order, btn)"
        >
            <Icon
                name="PhHeadset"
                size="20"
            />
        </Button.Native>
    </div>
</template>

<script setup lang="ts">
    import { Button, Icon } from '@components'
    import { inject } from 'vue'
    import { printProductDetails } from '@/helper'
    import { useCustomOrder } from '@/pages/orders/fragments/createNewOrder/useCustomOrder'

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
        markAsDone,
        markAsFollowing
    } = inject('useOrders') as any

    const { configData } = inject('configData') as any

    const {
        cloneOrder
    } = useCustomOrder()
</script>