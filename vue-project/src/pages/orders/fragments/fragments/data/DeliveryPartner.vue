<template>
    <div>
        <span 
            title="Delivery success probability"
            class="font-semibold flex gap-1 mb-3 items-center px-1 py-1 rounded-sm border-l-2"
            :style="{
                background: +deliveryProbability >=0 ? `hsla(${ (+deliveryProbability / 100) * 120 } 75% 35% / 0.2)` : `#ff000033`,
                color: `hsl(${ (+deliveryProbability / 100) * 120 } 75% 35%)`,
                borderLeftColor: `hsl(${ (+deliveryProbability / 100) * 120 } 75% 35%)`
            }"
        >
            <Icon
                class="cursor-pointer"
                title="This is just a prediction based on available data. \nWe do not guarantee the accuracy of the outcome, as various external factors may influence the actual results."
                name="PhInfo"
                size="20"
            />
            {{ +deliveryProbability >= 0 ? `DSP: ${deliveryProbability}%` : deliveryProbability }}
        </span>
    
        <div 
            v-if="!isEmpty(order?.courier_data)"
            class="grid relative"
        >
            <div title="Delivery partner" class="mb-1">
                <img
                    v-if="courierConfigs[order?.courier_data?.partner]?.logo"
                    :src="courierConfigs[order?.courier_data?.partner]?.logo"
                    class="w-[100px]"
                />
                <span v-else>
                    🚚 {{ order?.courier_data?.partner }}
                </span>
            </div>
            <a
                v-if='order?.courier_data?.parcel_tracking_link'
                class="font-medium text-blue-500" 
                title="Click to track your parcel"
                :href="order?.courier_data?.parcel_tracking_link"
                target="_black"
            >
                📍 Track Parcel
            </a>
            <div title="Consignment Id" class="flex items-center gap-1">
                🆔 
                <span
                    v-if="!isEditingConsignmentId"
                    @dblclick="isEditingConsignmentId = true"
                    class="cursor-pointer hover:bg-gray-100 px-2 py-1 rounded border border-transparent hover:border-gray-300 transition-colors"
                    title="Double-click to edit"
                >
                    {{ order?.courier_data?.consignment_id || 'Not set' }}
                </span>
                <Input.Classic 
                    v-else
                    v-model="order!.courier_data.consignment_id"
                    @update:modelValue="updateConsignmentIdInOrder(order!)"
                    @blur="isEditingConsignmentId = false"
                    @keyup.enter="isEditingConsignmentId = false"
                    @keyup.escape="isEditingConsignmentId = false"
                    autofocus
                />
            </div>

            
            <a 
                :href="`https://www.steadfast.com.bd/user/edit-parcel/${order?.courier_data?.consignment_id}`" 
                title="Update Steadfast Parcel Data"
                class="flex items-center gap-2"
                target="__blank"
            >
                <Icon name="PhArrowSquareOut" size="20" />
                Edit Parcel Data
            </a>
            
            <span 
                class="font-medium text-sky-500 flex items-center gap-2" 
                title="Courier status"
            >
                📦 {{ order?.courier_data?.status || 'N/A' }}
                <Icon
                    name="PhInfo"
                    :title="courierStatusInfo[order?.courier_data?.status]"
                    size="20"
                    class="cursor-pointer"
                />
            </span>
        </div>
        <div class="flex flex-col gap-3 mt-2">
            <CourierEntry
                :order="order"
            />
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Icon, Input } from '@/components'
    import { computed, inject, ref } from 'vue'
    import { isEmpty } from 'lodash'
    import { CourierEntry } from '@/components'

    const props = defineProps({
        order: Object
    })

    const isEditingConsignmentId = ref(false)

    const {
        courierStatusInfo,
        getDeliveryProbability,
        updateConsignmentIdInOrder
    } = inject('useOrders')

    const { courierConfigs } = inject('useCourierConfig')


    const deliveryProbability = computed(() => {
        return getDeliveryProbability(props.order)
    })
</script>