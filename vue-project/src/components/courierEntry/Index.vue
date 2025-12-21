<template>
    <div 
        class="relative"
        v-click-outside="() => {
            toggleCourierDropdown = false
        }"
        v-if="configData.courier_automation && isEmpty(order?.courier_data)"
    >
        <Button.Native
            @click="toggleCourierDropdown = !toggleCourierDropdown"
            class="py-1 px-2 w-full border shadow rounded-sm bg-[#553555] text-white"
        >
            <Icon
                name="PhTruck"
                size="16"
            />
            <span v-if="!hideText">Courier Entry</span>
        </Button.Native>
        <div
            v-if="toggleCourierDropdown"
            class="absolute top-full left-0 min-w-[120px] border border-[#693d84] overflow-hidden bg-white [&>button+button]:border-t shadow rounded-b-sm z-50 grid"
        >
            <template
                v-for="_item in courierCompanyNames"
                :key="_item.slug"
            >
                <Button.Native 
                    v-if="courierConfigs[_item.slug]?.is_active"
                    class="text-left text-xl px-2 py-2 text-gray-700 hover:scale-110 origin-left duration-300"
                    @onClick="async (btn) => {
                        await handleCourier(_item.slug, btn)
                        toggleCourierDropdown = false
                        $emit('close')
                    }"
                >
                    <img
                        v-if="courierConfigs[_item.slug]?.logo"
                        :src="courierConfigs[_item.slug]?.logo"
                        class="w-20 object-contain"
                    />
                    <span v-else>{{ _item.title }}</span>
                </Button.Native>
            </template>
        </div>
    </div>

    <div>
        <Button.Native
            v-if="configData.courier_automation && (showRefreshBtn ^ (order && !isEmpty(order?.courier_data)))"
            class="opacity-100 w-full text-white bg-sky-500 shadow rounded-sm truncate px-2 md:px-1 py-1"
            title="Refresh CourierData"
            @onClick="async (btn) => {
                await handleCourierDataRefresh(btn)
                $emit('close')
            }"
        >
            <Icon
                name="PhArrowsClockwise"
                size="16"
                weight="bold"
            />
            <span v-if="!hideText">Courier Data Refresh</span>
        </Button.Native>
    </div>
</template>

<script setup lang="ts">
    import { inject, ref } from 'vue'
    import { Button, Icon } from '@components'
    import { isEmpty } from 'lodash'

    const props = defineProps<{
        showRefreshBtn?: boolean
        hideText?: boolean
        order?: {
            courier_data: object
        }
    }>()

    const { 
        handleCourierEntry,
        clearSelectedOrders,
        setSelectedOrder,
        refreshBulkCourierData
    } = inject('useOrders') as any
    
    const {configData} = inject('configData') as any
    const {
        courierCompanyNames,
        courierConfigs
    } = inject('useCourierConfig') as any

    const toggleCourierDropdown = ref(false)
    const handleCourier = async (partnerName, btn) => {
        if(props.order) {
            clearSelectedOrders()
            setSelectedOrder(props.order)
        }

       await handleCourierEntry(partnerName, btn)
    }

    const handleCourierDataRefresh = async (btn) => {
        if(props.order) {
            clearSelectedOrders()
            setSelectedOrder(props.order)
        }
        await refreshBulkCourierData(btn)
        setSelectedOrder(props.order)
    }
</script>