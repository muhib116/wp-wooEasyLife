<template>
    <DashboardCard
        title="Sales Progress"
        subtitle="Total Completed Orders Within the Selected Date Range"
        :Key="chartKey"
        @dateChange="loadSalesProgressData"
    >
        <Loader
            :active="isLoading"
            class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
        />
        <div class="-ml-3 -mr-3 h-[320px]">
            <Chart.Native
                :chartData="chartData"
                width="100%"
                height="100%"
            />
        </div>
    </DashboardCard>
</template>

<script setup lang="ts">
    import {
        Chart,
        Loader
    } from '@components'
    import { computed } from 'vue'
    import { useSalesProgress } from './useSalesProgress.ts'
    import DashboardCard from '../DashboardCard.vue'

    const {
        chartKey,
        isLoading,
        salesProgressData,
        loadSalesProgressData 
    } = useSalesProgress()

    const chartData = computed(() => {
        return {
            type: 'line',
            options: {
                xaxis: {
                    categories: salesProgressData.value?.categories || []
                },
                colors: ['#f97315']
            },
            series: salesProgressData.value?.series || []
        }
    })
</script>