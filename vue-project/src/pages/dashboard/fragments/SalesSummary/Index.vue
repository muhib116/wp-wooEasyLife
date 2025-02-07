<template>
    <DashboardCard
        title="Sales summary"
        showStatusFilter
        @dateChange="loadSalesSummaryData"
    >
        <Loader
            :active="isLoading"
            class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
        />
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4  3xl:grid-cols-5 gap-3 md:gap-4 xl:gap-6">
            <Card.Stylist
                :title="salesSummaryData?.total_orders || 0"
                subtitle="Total orders"
                iconName="PhPackage"
                iconWeight="duotone"
                class="!bg-[#ffa332]"
            />
            <Card.Stylist
                :title="salesSummaryData?.average_order_value || 0"
                subtitle="Average order value"
                iconName="PhMoney"
                iconWeight="duotone"
                class="!bg-[#243642]"
            />
            <Card.Stylist
                :title="salesSummaryData?.total_sale_amount || 0"
                subtitle="Total sale amount"
                iconName="PhCashRegister"
                iconWeight="duotone"
                class="!bg-[#433878]"
            />
            <Card.Stylist
                :title="salesSummaryData?.total_discount_amount || 0"
                subtitle="Total discount amount"
                iconName="PhSealPercent"
                iconWeight="duotone"
                class="!bg-[#180161]"
            />
            <Card.Stylist
                :title="salesSummaryData?.total_shipping_cost || 0"
                subtitle="Total shipping"
                iconName="PhTruck"
                iconWeight="duotone"
                class="!bg-[#d82d58]"
            />
        </div>
    </DashboardCard>
</template>

<script setup lang="ts">
    import { Card, Loader } from '@components'
    import { useSalesSummary } from './useSalesSummary'
    import DashboardCard from '../DashboardCard.vue'

    const {
        isLoading,
        salesSummaryData,
        loadSalesSummaryData  
    } = useSalesSummary()
</script>