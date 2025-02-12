<template>
    <div v-if="orderFilter" class="flex flex-col xl:flex-row gap-4 items-center md:items-stretch md:justify-between whitespace-nowrap">
        <div v-if="$slots.leftSide" class="sm:flex gap-2 md:gap-4">
            <slot name="leftSide"></slot>
        </div>

        <!-- Pagination Controls -->
        <div class="flex flex-wrap md:flex-nowrap gap-2 items-center sm:gap-3 text-sm justify-end">
            <!-- Per Page Input -->
            <div class="hidden md:flex items-center gap-2">
                <span>Per page</span>
                <Input.Native
                    type="number"
                    class="border px-2 pr-1 py-1 w-14"
                    v-model="orderFilter.per_page"
                    @input="loadAbandonedOrder"
                />
            </div>

            <!-- Total Items -->
            <span class="hidden md:inline-block">{{ totalRecords }} items</span>

            <!-- Pagination Buttons -->
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
                <span class="text-gray-500">
                    {{ currentPage }}
                </span>
                <span class="text-gray-500">
                    of {{ totalPages }}
                </span>
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

defineProps<{
    hideSearch?: boolean
}>()

// Inject dependencies
const {
    totalPages,
    currentPage,
    totalRecords,
    orderFilter,
    loadAbandonedOrder
} = inject('useMissingOrder')

const isFirstPage = computed(() => orderFilter.value.page <= 1)
const isLastPage = computed(() => orderFilter.value.page >= totalPages.value)

const goToPage = async (page: number, btn) => {
    btn.isLoading = true
    orderFilter.value.page = page
    await loadAbandonedOrder()
    btn.isLoading = false
}
const goToPreviousPage = async (btn) => {
    if (!isFirstPage.value) {
        btn.isLoading = true
        orderFilter.value.page--
        await loadAbandonedOrder()
        btn.isLoading = false
    }
}
const goToNextPage = async (btn) => {
    if (!isLastPage.value) {
        btn.isLoading = true
        orderFilter.value.page++
        await loadAbandonedOrder()
        btn.isLoading = false
    }
}
</script>