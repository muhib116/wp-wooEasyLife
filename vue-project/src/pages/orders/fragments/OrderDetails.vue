<template>
    <div class="relative select-none">
        <div
            v-if="isLoading"
            class="absolute inset-0 z-50 flex justify-center pt-[90px] backdrop-blur-sm"
        >
            <Loader />
        </div>
        <div v-if="activeOrder" class="grid md:grid-cols-2 gap-6 mb-4">
            <div>
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
                    <h4 class="capitalize">
                        <span style="font-weight: bold;">
                            Order Status:
                        </span> 
                        {{ activeOrder.status == 'processing' ? 'New order' : activeOrder.status }}
                    </h4>
                </div>

                <h4>
                    <span style="font-weight: bold;">
                        Billing Address:
                    </span> 
                    {{ activeOrder.billing_address?.address_1 }} 
                    {{ activeOrder.billing_address?.address_2 }}
                </h4>

                <h4>
                    <span style="font-weight: bold;">
                        Total Amount
                    </span> 
                    {{ activeOrder.total }}
                </h4>

                <CODAssigner
                    :order="activeOrder"
                />
            </div>

            <div>
                <div class="max-w-[235px] md:ml-auto pr-4 whitespace-nowrap">
                    <DeliveryPartner
                        :order="activeOrder"
                    />
                    <PrintStickerAndMark
                        class="mt-4"
                        :order="activeOrder"
                    />
                </div>
            </div>
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
    import { Loader } from '@/components'
    import DesktopOrderedProductDetails from './DesktopOrderedProductDetails.vue'
    import MobileOrderedProductDetails from './MobileOrderedProductDetails.vue'
    import DeliveryPartner from '@/pages/orders/fragments/fragments/data/DeliveryPartner.vue'
    import CODAssigner from '@/pages/orders/fragments/fragments/data/CODAssigner.vue'
    import PrintStickerAndMark from './PrintStickerAndMark.vue'
    import { onMounted, onBeforeUnmount, inject, ref } from 'vue'
    import { printProductDetails } from '@/helper'

    const {
        activeOrder,
        markAsDone
    } = inject('useOrders')
    const { configData } = inject('configData')

    const isLoading = ref(false)

    const printHandler = () => {
        printProductDetails(activeOrder.value, () => markAsDone(activeOrder.value, isLoading), configData.invoice_logo)
    }
    
    const onKeyUp = (event: KeyboardEvent) => {
        if (event.code === 'Space' || event.key === ' ') {
            printHandler()
        }
    }

    onMounted(() => {
        window.addEventListener('keyup', onKeyUp)
    })

    onBeforeUnmount(() => {
        window.removeEventListener('keyup', onKeyUp)
    })
</script>