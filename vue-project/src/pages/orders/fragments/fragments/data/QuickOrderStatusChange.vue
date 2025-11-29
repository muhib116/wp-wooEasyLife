<template>
    <div 
        class="relative ml-auto mr-2"
        v-click-outside="closeDropdown"
    >
        <button
            class="py-0.5 bg-black/10 hover:bg-black hover:text-white px-1 rounded-full size-8 md:size-auto grid place-content-center"
            :class="{'!bg-black !text-white': toggleDropdown}"
            @click="toggleDropdown = !toggleDropdown"
            >
            <Icon name="PhDotsThreeOutline" weight="fill" size="20"/>
        </button>
        
        <div 
            v-if="toggleDropdown" 
            class="absolute right-0 bg-white border shadow-md rounded z-10 w-fit block"
            :class="from == 'mobile' ? 'bottom-full mb-1' : 'top-full mt-1'"
        >
            <BlockAllTogether
                :order="order"
                @closeDropdown="toggleDropdown = false "
            />
            <Button.Native
                v-if="hasStatus('cancelled')"
                @onClick="onClickChangeToCancelled"
                class="w-full font-light hover:scale-1 hover:bg-gray-200 gap-0 whitespace-nowrap text-left px-3 py-2 text-sm text-red-600 disabled:opacity-50"
            >
                <Icon name="PhXCircle" size="16" class="inline-block mr-2"/> Cancel Order
            </Button.Native>
            <Button.Native
                v-if="hasStatus('call-not-received')"
                @onClick="onClickChangeToCallNotReceived"
                class="w-full border-t hover:scale-1 hover:bg-gray-200 gap-0 font-light whitespace-nowrap text-left px-3 py-2 text-sm text-black disabled:opacity-50"
            >
                <Icon name="PhPhoneSlash" size="16" class="inline-block mr-2"/> Call Not Received
            </Button.Native>
            <Button.Native
                v-if="hasStatus('checkout-draft')"
                @onClick="onClickChangeToDraft"
                class="w-full border-t hover:scale-1 hover:bg-gray-200 gap-0 font-light whitespace-nowrap text-left px-3 py-2 text-sm text-yellow-600 disabled:opacity-50"
            >
                <Icon name="PhFileDashed" size="16" class="inline-block mr-2"/> Sent to Draft
            </Button.Native>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref, inject } from 'vue'
    import { changeStatus } from '@/api'
    import { Button, Icon } from '@/components'
    import BlockAllTogether from './BlockAllTogether.vue'

    const { wooCommerceStatuses } = inject('useOrders') as any
    const toggleDropdown = ref(false)
    const props = defineProps<{
        order: any
        from?: string
    }>()

    // Check if a status exists in the available statuses
    const hasStatus = (statusSlug: string) => {
        if (!wooCommerceStatuses?.value) return false
        return wooCommerceStatuses.value.some((status: any) => 
            status.slug === statusSlug || 
            status.slug === `wc-${statusSlug}` ||
            status.value === statusSlug ||
            status.value === `wc-${statusSlug}`
        )
    }

    // Change order status
    const changeOrderStatus = async (btn: { isLoading: boolean; }, newStatus: string) => {
        if (!props.order) return
        
        try {
            btn.isLoading = true
            
            // Ensure status has wc- prefix if needed
            const statusValue = newStatus.startsWith('wc-') ? newStatus : `wc-${newStatus}`
            
            await changeStatus([{
                order_id: props.order.id,
                new_status: statusValue
            }])
            
            // You might want to emit an event or update the order status locally here
            console.log(`Order ${props.order.id} status changed to ${statusValue}`)
            
        } catch (error) {
            console.error('Failed to change order status:', error)
        } finally {
            btn.isLoading = false
            props.order.status = newStatus
            toggleDropdown.value = false
        }
    }

    // Close dropdown function for click-outside directive
    const closeDropdown = () => {
        // Add a small delay to prevent immediate closing when button is clicked
        setTimeout(() => {
            if (toggleDropdown.value) {
                toggleDropdown.value = false
            }
        }, 50)
    }

    // Typed handlers referenced from the template to avoid implicit 'any' on the `btn` parameter
    const onClickChangeToCancelled = (btn: { isLoading: boolean }) => changeOrderStatus(btn, 'cancelled')
    const onClickChangeToCallNotReceived = (btn: { isLoading: boolean }) => changeOrderStatus(btn, 'call-not-received')
    const onClickChangeToDraft = (btn: { isLoading: boolean }) => changeOrderStatus(btn, 'checkout-draft')
</script>