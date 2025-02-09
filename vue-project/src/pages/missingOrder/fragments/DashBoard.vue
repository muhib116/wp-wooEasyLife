<template>
    <DashboardCard
        class="!p-0 !shadow-none"
        @dateChange="loadAbandonedOrder"
    >
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
            <Card.Stylist
                :title="getDashboardData.totalAbandonedOrder"
                subtitle="Total Abandoned Order"
                class="!bg-orange-500"
                iconName="PhFlagBanner"
            />
            <Card.Stylist
                :title="getDashboardData.totalCallNotReceived"
                subtitle="Total Call Not Received"
                class="!bg-red-400"
                iconName="PhPhonePlus"
            />
            <Card.Stylist
                :title="getDashboardData.remainingAbandonedOrder"
                subtitle="Total Remaining Abandoned Order"
                class="!bg-gray-500"
                iconName="PhClockCountdown"
            />
            <Card.Stylist
                :title="`${getDashboardData.loosedAmount}tk`"
                subtitle="Loosed Amount"
                class="!bg-red-500"
                iconName="PhTrendDown"
            />
            <Card.Stylist
                :title="getDashboardData.totalRecoveredOrder"
                subtitle="Total Recovered Order"
                class="!bg-sky-500"
                iconName="PhClockCounterClockwise"
            />
            <Card.Stylist
                :title="`${getDashboardData.recoveredAmount}tk`"
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
        loadAbandonedOrder,
        getDashboardData
    } = inject('useMissingOrder')
</script>

