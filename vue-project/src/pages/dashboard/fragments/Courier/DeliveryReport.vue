<template>
    <DashboardCard
        title="Delivery Report"
        @dateChange="loadCourierDeliveryData"
    >
        <Loader
            :active="isLoading"
            class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
        />
        <div 
            v-if="courierDeliveryData && Object.keys(courierDeliveryData).length"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 3xl:grid-cols-5 gap-3 md:gap-4 xl:gap-6 !text-white relative"
        >
            <Card.Stylist
                v-for="(item, key, index) in courierDeliveryData"
                :key="key"
                :style="{
                    backgroundColor: colors[index]
                }"
            >
                <p class="text-lg">
                    Status: <span class="font-bold capitalize">{{ key || 'n/a' }}</span>
                </p>
                <p class="font-semibold opacity-80">
                    Total Parcels: {{ item.total_parcel }}
                </p>
                <p class="opacity-60">
                    Partners: {{ item.partners.join(', ') }}
                </p>
            </Card.Stylist>
        </div>
        <p v-else>No delivery report available!</p>
    </DashboardCard>
</template>

<script setup lang="ts">
    import {
        Card,
        Loader
    } from '@components'
    import DashboardCard from '../DashboardCard.vue'
    import { useDeliveryReport } from './useDeliveryReport'

    const colors = ['#23486A', '#131010', '#441752', '#CB6040', '#A04747', '#557C56']
    const {
        isLoading,
        courierDeliveryData,
        loadCourierDeliveryData 
    } = useDeliveryReport()
</script>