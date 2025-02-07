<template>
    <DashboardCard
        title="Order Progress"
        subtitle="Total Orders Placed Within the Selected Date Range"
        :Key="chartKey"
        @dateChange="loadOrderProgressData"
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
    import { useOrderProgress } from './useOrderProgress.js'
    import DashboardCard from '../DashboardCard.vue'

    const {
        chartKey,
        isLoading,
        orderProgressData,
        loadOrderProgressData 
    } = useOrderProgress()

    const chartData = computed(() => {
        return {
            type: 'line',
            options: {
                xaxis: {
                    categories: orderProgressData.value?.categories || []
                }
            },
            series: orderProgressData.value?.series || []
        }
    })
</script>