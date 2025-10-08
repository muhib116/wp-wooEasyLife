<template>
    <div v-if="orderFilter" class="print:hidden flex flex-col xl:flex-row gap-4 items-center md:items-stretch md:justify-between whitespace-nowrap">
        <div v-if="$slots.leftSide" class="sm:flex gap-2 md:gap-4 items-center">
            <slot name="leftSide"></slot>
            
            <label class="font-light border px-2 py-1 rounded-sm">
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none"
                    v-model="selectedDspFilter"
                    title="Filter by Delivery Success Probability"
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

            <label class="font-light border px-2 py-1 rounded-sm">
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none"
                    v-model="orderFilter.is_done"
                    @change="debouncedGetOrders"
                    title="Filter by Done/Undone Status"
                >
                    <option value="">All Orders</option>
                    <option value="1">Marked as Done</option>
                    <option value="0">Marked as Undone</option>
                </select>
            </label>

            <label class="font-light border px-2 py-1 rounded-sm">
                <select 
                    class="outline-none bg-transparent !border-none focus:outline-none"
                    v-model="orderFilter.need_follow"
                    @change="debouncedGetOrders"
                    title="Filter by Follow Up Status"
                >
                    <option value="">All Orders</option>
                    <option value="1">Marked as Follow</option>
                    <option value="0">Marked as Not follow</option>
                </select>
            </label>
        </div>

        <!-- Pagination Controls -->
        <div class="flex flex-wrap md:flex-nowrap gap-2 items-center sm:gap-3 text-sm justify-end">
            <div class="hidden md:flex items-center gap-2">
                <span>Per page</span>
                <Input.Native
                    type="number"
                    class="border px-2 pr-1 py-1 w-14"
                    v-model="orderFilter.per_page"
                    @input="debouncedGetOrders"
                />
            </div>
            <span class="hidden md:inline-block">{{ totalRecords }} items</span>
            <div class="flex items-center space-x-1 ml-4">
                <Button.Native
                    :disabled="isFirstPage"
                    @onClick="btn => goToPage(1, btn)"
                    class="px-2 py-1 border rounded-sm hidden md:inline-block"
                    :class="isFirstPage ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-blue-600 bg-blue-100 border-blue-300 hover:bg-blue-200'"
                >«</Button.Native>
                <Button.Native
                    :disabled="isFirstPage"
                    @onClick="goToPreviousPage"
                    class="px-2 py-1 border rounded-sm"
                    :class="isFirstPage ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-blue-600 bg-blue-100 border-blue-300 hover:bg-blue-200'"
                >‹</Button.Native>
                <span class="text-gray-500">{{ currentPage }}</span>
                <span class="text-gray-500"> of {{ totalPages }}</span>
                <Button.Native
                    :disabled="isLastPage"
                    @onClick="goToNextPage"
                    class="px-2 py-1 border rounded-sm"
                    :class="isLastPage ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-blue-600 bg-blue-100 border-blue-300 hover:bg-blue-200'"
                >›</Button.Native>
                <Button.Native
                    :disabled="isLastPage"
                    @onClick="btn => goToPage(totalPages, btn)"
                    class="px-2 py-1 border rounded-sm hidden md:inline-block"
                    :class="isLastPage ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-blue-600 bg-blue-100 border-blue-300 hover:bg-blue-200'"
                >»</Button.Native>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Input, Button, Icon } from '@components'
import { computed, inject } from 'vue'

defineProps<{ hideSearch?: boolean }>()

const { 
    totalPages,
    currentPage,
    totalRecords,
    orderFilter,
    debouncedGetOrders,
    selectedDspFilter,
    dspFilterOptions,
} = inject('useOrders')

const isFirstPage = computed(() => orderFilter.value.page <= 1)
const isLastPage = computed(() => orderFilter.value.page >= totalPages.value)

const goToPage = async (page: number, btn) => {
    btn.isLoading = true
    orderFilter.value.page = page
    await debouncedGetOrders({isLoading: false}) // Assuming debouncedGetOrders handles button state
    btn.isLoading = false
}
const goToPreviousPage = async (btn) => {
    if (!isFirstPage.value) {
        btn.isLoading = true
        orderFilter.value.page--
        await debouncedGetOrders({isLoading: false})
        btn.isLoading = false
    }
}
const goToNextPage = async (btn) => {
    if (!isLastPage.value) {
        btn.isLoading = true
        orderFilter.value.page++
        await debouncedGetOrders({isLoading: false})
        btn.isLoading = false
    }
}
</script>