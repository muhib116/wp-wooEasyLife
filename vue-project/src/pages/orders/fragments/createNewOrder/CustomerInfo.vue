<template>
    <Select.Primary
        label="Order status"
        defaultOption="Select Status"
        :options="wooCommerceStatuses"
        itemKey="slug"
        v-model="form.order_status"
    />
    <div class="grid gap-4">
        <Input.Primary
            label="Customer Name *"
            placeholder="Write customer name."
            v-model="form.first_name"
        />

        <div>
            <Input.Primary
                label="Customer Phone Number *"
                placeholder="Write valid phone number."
                v-model="form.phone"
                @input="getCustomerDeliveryStatus(form.phone)"
            />
            <div 
                v-if="fraudData?.report"
                class="mt-2 text-sm text-gray-600 flex gap-4"
            >
                <div>Delivered: <span class="text-green-500">{{ fraudData.report.confirmed }}</span></div>
                <div>Cancelled: <span class="text-red-500">{{ fraudData.report.cancel }}</span></div>
                <div>Delivery Success Rate: <span class="font-semibold" :class="fraudData.report.success_rate < 50 ? 'text-red-500' : 'text-green-500'">{{ fraudData.report.success_rate }}</span></div>
            </div>
        </div>
    </div>

    <Textarea.Native
        label="Customer Address *"
        placeholder="Write customer address."
        v-model="form.address_1"
    />

    <div class="flex gap-4">
        <Select.Primary
            label="Order Source *"
            :options="orderSource"
            v-model="selectedSource"
            wrapperClass="flex-1"
            @change="() => {
                if(selectedSource == 'other') {
                    form.created_via = ''
                }else {
                    form.created_via = selectedSource
                }
            }"
        />
        <Input.Primary
            v-if="selectedSource == 'other'"
            label="Order source name"
            placeholder="Write order source name."
            wrapperClass="flex-1"
            v-model="form.created_via"
        />
    </div>
</template>

<script setup lang="ts">
    import { Input, Select, Textarea } from '@/components'
    import { ref, inject, onUnmounted, onMounted } from 'vue'
    import { normalizePhoneNumber, validateBDPhoneNumber, showNotification } from '@/helper'
    import { useFraudChecker } from '@/pages/fraudChecker/useFraudChecker'

    const selectedSource = ref(null)
    const { configData } = inject('configData') as any

    const orderSource = [
        {
            id: 'whats-app',
            title: 'Whats app'
        },
        {
            id: 'messenger',
            title: 'Messenger'
        },
        {
            id: 'instagram',
            title: 'Instagram'
        },
        {
            id: 'phone-call',
            title: 'Phone call'
        },
        {
            id: 'clone-order',
            title: 'Clone Order'
        },
        {
            id: 'other',
            title: 'Other'
        },
    ]

    const { handleFraudCheck, data: fraudData } = useFraudChecker()
    const { form } = inject<any>('useCustomOrder') as any

    const { wooCommerceStatuses } = inject<any>('useOrders') as any

    let timeoutId: number | null = null
    let debounceTimeoutId: number | null = null
    
    const showInvalidPhoneNotification = () => {
        if (timeoutId) clearTimeout(timeoutId)
        timeoutId = setTimeout(() => {
            showNotification({
                type: 'danger',
                message: 'Please enter a valid Bangladeshi phone number.'
            })
        }, 2000)
    }
    
    const getCustomerDeliveryStatus = async (phone: string) => {
        // Reset previous fraud data
        fraudData.value = null
        
        // Clear previous debounce timeout
        if (debounceTimeoutId) clearTimeout(debounceTimeoutId)
        
        // Early return if phone is empty or too short
        if (!phone || phone.trim().length < 10) {
            return
        }
        
        // Debounce API calls to avoid too many requests while typing
        debounceTimeoutId = setTimeout(async () => {
            // Clean and validate phone number
            const cleanedPhone = normalizePhoneNumber(phone.trim())
            const isValidPhone = validateBDPhoneNumber(cleanedPhone)
            
            if (!isValidPhone) {
                showInvalidPhoneNotification()
                return
            }
            
            try {
                // Create button object for the handleFraudCheck function
                const buttonState = { isLoading: false }
                
                // Call fraud check API
                await handleFraudCheck(cleanedPhone, buttonState)
            } catch (error) {
                console.error('Error fetching customer fraud data:', error)
                showNotification({
                    type: 'danger',
                    message: 'Failed to check customer data. Please try again.'
                })
            }
        }, 1000) // Wait 1 second after user stops typing
    }

    // Cleanup timeouts when component unmounts
    onUnmounted(() => {
        if (timeoutId) clearTimeout(timeoutId)
        if (debounceTimeoutId) clearTimeout(debounceTimeoutId)
    })

    onMounted(() => {
        selectedSource.value = orderSource.find(item => item.id === form.created_via) ? form.created_via : 'other'
    })
</script>