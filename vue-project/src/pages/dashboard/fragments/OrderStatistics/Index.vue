<template>
    <DashboardCard
        title="Status Statistics"
        @dateChange="loadOrderStatisticsData"
    >
        <Loader
            :active="isLoading"
            class="absolute inset-x-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-20"
        />

        <MessageBox
            v-if="!orderStatuses?.length && !isLoading"
            title="No data found!"
            type="info"
        />

        <div
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4  3xl:grid-cols-5 gap-3 md:gap-4 xl:gap-6 !text-white relative"
            v-if="orderStatuses?.length && Object.keys(orderStatistics)?.length"
        >
            <Card.Stylist
                v-for="(item, index) in orderStatuses"
                :key="index"
                class="bg-blue-500 text-white order-status"
                :class="`status-${item.slug}`"
                :title="orderStatistics.status_wise[item.slug] || 0"
                :subtitle="item.title"
                :iconName="iconsWithBg[item.slug]?.icon"
            />
        </div>
    </DashboardCard>
</template>
<script setup lang="ts">
    import { Card, Loader } from '@components'
    import { useOrderStatistics } from './useOrderStatistics'
    import DashboardCard from '../DashboardCard.vue'

    const {
        orderStatuses,
        orderStatistics,
        isLoading,
        loadOrderStatisticsData
    } = useOrderStatistics()

    const iconsWithBg = {
        processing: {
            icon: 'PhBasket'
        },
        "follow-up": {
            icon: 'PhHeadset'
        },
        "confirmed": {
            icon: 'PhCheck'
        },
        "call-not-received": {
            icon: 'PhPhoneX'
        },
        "fake": {
            icon: 'PhXCircle'
        },
        "courier-entry": {
            icon: 'PhListNumbers'
        },
        "courier-hand-over": {
            icon: 'PhPackage'
        },
        "out-for-delivery": {
            icon: 'PhTruck'
        },
        "delivered": {
            icon: 'PhHandDeposit'
        },
        "payment-received": {
            icon: 'PhCreditCard'
        },
        "pending-payment": {
            icon: 'PhCardholder'
        },
        "returned": {
            icon: 'PhArrowUUpLeft'
        },
        "refunded": {
            icon: 'PhClockCounterClockwise'
        },
        "on-hold": {
            icon: 'PhHandGrabbing'
        },
        "completed": {
            icon: 'PhFlag'
        },
        "cancelled": {
            icon: 'PhX'
        },
        "checkout-draft": {
            icon: 'PhFileDashed'
        },
    }
</script>