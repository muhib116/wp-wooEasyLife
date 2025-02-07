<template>
    <div class="flex justify-between px-4 mt-2 mb-2">
        <div class="flex gap-4">
            <slot name="beforeSearch"></slot>
            <!-- Search Input -->
            <div 
                v-if="!hideSearch" 
                class="flex gap-1"
            >
                <Input.Native
                    placeholder="Search customer"
                    class="text-base border px-2 py-1 rounded-sm"
                    v-model="orderFilter.search"
                />
                <Button.Primary
                    class="!py-1 px-2"
                    @click="debouncedGetOrders"
                >
                    <Icon
                        name="PhMagnifyingGlass"
                        weight="bold"
                    />
                </Button.Primary>
            </div>
        </div>

        <!-- Pagination Controls -->
        <div class="flex items-center space-x-2 text-sm justify-end">
            <!-- Per Page Input -->
            <div class="flex items-center gap-2">
                <span>Per page</span>
                <Input.Native
                    type="number"
                    class="border px-2 pr-1 py-1 w-14"
                    v-model="orderFilter.per_page"
                    @input="debouncedGetOrders"
                />
            </div>

            <!-- Total Items -->
            <span>{{ totalRecords }} items</span>

            <!-- Pagination Buttons -->
            <div class="flex items-center space-x-1 ml-4">
                <Button.Native
                    :disabled="isFirstPage"
                    @onClick="btn => goToPage(1, btn)"
                    class="px-2 py-1 border rounded-sm"
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
                    class="px-2 py-1 border rounded-sm"
                    :class="isLastPage ? 'text-gray-400 bg-gray-100 cursor-not-allowed' : 'text-blue-600 bg-blue-100 border-blue-300 hover:bg-blue-200'"
                >»</Button.Native>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Input, Button, Icon } from '@components'
import { computed, inject, ref } from 'vue'

defineProps<{
    hideSearch?: boolean
}>()

// Inject dependencies
const { totalRecords, orderFilter, getOrders } = inject('useOrders')

// Debounce handler for getting orders
let timeoutId: any;
const debouncedGetOrders = () => {
    orderFilter.value.page = orderFilter.value.page > totalPages.value ? totalPages.value : orderFilter.value.page
    clearTimeout(timeoutId)
    timeoutId = setTimeout(getOrders, 500)
}

// Pagination logic
const currentPage = computed(() =>
    orderFilter.value.page > totalPages.value ? totalPages.value : orderFilter.value.page
)
const totalPages = computed(() =>
    orderFilter.value.per_page ? Math.ceil(totalRecords.value / orderFilter.value.per_page) : 1
)
const isFirstPage = computed(() => orderFilter.value.page <= 1)
const isLastPage = computed(() => orderFilter.value.page >= totalPages.value)

const goToPage = async (page: number, btn) => {
    btn.isLoading = true
    orderFilter.value.page = page
    await getOrders()
    btn.isLoading = false
}
const goToPreviousPage = async (btn) => {
    if (!isFirstPage.value) {
        btn.isLoading = true
        orderFilter.value.page--
        await getOrders()
        btn.isLoading = false
    }
}
const goToNextPage = async (btn) => {
    if (!isLastPage.value) {
        btn.isLoading = true
        orderFilter.value.page++
        await getOrders()
        btn.isLoading = false
    }
}
</script>