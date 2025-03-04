<template>
    <div class="grid">
        <span 
            title="Payment methods"
            class="font-medium whitespace-nowrap"
        >
            🚚 {{ order.payment_method_title || 'N/A' }}
        </span>
        <span
            class="min-w-[200px] flex items-center justify-between gap-1"
            :title="`Shipping methods: ${order?.shipping_methods.method_title || 'N/A'}`"
        >
            📍 {{ order.shipping_methods.join(', ') || 'N/A' }}
            <Button.Native
                class="mr-2"
                :class="isShippingEditable ? 'opacity-100' : 'opacity-40'"
                @click="isShippingEditable = !isShippingEditable"
            >
                <Icon
                    name="PhPencilSimpleLine"
                    size="20"
                />
            </Button.Native>
        </span>
        <div 
            v-if="isShippingEditable"
            class="flex gap-1 items-center my-2 relative w-fit"
        >
            <Loader
                class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
                :active="isShippingEditing"
            />
            <Select.Primary
                :options="(shippingMethods || []).map(item => {
                    return {
                        ...item,
                        title: `${item.zone_name}-${item.method_title}-(${item.shipping_cost})`
                    }
                })"
                itemValue="title"
                itemKey="instance_id"
                defaultOption="Select shipping method"
                v-model="selectedShippingInstanceId"
                @change="handleUpdateShippingMethod({
                    shipping_instance_id: selectedShippingInstanceId, 
                    order_id: order.id
                })"
            />
        </div>
        <span
            title="Shipping cost"
            class="font-medium text-red-500"
        >
            💰 Cost: <span v-html="order.currency_symbol"></span>{{ order.shipping_cost || 'N/A' }}
        </span>
    </div>
</template>

<script setup lang="ts">
    import { Select } from '@components'
    import { inject, ref } from 'vue'
    import { Button, Icon, Loader } from '@components'

    defineProps({
        order: Object
    })

    const { 
        shippingMethods, 
        handleUpdateShippingMethod, 
        isShippingEditable, 
        isShippingEditing 
    } = inject('useOrders')
    const selectedShippingInstanceId = ref(null)
</script>