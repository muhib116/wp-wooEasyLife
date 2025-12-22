<template>
    <div class="grid md:grid-cols-2 gap-4">
        <div v-if="shippingMethods">
            <Select.Primary
                label="Shipping Method *"
                :options="shippingMethods.map(item => {
                    return {
                        ...item,
                        title: `${item.zone_name}-${item.method_title}-(${item.shipping_cost})`
                    }
                })"
                returnType="object"
                itemValue="title"
                itemKey="method_id"
                v-model="form.shippingMethod"
            />
        </div>
        <div v-if="paymentMethods">
            <Select.Primary
                label="Payment Method *"
                :options="paymentMethods"
                returnType="object"
                v-model="form.paymentMethod"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Select } from '@/components'
    import { ref, onMounted, inject } from 'vue'
    
    const {
        form
    } = inject('useCustomOrder') as any
    
    const {
        shippingMethods,
        paymentMethods // <-- useOrders এ লোড করা পেমেন্ট মেথড
    } = inject('useOrders') as any

    onMounted(() => {
        // Check if only one payment method is available
        if (paymentMethods.value && paymentMethods.value.length === 1) {
            form.value.paymentMethod = paymentMethods.value[0];
        }
        // Check if only one shipping method is available
        if (shippingMethods.value && shippingMethods.value.length === 1) {
            form.value.shippingMethod = shippingMethods.value[0];
        }
    });
</script>