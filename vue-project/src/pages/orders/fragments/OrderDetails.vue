<template>
    <div class="px-4 lg:px-6">
        <div v-if="activeOrder" class="grid md:grid-cols-2 mb-4">
            <h4>
                <span class="block">
                    <span style="font-weight: bold;">
                        Name:
                    </span> 
                    {{ activeOrder?.customer_name }}
                </span>
                <span class="block">
                    <span style="font-weight: bold;">
                        Date:
                    </span> 
                    {{ activeOrder?.date_created }}
                </span>
            </h4>

            <div>
                <a 
                    class="block text-orange-500 underline"
                    :href="`tel:${activeOrder.billing_address?.phone}`"
                >
                    <span style="font-weight: bold;">
                        Phone:
                    </span> 
                    {{ activeOrder.billing_address?.phone }}
                </a>
                <h4 v-if="activeOrder.billing_address?.email">
                    <span style="font-weight: bold;">
                        Email:
                    </span> 
                    {{ activeOrder.billing_address?.email }}
                </h4>
                <DeliveryPartner />
            </div>
            <h4>
                <span style="font-weight: bold;">
                    Billing Address:
                </span> 
                {{ activeOrder.billing_address?.address_1 }} 
                {{ activeOrder.billing_address?.address_2 }}
            </h4>
        </div>
        
        <DesktopOrderedProductDetails
            class="hidden lg:grid"
        />
        <MobileOrderedProductDetails 
            class="block lg:hidden"
        />
    </div>
</template>

<script setup lang="ts">
    import { inject } from 'vue'
    import DesktopOrderedProductDetails from './DesktopOrderedProductDetails.vue'
    import MobileOrderedProductDetails from './MobileOrderedProductDetails.vue'
    import DeliveryPartner from '@/pages/orders/fragments/fragments/data/DeliveryPartner.vue'

    const {
        activeOrder
    } = inject('useOrders')
</script>