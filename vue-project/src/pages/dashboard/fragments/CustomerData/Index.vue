<template>
    <DashboardCard
        title="New order by repeat customer"
        subtitle="New customer vs Repeat customer order comparison"
        @dateChange="loadCustomerData"
    >
        <Loader
            :active="isLoading"
            class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
        />

        <div class="mb-4 h-[250px]">
            <Chart.Native
                :chartData="chartData"
                width="100%"
                height="100%"
            />
        </div>
    </DashboardCard>

    <DashboardCard
        title="Customer Overview (based on completed order)"
        :showDateFilter="false"
        class="-mt-6"
    >
        <Card.Stylist class="!bg-orange-500 mb-4"
            :title="customerData?.total_customers || 0"
            subtitle="Total Customer"
        />
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-3 md:gap-4 xl:gap-6">
            <Card.Stylist class="!bg-green-600"
                :title="customerData?.total_repeat_customers || 0"
                subtitle="Repeat Customer"
                iconName="PhArrowUUpLeft"
                weight="fill"
            />
            <Card.Stylist class="!bg-green-700"
                :title="`${customerData?.repeat_customer_percentage || 0}%`"
                subtitle="Repeat Customer in Percentage"
                iconName="PhShoppingBagOpen"
            />
            <Card.Stylist class="!bg-sky-600"
                :title="customerData?.total_new_customers || 0"
                subtitle="New Customer"
                iconName="PhBasket"
            />
            <Card.Stylist class="!bg-sky-700"
                :title="`${customerData?.new_customer_percentage || 0}%`"
                subtitle="New Customer in Percentage"
                iconName="PhArrowUUpLeft"
            />
        </div>
    </DashboardCard>
</template>

<script setup lang="ts">
    import {
        Card,
        Chart,
        Loader
    } from '@components'
    import DashboardCard from '../DashboardCard.vue'
    import { useCustomerData } from './useCustomerData'

    const {
        isLoading,
        chartData,
        customerData,
        loadCustomerData 
    } = useCustomerData()
</script>