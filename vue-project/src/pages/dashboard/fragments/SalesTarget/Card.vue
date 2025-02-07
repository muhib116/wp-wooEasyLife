<template>
  <div class="border p-6 rounded-sm relative">
    <Loader
      :active="isLoading"
      class="bg-white/90 rounded-full p-[2px] absolute inset-1/2 -translate-x-1/2"
    />

    <Heading :title="title" titleClass="!text-base" />
    <hr class="my-2" />
    <div 
      class="items-center grid pt-4 gap-4"
      :class="hideTargetAchieve ? '' : 'grid-cols-2'"
    >
      <div
        class="min-h-[200px] max-h-[300px]"
        :class="{ 'mb-4 flex w-full justify-center': !hideTargetAchieve }"
      >
        <Chart.Native
          :width="hideTargetAchieve ? '100%' : '250'"
          :height="hideTargetAchieve ? '100%' : '250'"
          :chartData="chartData"
        />
      </div>

      <div v-if="!hideTargetAchieve" class="grid grid-cols-1 gap-4 text-black">
        <div class="bg-green-100 border-l-4 border-green-500 px-3 py-1">
          <h3>Achievement</h3>
          <h4 class="font-semibold">
            ৳ {{ Number(chartData?.series?.[0] || 0).toFixed(2) || 0 }}
          </h4>
        </div>
        <div class="bg-[#eb212822] border-l-4 border-[#eb2128] px-3 py-1">
          <h3>Target</h3>
          <h4 class="font-semibold">
            ৳ {{ Number(chartData?.series?.[1] || 0).toFixed(2) || 0 }}
          </h4>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Heading, Chart, Loader } from "@components";
import { computed, inject } from "vue";

defineProps<{
  title: string;
  chartData: object;
  hideTargetAchieve?: boolean;
}>();

const { isLoading } = inject("useSalesTarget");
</script>
