<template>
    <div class="flex items-center text-[12px] gap-2 gap-y-2 flex-wrap px-4 mb-4 text-extralight">
        <Button.Native
            class="capitalize px-2 py-[2px] leading-[14px] text-black"
            :class="orderFilter.status == '' ? 'font-semibold' : 'font-light'"
            @click="btn => handleFilter('', btn)"
        >
            All<span class="font-bold">({{ orders.length }})</span>
        </Button.Native>
        <template
            v-for="(item, index) in orderStatusWithCounts || []"
            :key="index"
        >
            <Button.Native
                class="capitalize order-status px-2 py-[2px] gap-1 leading-[14px]"
                :class="[
                    orderFilter.status == item.slug ? 'font-semibold' : 'font-light',
                    `status-${item.slug}`
                ]"
                @click="btn => handleFilter(item.slug, btn)"
            >
                {{ item.title }}
                <span class="font-bold">({{ item.count }})</span>
            </Button.Native>
        </template>
    </div>
</template>

<script setup lang="ts">
    import { inject } from 'vue'
    import { Button } from '@components'

    const {
        orders,
        getOrders,
        orderFilter,
        orderStatusWithCounts
    } = inject('useOrders')

    const handleFilter = async (status: string, btn) => {
        try {
            btn.isLoading = true
            orderFilter.value.status = status
            await getOrders()
        } finally {
            btn.isLoading = false
        }
    }
</script>