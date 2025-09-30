<template>
    <div class="grid">
        <!-- 1. Payment Method Display and Edit Toggle -->
        <div class="flex items-center gap-2 justify-between">
            <div 
                v-if="!isPaymentEditable"
                title="Payment methods"
                class="font-medium whitespace-nowrap min-w-[200px] flex items-center justify-between gap-1"
            >
                    🚚 {{ order.payment_method_title || 'N/A' }}
            </div>
            
            <!-- Payment Method Editor -->
            <div 
                v-if="isPaymentEditable"
                class="flex gap-1 items-center my-2 relative w-fit"
            >
                <Loader
                    class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
                    :active="isPaymentEditing"
                    size="20"
                />
                <Select.Primary
                    :options="paymentMethods"
                    itemValue="title"
                    itemKey="id"
                    defaultOption="Select payment method"
                    v-model="selectedPaymentMethodId"
                    @change="_handleUpdatePaymentMethod()"
                />
            </div>

            <Button.Native
                class="mr-2"
                :class="isPaymentEditable ? 'opacity-100' : 'opacity-40'"
                @click="togglePaymentEditable"
            >
                <Icon
                    :name="isPaymentEditable ? 'PhCheckSquareOffset': 'PhNotePencil'"
                    size="20"
                />
            </Button.Native>
        </div>
        
        <!-- 2. Shipping Method Display and Edit Toggle -->
        <div class="flex items-center gap-2 justify-between">
            <div
                v-if="!isShippingEditable"
                class="min-w-[200px] flex items-center justify-between gap-1 truncate"
                :title="`Shipping methods: ${order?.shipping_methods.join(', ') || 'N/A'}`"
            >
                📍 {{ order.shipping_methods.join(', ') || 'N/A' }}
            </div>
            
            <!-- Shipping Method Editor -->
            <div 
                v-if="isShippingEditable"
                class="flex gap-1 items-center my-2 relative w-fit"
            >
                <Loader
                    class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
                    :active="isShippingEditing"
                    size="20"
                />
                <Select.Primary
                    :options="(shippingMethods || []).map(item => {
                        // Logic to determine the key: use instance_id if > 0, otherwise use method_id
                        // This aligns with the robust PHP backend logic
                        const keyToSend = item.instance_id > 0 ? item.instance_id : item.method_id;
                        return {
                            ...item,
                            title: `${item.zone_name}-${item.method_title}-(${item.shipping_cost})`,
                            key_to_send: keyToSend
                        }
                    })"
                    itemValue="title"
                    itemKey="key_to_send"
                    defaultOption="Select shipping method"
                    v-model="selectedShippingInstanceId"
                    @change="_handleUpdateShippingMethod()"
                />
            </div>

            <!-- Shipping Edit Button -->
            <Button.Native
                class="mr-2"
                :class="isShippingEditable ? 'opacity-100' : 'opacity-40'"
                @click="isShippingEditable = !isShippingEditable"
            >
                <Icon
                    :name="isShippingEditable ? 'PhCheckSquareOffset': 'PhNotePencil'"
                    size="20"
                />
            </Button.Native>
        </div>
        
        <!-- 3. Shipping Cost Display -->
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
    import { showNotification } from "@/helper"
    import { updateAddress } from '@/api'

    const props = defineProps({
        order: Object
    })

    // --- Local State for Editing ---
    const selectedShippingInstanceId = ref(null)
    const isShippingEditable = ref(false)
    const isShippingEditing  = ref(false)
    const isPaymentEditable  = ref(false)
    const isPaymentEditing   = ref(false)
    
    // Payment specific state: Initialize with current order payment method ID
    const selectedPaymentMethodId = ref(props.order?.payment_method || null) 
    
    // --- Injected Functions & Data ---
    const { 
        shippingMethods, 
        handleUpdateShippingMethod,
        paymentMethods, // Injected: Array of available payment methods
        getOrders,      // Injected: Function to refresh the order list
    } = inject('useOrders')

    // --- Payment Method Logic ---
    const togglePaymentEditable = () => {
        isPaymentEditable.value = !isPaymentEditable.value;
        // Shipping editor বন্ধ করে দেওয়া
        if(isPaymentEditable.value) isShippingEditable.value = false; 
    }

    const _handleUpdatePaymentMethod = async () => {
        if (!selectedPaymentMethodId.value || !props.order?.id) return;
        
        // পেলোড তৈরি করা
        const payload = {
            order_id: props.order.id,
            payment_method: selectedPaymentMethodId.value
        };

        try {
            isPaymentEditing.value = true;
            
            // API কল
            await updateAddress(payload);

            // সাফল্যের বার্তা তৈরি ও ডেটা রিফ্রেশ করা
            const newMethodTitle = paymentMethods.value.find(
                (method: any) => method.id === selectedPaymentMethodId.value
            )?.title || selectedPaymentMethodId.value;

            await getOrders(); 
            showNotification({ 
                type: 'success', 
                message: `Payment method updated to: <strong>${newMethodTitle}</strong>` 
            });
            
        } catch (error) {
            console.error("Payment Update Error:", error);
            showNotification({ type: 'danger', message: 'Failed to update payment method.' });
            
        } finally {
            isPaymentEditing.value = false;
            isPaymentEditable.value = false;
        }
    }

    // --- Shipping Method Logic ---
    const _handleUpdateShippingMethod = async () => {
        if (!selectedShippingInstanceId.value || !props.order?.id) return;
        
        // Payment editor বন্ধ করে দেওয়া
        isPaymentEditable.value = false; 
        
        try {
            isShippingEditing.value = true
            // Injected handleUpdateShippingMethod ফাংশন কল করা
            await handleUpdateShippingMethod({
                shipping_instance_id: selectedShippingInstanceId.value, 
                order_id: props.order.id
            })
        } catch (error) {
            console.error("Shipping Update Error:", error);
        } finally {
            isShippingEditable.value = false
            isShippingEditing.value = false
        }
    }
</script>