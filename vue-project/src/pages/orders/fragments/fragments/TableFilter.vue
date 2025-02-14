<template>
    <div class="flex justify-stretch sm:justify-start items-center text-[12px] sm:gap-2 md:gap-y-2 flex-wrap sm:px-2 md:px-4 mb-4 text-extralight">
        <Button.Native
            class="capitalize sm:px-2 py-[2px] leading-[14px] text-black"
            :class="orderFilter.status == '' ? 'font-semibold' : 'font-light'"
            @click="btn => handleFilter('', btn)"
        >
            All<span class="font-bold">({{ totalRecords }})</span>
        </Button.Native>
        <template
            v-for="(item, index) in orderStatusWithCounts || []"
            :key="index"
        >
            <Button.Native
                class="capitalize order-status px-1 sm:px-2 py-[2px] gap-1 leading-[14px]"
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
        totalRecords,
        handleFilter,
        orderFilter,
        orderStatusWithCounts
    } = inject('useOrders')
</script>