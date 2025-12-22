<template>
    <div class="space-y-4 relative">
        <Loader
            class="absolute left-1/2 top-14 -translate-x-1/2"
            :active="isLoading"
        />
        <CustomerInfo />
        <ShippingInfo />
        <AddProduct />
        <Coupon />

        <Input.Primary
            label="COD"
            placeholder="Modify the COD if Needed."
            type="number"
            v-model="form.cod_amount"
        />

        <Input.Primary
            label="Customer Note (Optional)"
            placeholder="Write customer note."
            v-model="form.customer_note"
        />

        <div>
            <Button.Primary 
                class="ml-auto"
                @onClick="handleCreateOrder"
            >
                Create Order
            </Button.Primary>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { onMounted, provide } from 'vue'
    import { Loader, Input, Button } from '@components'
    import CustomerInfo from './CustomerInfo.vue'
    import ShippingInfo from './ShippingInfo.vue'
    import AddProduct from './AddProduct.vue'
    import Coupon from './Coupon.vue'
    import { useCustomOrder } from './useCustomOrder'

    const _useCustomOrder = useCustomOrder()
    const {
        form,
        handleCreateOrder,
        isLoading,
        resetCustomOrderForm
    } = _useCustomOrder

    provide('useCustomOrder', _useCustomOrder)
</script>