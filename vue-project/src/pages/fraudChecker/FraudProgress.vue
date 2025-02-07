<template>
    <div
        v-if="data && data?.report?.total_order"
        class="progress-bar h-[15px] mt-2 mb-[25px] bg-red-500 rounded relative text-[10px] flex shadow"
    >
        <div class="relative h-full bg-green-500 flex-shrink-0 rounded-sm shadow" :style="{ width: data.report.success_rate }">
            <span title="Success rate." class="progress-tool-tip bg-green-500 text-white border border-gray-200 absolute py-[1px] px-1 rounded-sm top-full mt-1 left-1/2 -translate-x-1/2 shadow">
                {{ data.report.success_rate }}
            </span>
        </div>
        <div v-if="data.report.success_rate != '100%'" class="flex-1 relative">
            <span title="Cancel rate." class="progress-tool-tip bg-red-500 cancel text-white border border-gray-200 absolute py-[1px] px-1 rounded-sm top-full mt-1 left-1/2 -translate-x-1/2 shadow">
                {{  100 - data.report.success_rate.replace('%', '') }}%
            </span>
        </div>
    </div>
</template>

<script setup lang="ts">
    defineProps<{
        data: {
            report: {
                total_order: number | string
                success_rate: number | string
            }
        }
    }>()
</script>

<style scoped>
    .progress-tool-tip::before{
        content: '';
        border: 1px solid #3334;
        border-right: none;
        border-bottom: none;
        position: absolute;
        bottom: calc(100% - 3px);
        left: calc(50% - 3px);
        width: 6px;
        height: 6px;
        rotate: 45deg;
        background: #22c55d;
    }
    .progress-tool-tip.cancel::before{
        background: #ef4444;
    }
</style>