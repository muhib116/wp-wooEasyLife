<template>
    <DashboardCard
        title="Order cycle time"
        :Key="chartKey"
        @dateChange="loadOrderCycleTimeData"
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
    import { useOrderCycleTime } from './useOrderCycleTime.js'
    import DashboardCard from '../DashboardCard.vue'

    const {
        chartKey,
        isLoading,
        orderCycleTimeData,
        loadOrderCycleTimeData 
    } = useOrderCycleTime()

    const chartData = computed(() => {
        return {
            type: 'bar',
            options: {
                xaxis: {
                    categories: orderCycleTimeData.value?.categories || []
                },
                colors: ['#6e4189']
            },
            series: orderCycleTimeData.value?.series || []
        }
    })
</script>