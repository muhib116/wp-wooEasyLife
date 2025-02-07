<template>
    <Layout>
        <Container>
            <div class="grid gap-4 md:gap-6 lg:gap-8 font-light">
                <OrderStatistics />
                <CustomerData />
                <SalesSummary />

                <div class="grid lg:grid-cols-2 gap-5 w-full">
                    <OrderProgress />
                    <SalesProgress />
                </div>

                <SalesTarget />
                <Courier />
                
                <div class="grid lg:grid-cols-2 gap-5 w-full">
                    <OrderSource />
                    <OrderCycleTime />
                </div>
                

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 w-full">
                    <RecentOrder />
                    <TopSellingProduct />
                </div>
                
            </div>
        </Container>
    </Layout>
</template>

<script setup lang="ts">
    import { provide } from 'vue'
    import { Layout, Container } from '@layout'
    import OrderStatistics from './fragments/OrderStatistics/Index.vue'
    import { useDashboard } from './useDashboard'
    import RecentOrder from './fragments/recentOrder/RecentOrder.vue'
    import TopSellingProduct from './fragments/TopSellingProduct/Index.vue'
    import SalesSummary from './fragments/SalesSummary/Index.vue'
    import SalesProgress from './fragments/SalesProgress/Index.vue'
    import OrderProgress from './fragments/OrderProgress/Index.vue'
    import OrderSource from './fragments/OrderSource/Index.vue'
    import OrderCycleTime from './fragments/OrderCycleTime/Index.vue'
    import Courier from './fragments/Courier/Index.vue'
    import SalesTarget from './fragments/SalesTarget/Index.vue'
    import CustomerData from './fragments/CustomerData/Index.vue'

    const _useDashboard = useDashboard()

    provide('useDashboard', _useDashboard)
</script>