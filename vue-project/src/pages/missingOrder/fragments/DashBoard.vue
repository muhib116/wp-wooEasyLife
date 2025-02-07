<template>
    <DashboardCard
        class="!p-0 !shadow-none"
        @dateChange="loadAbandonedOrder"
    >
        <div class="grid grid-cols-4 gap-4">
            <Card.Stylist
                :title="getDashboardData.totalAbandonedOrder"
                subtitle="Total Abandoned Order"
                class="!bg-orange-500"
            />
            <Card.Stylist
                :title="`${getDashboardData.loosedAmount}tk`"
                subtitle="Loosed Amount"
                class="!bg-red-500"
            />
            <Card.Stylist
                :title="getDashboardData.totalRecoveredOrder"
                subtitle="Total Recovered Order"
                class="!bg-sky-500"
            />
            <Card.Stylist
                :title="`${getDashboardData.recoveredAmount}tk`"
                subtitle="Recovered Amount"
                class="!bg-green-500"
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

