<template>
    <div class="flex justify-between pr-6">
        <Heading
            title="Order Details"
            class="mb-4 px-4 lg:px-6"
        />
        <Button.Primary
            class="!bg-green-500"
            @click="setActiveOrder('')"
        >
            <Icon
                name="PhCaretLeft"
                size="20"
                weight="bold"
            />
            <span class="hidden md:inline-block">Back</span>
        </Button.Primary>
    </div>

    <div class="px-4 lg:px-6">
        <div v-if="activeOrder" class="grid md:grid-cols-2 mb-4">
            <h4>
                <span style="font-weight: bold;">
                    Name:
                </span> 
                {{ activeOrder?.customer_name }}
            </h4>
            <h4>
                <span style="font-weight: bold;">
                    Phone:
                </span> 
                {{ activeOrder.billing_address?.phone }}
            </h4>
            <h4 v-if="activeOrder.billing_address?.email">
                <span style="font-weight: bold;">
                    Email:
                </span> 
                {{ activeOrder.billing_address?.email }}
            </h4>
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
    import { Table, Button, Icon, Heading } from '@components'
    import { inject } from 'vue'
    import DesktopOrderedProductDetails from './DesktopOrderedProductDetails.vue'
    import MobileOrderedProductDetails from './MobileOrderedProductDetails.vue'

    const {
        activeOrder,
        setActiveOrder
    } = inject('useOrders')
</script>