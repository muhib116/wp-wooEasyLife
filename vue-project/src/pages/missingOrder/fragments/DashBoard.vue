<template>
    <DashboardCard
        class="!p-0 !shadow-none"
        @dateChange="loadDashboardData"
    >
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
            <Card.Stylist
                :title="dashboardData.total_abandoned_orders"
                subtitle="Total Abandoned Order"
                class="!bg-orange-500"
                iconName="PhFlagBanner"
            />
            <Card.Stylist
                :title="dashboardData.total_call_not_received_orders"
                subtitle="Total Call Not Received"
                class="!bg-red-400"
                iconName="PhPhonePlus"
            />
            <Card.Stylist
                :title="dashboardData.total_remaining_abandoned"
                subtitle="Total Remaining Abandoned Order"
                class="!bg-gray-500"
                iconName="PhClockCountdown"
            />
            <Card.Stylist
                :title="`${dashboardData.lost_amount}tk`"
                subtitle="Lost Amount"
                class="!bg-red-500"
                iconName="PhTrendDown"
            />
            <Card.Stylist
                :title="dashboardData.total_confirmed_orders"
                subtitle="Total Recovered Order"
                class="!bg-sky-500"
                iconName="PhClockCounterClockwise"
            />
            <Card.Stylist
                :title="`${dashboardData.confirmed_amount}tk`"
                subtitle="Recovered Amount"
                class="!bg-green-500"
                iconName="PhTrendUp"
            />
        </div>
    </DashboardCard>
</template>


<script setup lang="ts">
    import {
        Card
    } from '@components'
    import DashboardCard from '@/pages/dashboard/fragments/DashboardCard.vue'
    import { inject } from 'vue'


    const {
        dashboardData,
        loadDashboardData
    } = inject('useMissingOrder')
</script>

