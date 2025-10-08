<template>
    <div class="fixed bg-white top-0 left-0 z-[99999] bottom-0 max-w-[400px] w-full p-4 shadow overflow-auto">
        <Loader
            :active="isLoading"
            class="absolute inset-1/2 -translate-x-1/2 -translate-y-1/2"
        />
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

        <div class="border px-4 py-2 rounded mb-1">
            <div
                class="flex justify-between items-center text-black font-semibold"
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
                    class="capitalize sm:px-2 text-sm leading-[14px] text-black py-[6px] text-left"
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
                        class="capitalize text-sm sm:px-2 gap-1 leading-[14px] py-[6px] text-left"
                        :class="[
                            orderFilter.status == item.slug ? 'font-semibold' : 'font-light'
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

        <!-- START: New DSP Filter for Mobile -->
        <div class="border px-4 py-2 rounded mb-1">
            <label class="flex justify-between items-center text-black font-semibold">
                Filter by Probability
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none text-sm font-light"
                    v-model="selectedDspFilter"
                >
                    <option
                        v-for="option in dspFilterOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </label>
        </div>
        <!-- END: New DSP Filter for Mobile -->

        <div class="border px-4 py-2 rounded mb-1">
            <label class="flex justify-between items-center text-black font-semibold whitespace-nowrap">
                Filter by Done/Undone
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none w-full font-light text-right"
                    v-model="orderFilter.is_done"
                    @change="async () => {
                        await debouncedGetOrders({})
                        $emit('close')
                    }"
                    title="Filter by Done/Undone Status"
                >
                    <option value="">All Orders</option>
                    <option value="1">Marked as Done</option>
                    <option value="0">Marked as Undone</option>
                </select>
            </label>
        </div>

        <div class="border px-4 py-2 rounded mb-1">
            <label class="flex justify-between items-center text-black font-semibold whitespace-nowrap">
                Filter by Following
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none w-full font-light text-right"
                    v-model="orderFilter.need_follow"
                    @change="async () => {
                        await debouncedGetOrders({})
                        $emit('close')
                    }"
                    title="Filter by Follow Up Status"
                >
                    <option value="">All Orders</option>
                    <option value="1">Marked as Follow</option>
                    <option value="0">Marked as Not follow</option>
                </select>
            </label>
        </div>

        <div class="font-light border px-2 py-1 rounded-sm">
            
        </div>

        
        <TableHeaderAction
            class="flex-col bg-red-50 w-full [&>*]:w-full [&>*>*]:w-full [&>*]:text-[16px]"
            @close="$emit('close')"
        />
    </div>
</template>

<script setup lang="ts">
import { Icon, Loader } from '@components'
import { inject, ref } from 'vue'
import TableHeaderAction from '@/pages/orders/fragments/TableHeaderAction.vue'

const {
    orderStatusWithCounts,
    orderFilter,
    handleFilter,
    totalRecords,
    isLoading,
    selectedDspFilter,
    dspFilterOptions,
    debouncedGetOrders
} = inject('useOrders')

const toggleStatus = ref(false)
</script>