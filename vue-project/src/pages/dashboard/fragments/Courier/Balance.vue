<template>
    <DashboardCard
        title="Courier Balance"
        :showDateFilter="false"
    >
        <Loader
            :active="isLoading"
            class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
        />
        <div 
            v-if="!courierBalanceData"
            class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
        >
            <Card.Stylist
                :title="`${courierBalanceData?.total || 0}tk`"
                subtitle="Total Balance"
                class="!bg-[#693d84]"
            />
            <Card.Native
                v-if="courierBalanceData?.steadfast"
                class="!bg-[#02b795] text-white !pl-0 grid grid-cols-[40%_60%] gap-4 overflow-hidden border border-[#02b795]"
            >
                <div class="bg-white p-2 -mt-6 -mb-6 flex items-center">
                    <img
                        :src="courierBalanceData?.steadfast?.logo"
                    />
                </div>
                <div>
                    <h3 
                        class="font-black text-xl leading-6 truncate" 
                    >
                        {{ courierBalanceData?.steadfast?.balance || 0 }} tk
                    </h3>
                    <h3 class="capitalize mt-1">
                        Steadfast balance
                    </h3>
                </div>
            </Card.Native>

            <Card.Native
                v-if="false && courierBalanceData?.redx"
                class="!bg-[#eb2128] text-white !pl-0 grid grid-cols-[40%_60%] gap-4 overflow-hidden border border-[#eb2128]"
            >
                <div class="bg-white p-2 -mt-6 -mb-6 flex items-center">
                    <img
                        :src="courierBalanceData?.redx?.logo"
                    />
                </div>
                <div>
                    <h3 
                        class="font-black text-xl leading-6 truncate" 
                    >
                        {{ courierBalanceData?.redx?.balance || 0 }} tk
                    </h3>
                    <h3 class="capitalize mt-1">
                        Redx
                    </h3>
                </div>
            </Card.Native>

            <Card.Native
                v-if="false && courierBalanceData?.paperfly"
                class="!bg-[#0066c5] text-white !pl-0 grid grid-cols-[40%_60%] gap-4 overflow-hidden border border-[#0066c5]"
            >
                <div class="bg-white p-2 -mt-6 -mb-6 flex items-center">
                    <img
                        :src="courierBalanceData?.paperfly?.logo"
                    />
                </div>
                <div>
                    <h3 
                        class="font-black text-xl leading-6 truncate" 
                    >
                        {{ courierBalanceData?.paperfly?.balance || 0 }} tk
                    </h3>
                    <h3 class="capitalize mt-1">
                        Paperfly
                    </h3>
                </div>
            </Card.Native>

            <Card.Native
                v-if="false"
                class="!bg-[#fb4347] text-white !pl-0 grid grid-cols-[40%_60%] gap-4 overflow-hidden border border-[#fb4347]"
            >
                <div class="bg-white p-2 -mt-6 -mb-6 flex items-center">
                    <img
                        :src="courierBalanceData?.pathao?.logo"
                    />
                </div>
                <div>
                    <h3 
                        class="font-black text-xl leading-6 truncate" 
                    >
                        {{ courierBalanceData?.pathao?.balance || 0 }} tk
                    </h3>
                    <h3 class="capitalize mt-1">
                        Pathao
                    </h3>
                </div>
            </Card.Native>
        </div>
        <div v-else>
            No balance data found!
        </div>
    </DashboardCard>
</template>

<script setup lang="ts">
    import {
        Card,
        Table,
        Loader
    } from '@components'
    import DashboardCard from '../DashboardCard.vue'
    import { useBalance } from './useBalance'
    import { onMounted } from 'vue'

    const {
        isLoading,
        courierBalanceData,
        loadCourierBalance 
    } = useBalance()

    onMounted(loadCourierBalance)
</script>