<template>
    <div class="fixed bg-white top-0 left-0 z-40 bottom-0 max-w-[400px] w-full p-4 shadow">
        <div class="flex justify-between items-center border-b mb-4 pb-2">
            <span class="mb-1 mt-2 block text-gray-800 text-xl font-semibold">
                Filter & Action
            </span>
            <button
                class="-mt-4 hover:text-red-500"
                @click="$emit('close')"
            >
                <Icon
                    name="PhX"
                    size="25"
                    weight="bold"
                />
            </button>
        </div>

        <div class="border p-4 rounded">
            <div
                class="flex justify-between items-center text-lg font-semibold"
                @click="toggleStatus = !toggleStatus"
            >
                Filter by Status
                <Icon
                    :name="toggleStatus ? 'PhCaretUp' : 'PhCaretDown'"
                    size="20"
                />
            </div>
            <div 
                v-if="toggleStatus"
                class="grid sm:justify-stretch items-center pt-3 border-t mt-3"
            >
                <button
                    class="capitalize sm:px-2 leading-[14px] text-black py-2 text-left"
                    :class="orderFilter.status == '' ? 'font-medium' : 'font-light'"
                    @click="() => {
                        btn => handleFilter('', btn)
                        $emit('close')
                    }"
                >
                    All<span class="font-bold">({{ totalRecords }})</span>
                </button>
                <template
                    v-for="(item, index) in orderStatusWithCounts || []"
                    :key="index"
                >
                    <button
                        class="capitalize order-status sm:px-2 gap-1 leading-[14px] py-2 text-left"
                        :class="[
                            orderFilter.status == item.slug ? 'font-semibold' : 'font-light',
                            `status-${item.slug}`
                        ]"
                        @click="async (btn) => {
                            await handleFilter(item.slug, btn)
                            $emit('close')
                        }"
                    >
                        {{ item.title }}
                        <span class="font-bold">({{ item.count }})</span>
                    </button>
                </template>
            </div>
        </div>

        <TableHeaderAction
            @close="$emit('close')"
        />
    </div>
</template>

<script setup lang="ts">
import {
    Icon,
} from '@components'
import { inject, ref } from 'vue'
import TableHeaderAction from '@/pages/orders/fragments/TableHeaderAction.vue'

const {
    orderStatusWithCounts,
    orderFilter,
    handleFilter,
    totalRecords
} = inject('useOrders')

const toggleStatus = ref(false)
</script>